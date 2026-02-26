<?php
// AXM/php/vistas_usuario.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['ID'])) {
    http_response_code(401);
    exit('No autorizado');
}

$vista = $_GET['v'] ?? 'inicio';

$base = __DIR__ . '/../usuario/';

$map = [
    'inicio'        => $base . 'inicio.php',
    'consultas'     => $base . 'consultas.php',
    'historial'     => $base . 'historial.php',
    'notificaciones'=> $base . 'notificaciones.php',
    'complementar'  => $base . 'complementar.php',
    'ajustes'       => $base . 'ajustes.php',
];

if (!isset($map[$vista]) || !file_exists($map[$vista])) {
    http_response_code(404);
    exit('Vista no encontrada');
}

ob_start();
require $map[$vista];
$html = ob_get_clean();

header('Content-Type: text/html; charset=utf-8');
echo $html;
