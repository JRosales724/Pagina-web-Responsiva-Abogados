<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include '../clases/class.conexion.php';

function jexit($ok, $msg = '', $extra = [])
{
    http_response_code($ok ? 200 : 400);
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function get_json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?? '', true);
    return is_array($data) ? $data : [];
}

function check_csrf(): void
{
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !$sent || !hash_equals($_SESSION['csrf_token'], $sent)) {
        jexit(false, 'CSRF token inválido.');
    }
}

// ====== INICIO ======
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    jexit(false, 'Método no permitido.');
check_csrf();

$in = get_json_input();
$registro_id = (int) ($in['registro_id'] ?? 0);
$pref = trim((string) ($in['contact_preference'] ?? ''));
$correo = trim((string) ($in['correo'] ?? ''));
$telefono = trim((string) ($in['telefono'] ?? ''));

if ($registro_id <= 0 || $pref === '')
    jexit(false, 'Datos incompletos.');
if (!in_array($pref, ['mail', 'call', 'none'], true))
    jexit(false, 'Preferencia no válida.');

$db = new Conexion();

// Validar formulario
$res = $db->query("SELECT id, id_usuario FROM formulario WHERE id = {$registro_id} LIMIT 1");
if (!$res || $res->num_rows === 0)
    jexit(false, 'Formulario no encontrado.');
$row = $res->fetch_assoc();
$user_id = (int) ($row['id_usuario'] ?? 0);

// Actualizar formulario
$sets = ["pref_contact = '" . $db->real_escape_string($pref) . "'"];
if ($correo && filter_var($correo, FILTER_VALIDATE_EMAIL))
    $sets[] = "correo = '" . $db->real_escape_string($correo) . "'";
if ($telefono && preg_match('/^[0-9()\s\-]{7,20}$/', $telefono))
    $sets[] = "telefono = '" . $db->real_escape_string($telefono) . "'";
$sql = "UPDATE formulario SET " . implode(',', $sets) . " WHERE id = {$registro_id}";
if (!$db->query($sql))
    jexit(false, 'Error actualizando formulario: ' . $db->error);

// Sincronizar con usuario
if ($user_id > 0) {
    $update = [];
    if ($correo)
        $update[] = "correo = '" . $db->real_escape_string($correo) . "'";
    if ($telefono)
        $update[] = "num_telefono = '" . $db->real_escape_string($telefono) . "'";
    if ($update) {
        $db->query("UPDATE usuario SET " . implode(',', $update) . " WHERE id = {$user_id}");
    }
}

jexit(true, 'Preferencia guardada correctamente', ['registro_id' => $registro_id, 'pref' => $pref]);
