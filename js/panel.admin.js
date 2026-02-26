// AXM/js/panel.admin.js
(function () {
  const BASE = "/AXM"; // misma base visual que el usuario
  const main = document.getElementById("axm-main");
  if (!main) return;

  async function cargarVista(vista, anchor) {
    if (!vista) vista = "dashboard";

    const url = `${BASE}/components/menu.admin.php?v=${encodeURIComponent(
      vista
    )}&partial=1`;

    try {
      const res = await fetch(url, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!res.ok) throw new Error("Error al cargar vista admin");

      const html = await res.text();
      main.innerHTML = html;

      // Mantener SIEMPRE la misma URL visible (/AXM/)
      history.pushState({ vista, anchor }, "", BASE + "/");

      // Marcar enlace activo
      document.querySelectorAll(".axm-nav-link[data-view]").forEach((a) => {
        a.classList.toggle("axm-nav-active", a.dataset.view === vista);
      });

      // Ancla dentro de la vista (opcional)
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
        '<div style="padding:1rem">Ocurrió un error al cargar la vista de administrador.</div>';
    }
  }

  // Interceptar clicks en .axm-nav-link
  document.addEventListener("click", (e) => {
    const link = e.target.closest(".axm-nav-link");
    if (!link) return;

    const vista = link.dataset.view;
    if (!vista) return; // Link normal

    e.preventDefault();
    const anchor = link.dataset.anchor || "";
    cargarVista(vista, anchor);
  });

  // Navegación del navegador (atrás / adelante)
  window.addEventListener("popstate", (e) => {
    const state = e.state || {};
    const vista = state.vista || "dashboard";
    const anchor = state.anchor || "";
    cargarVista(vista, anchor);
  });

  // Estado inicial (para que la barra se quede en /AXM/)
  if (!history.state || !history.state.vista) {
    history.replaceState({ vista: "dashboard", anchor: "" }, "", BASE + "/");
  }
})();
