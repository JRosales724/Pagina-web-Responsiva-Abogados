/* js/main.js — versión segura para SPA del panel
   - Se auto-desactiva si no hay #main o #sidebar
   - No interfiere con landing pública / enlaces externos
*/

(function () {
  // ===== Helpers de entorno =====
  var hasjQuery = typeof window.jQuery !== "undefined";
  var $ = hasjQuery ? window.jQuery : null;

  // Activa solo en vistas que tengan #main o #sidebar (panel)
  var mainEl = document.getElementById("main");
  var sidebarEl = document.getElementById("sidebar");
  var collapseBtn = document.getElementById("sidebarCollapse");

  if (!mainEl && !sidebarEl) {
    // No es el layout del panel -> salir sin hacer nada
    return;
  }

  // ===== Utilidades =====
  function sameOrigin(url) {
    try {
      var u = new URL(url, window.location.href);
      return u.origin === window.location.origin;
    } catch (e) {
      return false;
    }
  }

  function isAjaxCandidate(linkEl) {
    // Reglas que NO se interceptan
    var href = linkEl.getAttribute("href") || "";
    if (!href || href === "#") return false;
    if (linkEl.hasAttribute("download")) return false;
    if (linkEl.getAttribute("target") === "_blank") return false;

    // Ignorar anchors, mailto, tel, javascript
    if (
      href.startsWith("#") ||
      href.startsWith("mailto:") ||
      href.startsWith("tel:") ||
      href.startsWith("javascript:")
    ) {
      return false;
    }

    // Ignorar WhatsApp / externos
    if (href.includes("wa.me") || href.includes("api.whatsapp.com"))
      return false;

    // Solo mismo origen
    if (!sameOrigin(href)) return false;

    return true;
  }

  function showLoader() {
    // Loader inline para no depender de CSS global
    var loaderHTML =
      '<div id="__spa_loader" style="' +
      "position:relative;min-height:120px" +
      '">' +
      '<div style="' +
      "position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.6)" +
      '">' +
      '<div style="' +
      "width:36px;height:36px;border-radius:50%;border:4px solid #d1d5db;border-top-color:#8A1538;animation:__spin 0.8s linear infinite" +
      '"></div>' +
      "</div>" +
      "<style>@keyframes __spin{to{transform:rotate(360deg)}}</style>" +
      "</div>";
    mainEl.innerHTML = loaderHTML;
  }

  function renderError(msg) {
    mainEl.innerHTML =
      '<div style="padding:12px;border-radius:8px;background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca">' +
      (msg || "Error al cargar la página.") +
      "</div>";
  }

  function extractMain(htmlText) {
    // Busca #main > * en el HTML recibido
    var tmp = document.createElement("html");
    tmp.innerHTML = htmlText;
    var incomingMain = tmp.querySelector("#main");
    if (!incomingMain) return null;
    var frag = document.createDocumentFragment();
    Array.from(incomingMain.children).forEach(function (ch) {
      frag.appendChild(ch.cloneNode(true));
    });
    return frag;
  }

  // ===== Carga AJAX con historial =====
  function loadContent(url, push) {
    showLoader();

    // Con jQuery (si está presente), usamos .get por compatibilidad
    if (hasjQuery) {
      $.get(url)
        .done(function (html) {
          var frag = extractMain(html);
          if (frag) {
            mainEl.innerHTML = "";
            mainEl.appendChild(frag);
            if (push) {
              window.history.pushState({ ajax: true, url: url }, "", url);
            }
            // Enlaces internos dentro de #main se delegan de nuevo (ya está abajo con addEventListener)
          } else {
            renderError("No se encontró el contenedor #main en la respuesta.");
          }
        })
        .fail(function () {
          renderError("Error al cargar la página.");
        });
      return;
    }

    // Sin jQuery: usar fetch
    fetch(url, { credentials: "same-origin" })
      .then(function (r) {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.text();
      })
      .then(function (html) {
        var frag = extractMain(html);
        if (frag) {
          mainEl.innerHTML = "";
          mainEl.appendChild(frag);
          if (push) {
            window.history.pushState({ ajax: true, url: url }, "", url);
          }
        } else {
          renderError("No se encontró el contenedor #main en la respuesta.");
        }
      })
      .catch(function () {
        renderError("Error al cargar la página.");
      });
  }

  // ===== Sidebar: abrir/cerrar =====
  if (collapseBtn && sidebarEl) {
    collapseBtn.addEventListener("click", function () {
      sidebarEl.classList.toggle("active");
    });
  }

  // ===== Delegación de enlaces dentro de #main =====
  if (mainEl) {
    mainEl.addEventListener("click", function (e) {
      // Respetar Cmd/Ctrl/Alt/Shift (abrir nueva pestaña, etc.)
      if (e.metaKey || e.ctrlKey || e.altKey || e.shiftKey) return;

      var a = e.target.closest("a");
      if (!a) return;

      if (!isAjaxCandidate(a)) return;

      e.preventDefault();
      var url = a.getAttribute("href");
      if (url) loadContent(url, true);
    });
  }

  // ===== Menú lateral: enlaces =====
  if (sidebarEl) {
    sidebarEl.addEventListener("click", function (e) {
      if (e.metaKey || e.ctrlKey || e.altKey || e.shiftKey) return;
      var a = e.target.closest("a");
      if (!a) return;
      if (a.classList.contains("dropdown-toggle")) return;

      if (!isAjaxCandidate(a)) return;

      e.preventDefault();
      var url = a.getAttribute("href");
      if (url) {
        if (sidebarEl.classList.contains("active")) {
          sidebarEl.classList.remove("active");
        }
        loadContent(url, true);
      }
    });
  }

  // ===== Soporte botón atrás/adelante =====
  window.addEventListener("popstate", function (ev) {
    if (ev.state && ev.state.ajax && ev.state.url) {
      // Re-cargar la URL del historial sin pushState
      loadContent(ev.state.url, false);
    }
  });

  // ===== Opcional: carga inicial si la URL no es la del dashboard base
  // (Descomenta si quieres que al entrar a /panel/xxx se auto-cargue)
  // if (mainEl && window.location.pathname !== "/panel/") {
  //   loadContent(window.location.href, false);
  // }
})();
