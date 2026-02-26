<?php
// AXM/partials/navbar_admin.php

$nombre = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Admin'));
if ($nombre === '') {
    $nombre = 'Admin';
}
$ini = mb_substr($nombre, 0, 1, 'UTF-8');
?>
<header class="axm-navbar">
    <div class="nav-inner">
        <a href="/AXM/#dashboard" class="brand axm-nav-link" data-view="dashboard">
            AXM <span>ADMIN</span>
        </a>

        <nav class="nav-links" aria-label="Principal">
            <a class="axm-nav-link" data-view="dashboard" href="/AXM/#dashboard">Inicio</a>
            <a class="axm-nav-link" data-view="consultas" href="/AXM/#consultas">Consultas</a>
            <a class="axm-nav-link" data-view="historial" href="/AXM/#historial">Historial</a>
            <a class="axm-nav-link" data-view="notificaciones" href="/AXM/#notificaciones">Notificaciones</a>
            <a class="axm-nav-link" data-view="usuarios" href="/AXM/#usuarios">Usuarios</a>
            <a class="axm-nav-link" data-view="registro_admin" href="/AXM/#registro_admin">Registrar admin</a>
            <a class="axm-nav-link" data-view="casos" href="/AXM/#casos">Casos</a>
            <a class="axm-nav-link" data-view="ajustes" href="/AXM/#ajustes">Ajustes</a>
        </nav>

        <!-- Perfil -->
        <div class="nav-user">
            <button id="btnUser" class="user-btn" aria-haspopup="menu" aria-expanded="false">
                <div class="avatar avatar-initial">
                    <?= htmlspecialchars(mb_strtoupper($ini, 'UTF-8')); ?>
                </div>
                <span class="user-name"><?= htmlspecialchars($nombre); ?></span>
            </button>
            <div id="userMenu" class="user-menu" role="menu" hidden>
                <a role="menuitem" class="axm-nav-link" data-view="ajustes" href="/AXM/#ajustes">Perfil / Ajustes</a>
                <a role="menuitem" href="/AXM/php/logout.php">Cerrar sesión</a>
            </div>
        </div>

        <!-- Hamburguesa -->
        <button id="btnHamb" class="hamb" aria-label="Abrir menú" aria-controls="drawer"
            aria-expanded="false">☰</button>
    </div>

    <!-- Drawer móvil -->
    <div id="drawer" class="drawer" hidden>
        <a class="axm-nav-link" data-view="dashboard" href="/AXM/#dashboard">Inicio</a>
        <a class="axm-nav-link" data-view="consultas" href="/AXM/#consultas">Consultas</a>
        <a class="axm-nav-link" data-view="historial" href="/AXM/#historial">Historial</a>
        <a class="axm-nav-link" data-view="notificaciones" href="/AXM/#notificaciones">Notificaciones</a>
        <a class="axm-nav-link" data-view="usuarios" href="/AXM/#usuarios">Usuarios</a>
        <a class="axm-nav-link" data-view="registro_admin" href="/AXM/#registro_admin">Registrar admin</a>
        <a class="axm-nav-link" data-view="casos" href="/AXM/#casos">Casos</a>
        <a class="axm-nav-link" data-view="ajustes" href="/AXM/#ajustes">Ajustes</a>
        <hr>
        <a href="/AXM/php/logout.php">Cerrar sesión</a>
    </div>
</header>

<link rel="stylesheet" href="/AXM/assets/css/panel.css">
<script defer src="/AXM/js/panel.admin.js"></script>