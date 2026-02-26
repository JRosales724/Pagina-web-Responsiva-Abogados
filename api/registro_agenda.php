<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ====== IMPORTAR CLASES ======
include '../class/class.conexion.php';
include '../class/class.agenda.php';
include '../class/class.formulario.php';
include '../class/class.usuario.php';

// ====== FUNCIONES AUXILIARES ======
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jexit(false, 'Método no permitido.');
}
check_csrf();

$in = get_json_input();

// Validaciones mínimas
$registro_id = (int) ($in['registro_id'] ?? 0);     // id del formulario
$id_usuario = (int) ($in['id_usuario'] ?? 0);     // id del usuario (si ya existe)
$datetime = trim((string) ($in['appointment_datetime'] ?? ''));
$tipo_cita = strtoupper(trim((string) ($in['mode'] ?? 'VIDEO'))); // VIDEO | PRESENCIAL | TELEFONICA | CORREO

if ($registro_id <= 0 || $datetime === '') {
    jexit(false, 'Datos incompletos.');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}(:\d{2})?$/', $datetime)) {
    jexit(false, 'Formato de fecha/hora inválido.');
}

$ts = strtotime($datetime);
if ($ts === false || $ts <= time()) {
    jexit(false, 'La cita debe ser en el futuro.');
}

// ====== OBTENER FECHA/HORA ======
$fecha = date('Y-m-d', $ts);
$hora = date('H:i:s', $ts);

// ====== CONEXIÓN A BD ======
$db = new Conexion();

// ====== VALIDAR FORMULARIO EXISTENTE ======
$resForm = $db->prepare("SELECT id, nombre, apellido_paterno, apellido_materno, telefono, correo, dependencia_laboraba 
                         FROM formulario WHERE id = ? LIMIT 1");
$resForm->bind_param('i', $registro_id);
$resForm->execute();
$formData = $resForm->get_result()->fetch_assoc();
$resForm->close();

if (!$formData) {
    jexit(false, 'Formulario no encontrado.');
}

// ====== OBTENER DATOS DEL USUARIO SI EXISTE ======
$usuarioData = null;
if ($id_usuario > 0) {
    $resUsr = $db->prepare("SELECT id, nombre, correo, num_telefono FROM usuarios WHERE id = ? LIMIT 1");
    $resUsr->bind_param('i', $id_usuario);
    $resUsr->execute();
    $usuarioData = $resUsr->get_result()->fetch_assoc();
    $resUsr->close();
}

// ====== CREAR OBJETO DE AGENDA ======
Agenda::ensureTable();

$agendaData = [
    'id_cliente' => $id_usuario ?: null,
    'id_formulario' => $registro_id,
    'nom_cliente' => $formData['nombre'] ?? null,
    'num_tel_cliente' => $formData['telefono'] ?? null,
    'correo_cliente' => $formData['correo'] ?? null,
    'dependencia_laboraba' => $formData['dependencia_laboraba'] ?? null,
    'tipo_de_cita' => $tipo_cita,
    'fecha_cita' => $fecha,
    'hora_cita' => $hora,
    'cliente_registro_cita' => 'web',
    'estado_cita' => 'programada',
    'prioridad_cita' => 'media'
];

$nuevaCita = new Agenda($agendaData);

// ====== VALIDAR SLOT DISPONIBLE ======
if (!Agenda::isSlotDisponible($fecha, $hora, $tipo_cita)) {
    jexit(false, 'El horario seleccionado ya está ocupado.');
}

// ====== CREAR LA CITA ======
if (!$nuevaCita->create()) {
    jexit(false, 'Error al crear la cita: ' . $nuevaCita->getDbError());
}

$agenda_id = $nuevaCita->getId();

// ====== ACTUALIZAR FORMULARIO CON CITA ======
$upd = $db->prepare("UPDATE formulario 
                     SET APPT_DATE = ?, APPT_TIME = ?, PREF_CONTACT = ? 
                     WHERE id = ?");
$pref = strtolower($tipo_cita);
$upd->bind_param('sssi', $fecha, $hora, $pref, $registro_id);
if (!$upd->execute()) {
    jexit(false, 'Error actualizando formulario: ' . $upd->error);
}
$upd->close();

// ====== RESPUESTA FINAL ======
jexit(true, 'Cita agendada correctamente.', [
    'agenda_id' => $agenda_id,
    'registro_id' => $registro_id,
    'usuario_id' => $id_usuario,
    'fecha' => $fecha,
    'hora' => $hora,
    'tipo_cita' => $tipo_cita
]);
