// AXM/assets/js/panel.js

// Drawer móvil
const btnHamb = document.getElementById("btnHamb");
const drawer = document.getElementById("drawer");
if (btnHamb && drawer) {
  btnHamb.addEventListener("click", () => {
    const isOpen = !drawer.hasAttribute("hidden");
    if (isOpen) {
      drawer.setAttribute("hidden", "");
      btnHamb.setAttribute("aria-expanded", "false");
    } else {
      drawer.removeAttribute("hidden");
      btnHamb.setAttribute("aria-expanded", "true");
    }
  });
  // cerrar al tocar fuera
  document.addEventListener("click", (e) => {
    if (!drawer.contains(e.target) && !btnHamb.contains(e.target)) {
      drawer.setAttribute("hidden", "");
      btnHamb.setAttribute("aria-expanded", "false");
    }
  });
}

// Menú de usuario (avatar)
const btnUser = document.getElementById("btnUser");
const userMenu = document.getElementById("userMenu");
if (btnUser && userMenu) {
  btnUser.addEventListener("click", () => {
    const open = !userMenu.hasAttribute("hidden");
    if (open) {
      userMenu.setAttribute("hidden", "");
      btnUser.setAttribute("aria-expanded", "false");
    } else {
      userMenu.removeAttribute("hidden");
      btnUser.setAttribute("aria-expanded", "true");
    }
  });
  document.addEventListener("click", (e) => {
    if (!userMenu.contains(e.target) && !btnUser.contains(e.target)) {
      userMenu.setAttribute("hidden", "");
      btnUser.setAttribute("aria-expanded", "false");
    }
  });
}
