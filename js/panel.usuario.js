// AXM/js/panel.usuario.js
(function () {
  const BASE = "/AXM"; // carpeta raíz del proyecto
  const main = document.getElementById("axm-main");
  if (!main) return;

  async function cargarVista(vista, anchor) {
    if (!vista) vista = "inicio";

    const url = `${BASE}/components/menu.usuario.php?v=${encodeURIComponent(
      vista
    )}&partial=1`;

    try {
      const res = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!res.ok) throw new Error("Error al cargar vista");

      const html = await res.text();
      main.innerHTML = html;

      // Mantener SIEMPRE la misma URL visible (/AXM/)
      history.pushState({ vista, anchor }, "", BASE + "/");

      // Marcar enlace activo
      document.querySelectorAll(".axm-nav-link[data-view]").forEach((a) => {
        a.classList.toggle("axm-nav-active", a.dataset.view === vista);
      });

      // Scroll opcional a un ancla dentro de la vista
      if (anchor) {
        const target =
          document.getElementById(anchor) ||
          document.querySelector(`[data-anchor-id="${anchor}"]`);
        if (target) {
          target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      } else {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    } catch (err) {
      console.error(err);
      main.innerHTML =
        '<div style="padding:1rem">Ocurrió un error al cargar la vista.</div>';
    }
  }

  // Click en cualquier link con .axm-nav-link
  document.addEventListener("click", (e) => {
    const link = e.target.closest(".axm-nav-link");
    if (!link) return;

    const vista = link.dataset.view;
    if (!vista) return; // deja pasar links normales

    e.preventDefault();
    const anchor = link.dataset.anchor || "";
    cargarVista(vista, anchor);
  });

  // Botón atrás / adelante del navegador
  window.addEventListener("popstate", (e) => {
    const state = e.state || {};
    const vista = state.vista || "inicio";
    const anchor = state.anchor || "";
    cargarVista(vista, anchor);
  });

  // Estado inicial de la historia (para que no se vea nada raro en la URL)
  if (!history.state || !history.state.vista) {
    history.replaceState({ vista: "inicio", anchor: "" }, "", BASE + "/");
  }
})();
