<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ====== IMPORTAR CLASES ======
include '../class/class.conexion.php';
include '../class/class.usuario.php';
include '../class/class.formulario.php';
include '../class/class.agenda.php';

// ====== HELPERS ======
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


// ESTA FUNCION ESTA COMENTADA PORQUE GENERA CONTRASEÑAS ALEATORIAS UN TANTO COMPLICADAS, SE REMPLAZO POR UNA MAS SIMPLE

// function random_password(int $len = 10): string
// {
//     $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#%*?';
//     $pw = '';
//     for ($i = 0; $i < $len; $i++) {
//         $pw .= $chars[random_int(0, strlen($chars) - 1)];
//     }
//     return $pw;
// }

// SE REMPLAZO POR ESTA FUNCION QUE HACE UNA MEZCLA DE NOMBRE + APELLIDO PATERNO + 4 NUMEROS ALEATORIOS
function random_password_simple(string $nombre = '', string $apellido = ''): string
{
    $base = $nombre . $apellido;
    // quitar acentos/ñ -> n; usa iconv si está disponible
    $base = iconv('UTF-8', 'ASCII//TRANSLIT', $base);
    $base = strtolower(preg_replace('/\s+/', '', $base));
    $base = preg_replace('/[^a-z0-9]/', '', $base); // solo alfanumérico
    $base = substr($base, 0, 10);
    $num = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    return $base . $num;
}


function norm_tipo_cita(string $mode): string
{
    $m = strtolower(trim($mode));
    switch ($m) {
        case 'presencial':
            return 'PRESENCIAL';
        case 'telefonica':
        case 'tel':
            return 'TELEFONICA';
        case 'correo':
            return 'CORREO';
        case 'video':
        case 'virtual':
        default:
            return 'VIDEO';
    }
}

function build_wa_link(string $telefono, string $texto): string
{
    $num = preg_replace('/\D+/', '', $telefono);
    // Ajusta el prefijo país si lo necesitas (ej. México 52)
    if (strpos($num, '52') !== 0) {
        $num = '52' . $num;
    }
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($texto);
}

// ====== INICIO ======
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jexit(false, 'Método no permitido.');
}
check_csrf();

$in = get_json_input();
$created_at = date('Y-m-d H:i:s');

// ===== DATOS DE CONTACTO =====
$nombre = trim((string) ($in['nombre'] ?? ''));
$ap_pat = trim((string) ($in['ap_pat'] ?? ''));
$ap_mat = trim((string) ($in['ap_mat'] ?? ''));
$telefono = trim((string) ($in['telefono'] ?? ''));
$correo = trim((string) ($in['correo'] ?? ''));
$comentarios = trim((string) ($in['comentarios'] ?? ''));
$dependencia = trim((string) ($in['dependencia_laboraba'] ?? ''));

// ===== CITA =====
$tipo_cita_in = (string) ($in['tipo_de_cita'] ?? $in['mode'] ?? 'VIDEO');
$tipo_cita = norm_tipo_cita($tipo_cita_in);
$appt_date = trim((string) ($in['appt_date'] ?? ''));
$appt_time = trim((string) ($in['appt_time'] ?? ''));

// ===== FLOW (opcional) =====
$respuestas = is_array($in['respuestas'] ?? null) ? $in['respuestas'] : [];
$last_end = isset($in['last_end']) ? (string) $in['last_end'] : null;

// ===== VALIDACIONES =====
if ($nombre === '' || $ap_pat === '' || $ap_mat === '' || $telefono === '' || $correo === '') {
    jexit(false, 'Faltan datos de contacto obligatorios.');
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    jexit(false, 'Correo electrónico inválido.');
}
if ($appt_date === '' || $appt_time === '') {
    jexit(false, 'Fecha y hora de cita no válidas.');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appt_date)) {
    jexit(false, 'Formato de fecha inválido (YYYY-MM-DD).');
}
if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $appt_time)) {
    jexit(false, 'Formato de hora inválido (HH:MM o HH:MM:SS).');
}
$dt = strtotime($appt_date . ' ' . $appt_time);
if ($dt === false || $dt <= time()) {
    jexit(false, 'La cita debe ser en el futuro.');
}

// ===== CONEXIÓN =====
$db = new Conexion();

// ===== ASEGURAR TABLAS =====
Usuario::ensureTable();
Agenda::ensureTable();

// ===== 1) USUARIO: crear o reutilizar (SIEMPRE devolver password) =====
$existing = Usuario::findByEmailOrPhone($correo, $telefono);
$user_id = null;
$temp_password = null;

if ($existing && !empty($existing['id'])) {
    // Existe -> regenerar password y actualizar
    $user_id = (int) $existing['id'];

    // SE COMENTO ESTA LINEA PORQUE ERA EN CONJUNTO CON LA FUNCION ANTERIOR, EN CASO DE USARSE LA ANTERIOR SE QUITA EL COMENTARIO
    // $temp_password = random_password(10);

    //SE REMPLAZO POR ESTA OTRA FUNCION
    $temp_password = random_password_simple($nombre, $ap_pat);

    $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);

    $updPwd = $db->prepare("UPDATE usuarios SET contrasena = ?, ultimo_cambio_contrasena = NOW() WHERE id = ?");
    if (!$updPwd) {
        jexit(false, 'Error preparando actualización de contraseña: ' . $db->error);
    }
    $updPwd->bind_param('si', $password_hash, $user_id);
    if (!$updPwd->execute()) {
        jexit(false, 'Error actualizando contraseña del usuario existente: ' . $updPwd->error);
    }
    $updPwd->close();

    // Usuario NO existe => crearlo con contraseña simple
} else {
    // genera simple: nombre + apellido + 4 dígitos (tu función)
    $plain_simple = random_password_simple($nombre, $ap_pat);

    $usr = new Usuario([
        'nombre' => $nombre,
        'apellido_paterno' => $ap_pat,
        'apellido_materno' => $ap_mat,
        'num_telefono' => $telefono,
        'correo' => $correo,
        'dependencia_laboraba' => $dependencia,
        'comentarios' => $comentarios,
        'fecha_registro' => $created_at,
        'contrasena' => $plain_simple,   // <- **AQUÍ LA PASAS EN CLARO**
    ]);

    if (!$usr->create()) {
        jexit(false, 'Error creando usuario: ' . $usr->getDbError());
    }
    $user_id = (int) $usr->getId();
    // tras el cambio en ensurePassword(), getTempPassword devolverá la misma simple
    $temp_password = (string) ($usr->getTempPassword() ?: $plain_simple);
}

// ===== 2) FORMULARIO =====
$formPayload = [
    'id_usuario' => $user_id,
    'pensionado_federal' => $respuestas['pensionado_federal'] ?? null,
    'tipo_tramite' => $respuestas['tipo_tramite'] ?? null,

    // Contacto redundante
    'nombre' => $nombre,
    'apellido_paterno' => $ap_pat,
    'apellido_materno' => $ap_mat,
    'telefono' => $telefono,
    'correo' => $correo,
    'dependencia_laboraba' => $dependencia,
    'comentarios' => $comentarios,

    // Preferencia derivada
    'pref_contact' => ($tipo_cita === 'TELEFONICA' ? 'call' : 'mail'),
    'appt_date' => $appt_date,
    'appt_time' => (strlen($appt_time) === 5 ? $appt_time . ':00' : $appt_time),
    'created_at' => $created_at,

    // Flow completo
    'flow_answers' => $respuestas,
    'flow_last_end' => $last_end
];

$form = Formulario::fromPayload($db, $formPayload);
if (!$form->create()) {
    jexit(false, 'Error creando formulario: ' . $form->getDbError());
}
$form_id = (int) $form->getId();

// Asegurar back-link en FORMULARIO
$updForm = $db->prepare("UPDATE formulario SET id_usuario = ? WHERE id = ?");
$updForm->bind_param('ii', $user_id, $form_id);
$updForm->execute();
$updForm->close();

// ===== 3) AGENDA (validar disponibilidad, crear y enlazar) =====
$fecha_sql = date('Y-m-d', $dt);
$hora_sql = date('H:i:s', $dt);

if (!Agenda::isSlotDisponible($fecha_sql, $hora_sql, $tipo_cita)) {
    jexit(false, 'El horario seleccionado ya está ocupado.');
}

$ag = new Agenda([
    'id_cliente' => $user_id,
    'id_formulario' => $form_id,
    'nom_cliente' => $nombre,
    'num_tel_cliente' => $telefono,
    'correo_cliente' => $correo,
    'dependencia_laboraba' => $dependencia,
    'tipo_de_cita' => $tipo_cita,
    'fecha_cita' => $fecha_sql,
    'hora_cita' => $hora_sql,
    'cliente_registro_cita' => 'web',
    'estado_cita' => 'programada',
    'prioridad_cita' => 'media'
]);

if (!$ag->create()) {
    jexit(false, 'Error creando cita: ' . $ag->getDbError());
}
$agenda_id = (int) $ag->getId();

// ===== 4) Enlaces cruzados garantizados =====
$updUsr = $db->prepare("UPDATE usuarios SET id_formulario = ?, id_cita = ? WHERE id = ?");
$updUsr->bind_param('iii', $form_id, $agenda_id, $user_id);
if (!$updUsr->execute()) {
    jexit(false, 'Error enlazando usuario: ' . $updUsr->error);
}
$updUsr->close();

// formulario.APPT_DATE/APPT_TIME por consistencia
$updForm2 = $db->prepare("UPDATE formulario SET APPT_DATE = ?, APPT_TIME = ? WHERE id = ?");
$appt_time_sql = $hora_sql;
$updForm2->bind_param('ssi', $fecha_sql, $appt_time_sql, $form_id);
$updForm2->execute();
$updForm2->close();

// ===== 5) Log/simulación de envío de credenciales (opcional)
$waText = "Hola {$nombre}, tu registro en AXM ABOGADOS fue exitoso.\n"
    . "Usuario: {$correo}\n"
    . "Contraseña temporal: {$temp_password}\n"
    . "Cita: {$fecha_sql} {$hora_sql} ({$tipo_cita}).\n\n"
    . "Guarda esta contraseña; podrás cambiarla al iniciar sesión.";
$waLink = build_wa_link($telefono, $waText);

if (!empty($temp_password)) {
    @file_put_contents('../logs/envios_whatsapp.log', '[' . date('c') . "] {$telefono}: {$waText}\n", FILE_APPEND);
}

// ===== RESPUESTA =====
jexit(true, 'Registro completado con éxito.', [
    'user_id' => $user_id,
    'form_id' => $form_id,
    'agenda_id' => $agenda_id,
    'usuario' => $correo,
    'password' => $temp_password, // SIEMPRE devuelto (nuevo o regenerado)
    'fecha_cita' => $fecha_sql,
    'hora_cita' => $hora_sql,
    'tipo_cita' => $tipo_cita,
    'wa_link' => $waLink
]);
