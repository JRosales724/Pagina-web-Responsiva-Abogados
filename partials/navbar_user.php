<?php
// AXM/partials/navbar_user.php
?>
<header class="axm-navbar">
    <div class="nav-inner">
        <a href="#" class="brand" data-view="inicio">AXM <span>ABOGADOS</span></a>

        <nav class="nav-links" aria-label="Principal">
            <a href="#" class="axm-nav-link" data-view="inicio">Inicio</a>
            <a href="#" class="axm-nav-link" data-view="consultas">Consultas</a>
            <a href="#" class="axm-nav-link" data-view="historial">Historial</a>
            <a href="#" class="axm-nav-link" data-view="notificaciones">Notificaciones</a>
            <a href="#" class="axm-nav-link" data-view="complementar">Complementar datos</a>
            <a href="#" class="axm-nav-link" data-view="ajustes">Ajustes del perfil</a>
        </nav>

        <!-- Perfil -->
        <div class="nav-user">
            <button id="btnUser" class="user-btn" aria-haspopup="menu" aria-expanded="false">
                <img src="/AXM/img/usuarios/avatar_default.png" alt="Perfil" class="avatar">
            </button>
            <div id="userMenu" class="user-menu" role="menu" hidden>
                <a role="menuitem" href="/AXM/components/menu.usuario.php?v=ajustes">Perfil / Ajustes</a>
                <a role="menuitem" href="/AXM/php/logout.php">Cerrar sesión</a>
            </div>
        </div>

        <!-- Hamburguesa -->
        <button id="btnHamb" class="hamb" aria-label="Abrir menú" aria-controls="drawer"
            aria-expanded="false">☰</button>
    </div>

    <!-- Drawer móvil -->
    <div id="drawer" class="drawer" hidden>
        <a href="#" class="axm-nav-link" data-view="inicio">Inicio</a>
        <a href="#" class="axm-nav-link" data-view="consultas">Consultas</a>
        <a href="#" class="axm-nav-link" data-view="historial">Historial</a>
        <a href="#" class="axm-nav-link" data-view="notificaciones">Notificaciones</a>
        <a href="#" class="axm-nav-link" data-view="complementar">Complementar datos</a>
        <a href="#" class="axm-nav-link" data-view="ajustes">Ajustes del perfil</a>
        <hr>
        <a href="/AXM/php/logout.php">Cerrar sesión</a>
    </div>
</header>

<link rel="stylesheet" href="/AXM/assets/css/panel.css">
<script defer src="/AXM/assets/js/panel.js"></script>
<script defer src="/AXM/js/panel.usuario.js"></script>