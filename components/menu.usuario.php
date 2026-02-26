<?php
// AXM/components/menu.usuario.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión => al login
if (empty($_SESSION['ID'])) {
    header('Location: /AXM/login/');
    exit;
}

// ¿Es petición parcial? (para AJAX / fetch)
$isPartial = isset($_GET['partial']) && $_GET['partial'] === '1';

// Vista solicitada (?v=)
$vista = $_GET['v'] ?? 'inicio';

// Mapa seguro de vistas
$map = [
    'inicio' => __DIR__ . '/../usuario/inicio.php',
    'consultas' => __DIR__ . '/../usuario/consultas.php',
    'historial' => __DIR__ . '/../usuario/historial.php',
    'notificaciones' => __DIR__ . '/../usuario/notificaciones.php',
    'complementar' => __DIR__ . '/../usuario/complementar.php',
    'ajustes' => __DIR__ . '/../usuario/ajustes.php',
];

// ---------- MODO PARCIAL (para JS) ----------
if ($isPartial) {
    if (isset($map[$vista]) && file_exists($map[$vista])) {
        require $map[$vista];
    } else {
        http_response_code(404);
        echo '<div style="padding:1rem">Vista no encontrada.</div>';
    }
    exit;
}

// ---------- MODO NORMAL (llamado desde index.php) ----------

// NAVBAR
require_once __DIR__ . '/../partials/navbar_user.php';

// CONTENEDOR PRINCIPAL
echo '<main id="axm-main" class="axm-main">';

if (isset($map[$vista]) && file_exists($map[$vista])) {
    require $map[$vista];
} else {
    http_response_code(404);
    echo '<div style="padding:1rem">Vista no encontrada.</div>';
}

echo '</main>';

// FOOTER
require_once __DIR__ . '/../partials/footer.php';
