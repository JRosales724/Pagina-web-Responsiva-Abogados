<?php
// php/login.php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

// ====== Constantes de rol (coinciden con tu index.php) ======
if (!defined('ROL_USUARIO'))
    define('ROL_USUARIO', 1);
if (!defined('ROL_ADMIN'))
    define('ROL_ADMIN', 2);
if (!defined('ROL_ABOGADO'))
    define('ROL_ABOGADO', 3); // reservado

// ====== Carga de clases ======
require_once __DIR__ . '/../class/class.conexion.php';
require_once __DIR__ . '/../class/class.admin.php';
require_once __DIR__ . '/../class/class.usuario.php';

// ====== Helper para responder JSON cuando venga fetch ======
function wants_json(): bool
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        return true;
    $ct = $_SERVER['HTTP_ACCEPT'] ?? '';
    return stripos($ct, 'application/json') !== false;
}

function json_out(int $status, array $payload)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

// ====== Sólo POST ======
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    if (wants_json())
        json_out(405, ['ok' => false, 'error' => 'Método no permitido']);
    header('Location: ../login/?e=method');
    exit;
}

// Acepta tanto x-www-form-urlencoded como application/json
$input = $_POST;
$ctype = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($ctype, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
}

// Campos del form (tu form usa name="username" y name="password")
$username = trim((string) ($input['username'] ?? ''));   // puede ser correo o username admin
$password = (string) ($input['password'] ?? '');
$remember = !empty($input['remember']);

// Validación mínima
if ($username === '' || $password === '') {
    $msg = 'Completa usuario y contraseña.';
    if (wants_json())
        json_out(422, ['ok' => false, 'error' => $msg]);
    header('Location: ../login/?e=empty');
    exit;
}

// ====== 1) Intentar ADMIN ======
$device = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 180);
$adminOK = Admin::login($username, $password, $device); // tu clase ya hace password_verify

if ($adminOK === 1) {
    // Asegurar que index.php nos reconozca como sesión válida y tipo admin:
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        // La Admin::login ya setea $_SESSION['ID'] y otras; nos aseguramos de 'tipo'
        $_SESSION['tipo'] = ROL_ADMIN;
    }

    // "Remember me" (duración de cookie de sesión) — opcional suave
    if ($remember) {
        // extiende el lifetime de la cookie de sesión (depende de configuración del server)
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'expires' => time() + 60 * 60 * 24 * 14, // 14 días
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    if (wants_json())
        json_out(200, ['ok' => true, 'role' => 'admin']);
    header('Location: ../');
    exit;
}

// ====== 2) Intentar USUARIO (tratamos $username como correo) ======
$user = Usuario::autenticar($username, $password);

if ($user) {
    session_regenerate_id(true);
    $_SESSION['ID'] = (int) $user['id'];
    $_SESSION['tipo'] = ROL_USUARIO;
    $_SESSION['EMAIL'] = $user['correo'] ?? null;
    $_SESSION['NOMBRE'] = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido_paterno'] ?? ''));

    if ($remember) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'expires' => time() + 60 * 60 * 24 * 14,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    if (wants_json())
        json_out(200, ['ok' => true, 'role' => 'usuario']);
    header('Location: ../');
    exit;
}

// ====== Falló todo ======
$msg = 'Credenciales inválidas.';
if (wants_json())
    json_out(401, ['ok' => false, 'error' => $msg]);
header('Location: ../login/?e=invalid');
exit;
