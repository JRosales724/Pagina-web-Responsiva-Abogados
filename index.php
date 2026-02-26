<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

// Roles
if (!defined('ROL_USUARIO'))
    define('ROL_USUARIO', 1);
if (!defined('ROL_ADMIN'))
    define('ROL_ADMIN', 2);
if (!defined('ROL_ABOGADO'))
    define('ROL_ABOGADO', 3);

// ===== MODO PÚBLICO: landing completa y salimos
if (empty($_SESSION['ID'])) {
    require __DIR__ . '/components/menu.index.php';
    exit;
}

// ===== MODO APP: shell del panel
require __DIR__ . '/bootstrap.php'; // Debe ser silencioso (sin echo/HTML)

$tipo = isset($_SESSION['tipo']) ? (int) $_SESSION['tipo'] : 0;
$tipoValido = in_array($tipo, [ROL_USUARIO, ROL_ADMIN, ROL_ABOGADO], true);

// Si el rol no es válido, limpiamos y mandamos a landing de AXM
if (!$tipoValido) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }
    session_destroy();
    header('Location: /AXM/');
    exit;
}

function renderVistaPorRol(int $tipo): void
{
    switch ($tipo) {
        case ROL_USUARIO:
            require __DIR__ . '/components/menu.usuario.php';
            break;
        case ROL_ADMIN:
            require __DIR__ . '/components/menu.admin.php';
            break;
        case ROL_ABOGADO:
            // Mientras no tengas una vista de abogado propia:
            require __DIR__ . '/components/menu.admin.php';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AXM ABOGADOS</title>

    <!-- Rutas absolutas para evitar 404 si cambias de subcarpeta -->
    <link rel="icon" type="image/png" href="/AXM/img/img_index/logoaxm.jpeg" />
    <link rel="stylesheet" href="/AXM/css/main.css" />
    <script src="/AXM/js/main.js" defer></script>
</head>

<body>
    <div id="content">
        <?php renderVistaPorRol($tipo); ?>
    </div>
</body>

</html>