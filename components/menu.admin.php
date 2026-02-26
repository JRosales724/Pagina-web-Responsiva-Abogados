<?php
// AXM/components/menu.admin.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROL_ADMIN')) {
    define('ROL_ADMIN', 2);
}

// Seguridad: solo admin
if (empty($_SESSION['ID']) || (int) ($_SESSION['tipo'] ?? 0) !== ROL_ADMIN) {
    // Si viene por AJAX, respondemos 401 sin redirigir
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(401);
        exit('NOAUTH');
    }
    header('Location: /AXM/login/');
    exit;
}

// Vista por query ?v=
$vista = $_GET['v'] ?? 'dashboard';

// Whitelist de vistas del admin
$map = [
    'dashboard' => __DIR__ . '/../admin/dashboard.php',
    'consultas' => __DIR__ . '/../admin/consultas.php',
    'historial' => __DIR__ . '/../admin/historial.php',
    'notificaciones' => __DIR__ . '/../admin/notificaciones.php',
    'usuarios' => __DIR__ . '/../admin/usuarios.php',
    'casos' => __DIR__ . '/../admin/casos.php',
    'registro_admin' => __DIR__ . '/../admin/registro_admin.php',
    'ajustes' => __DIR__ . '/../admin/ajustes.php',
];

// ¿Solo contenido (para AJAX)?
$isPartial = isset($_GET['partial']) && $_GET['partial'] === '1';

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

// NAVBAR ADMIN
require_once __DIR__ . '/../partials/navbar_admin.php';

// CONTENEDOR PRINCIPAL
echo '<main id="axm-main" class="axm-main">';

if (isset($map[$vista]) && file_exists($map[$vista])) {
    require $map[$vista];
} else {
    http_response_code(404);
    echo '<div style="padding:1rem">Vista no encontrada.</div>';
}

echo '</main>';

// FOOTER compartido
require_once __DIR__ . '/../partials/footer.php';
