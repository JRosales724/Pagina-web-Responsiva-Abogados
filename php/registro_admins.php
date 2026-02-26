<?php
// php/registro_admins.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

session_start();

include '../class/class.conexion.php';
include '../class/class.admin.php';

function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Método no permitido',
    ], 405);
}

// Recibir datos (desde fetch JS)
$camposEsperados = [
    'nombre',
    'apellido_paterno',
    'apellido_materno',
    'email',
    'telefono',
    'username',
    'email_secundario',
    'num_tel_secundario',
    'descripcion',
    'especialidad',
    'estudios',
    'ciudad_ejerce',
    'direccion',
    'departamento',
    'puesto',
    'rol',
    'estado',
];

$dataForm = [];
foreach ($camposEsperados as $campo) {
    $dataForm[$campo] = trim((string) ($_POST[$campo] ?? ''));
}

$password = (string) ($_POST['password'] ?? '');
$password2 = (string) ($_POST['password2'] ?? '');

$errores = [];

// ========== VALIDACIONES ==========
if ($dataForm['username'] === '') {
    $errores[] = 'El username es obligatorio.';
}

if (strlen($password) < 6) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
}

if ($password !== $password2) {
    $errores[] = 'Las contraseñas no coinciden.';
}

if ($dataForm['email'] !== '' && !filter_var($dataForm['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo principal no tiene un formato válido.';
}

if ($dataForm['email_secundario'] !== '' && !filter_var($dataForm['email_secundario'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo secundario no tiene un formato válido.';
}

// Rol y estado válidos
$rolesPermitidos = ['superadmin', 'admin', 'editor'];
$estadosPermitidos = ['activo', 'inactivo', 'suspendido'];

if (!in_array($dataForm['rol'], $rolesPermitidos, true)) {
    $dataForm['rol'] = 'admin';
}
if (!in_array($dataForm['estado'], $estadosPermitidos, true)) {
    $dataForm['estado'] = 'activo';
}

if (!empty($errores)) {
    jsonResponse([
        'success' => false,
        'message' => 'Hay errores en el formulario.',
        'errors' => $errores,
    ], 400);
}

// ========== CREAR ADMIN ==========
$admin = new Admin([
    'nombre' => $dataForm['nombre'] ?: null,
    'apellido_paterno' => $dataForm['apellido_paterno'] ?: null,
    'apellido_materno' => $dataForm['apellido_materno'] ?: null,
    'email' => $dataForm['email'] ?: null,
    'telefono' => $dataForm['telefono'] ?: null,
    'username' => $dataForm['username'],
    'password' => $password,                    // texto plano, la clase lo hashea
    'email_secundario' => $dataForm['email_secundario'] ?: null,
    'num_tel_secundario' => $dataForm['num_tel_secundario'] ?: null,
    'descripcion' => $dataForm['descripcion'] ?: null,
    'especialidad' => $dataForm['especialidad'] ?: null,
    'estudios' => $dataForm['estudios'] ?: null,
    'ciudad_ejerce' => $dataForm['ciudad_ejerce'] ?: null,
    'direccion' => $dataForm['direccion'] ?: null,
    'departamento' => $dataForm['departamento'] ?: null,
    'puesto' => $dataForm['puesto'] ?: null,
    'idioma_preferido' => 'es',
    'tema_visual' => 'claro',
    'preferencia_notificaciones' => null,
    'registrado_el' => date('Y-m-d H:i:s'),
    'estado' => $dataForm['estado'],
    'rol' => $dataForm['rol'],
]);

if ($admin->create()) {
    jsonResponse([
        'success' => true,
        'message' => 'Administrador registrado correctamente.',
        'id' => $admin->getId(),
    ], 200);
}

jsonResponse([
    'success' => false,
    'message' => 'Error al registrar el administrador.',
    'errors' => [$admin->getDbError()],
], 500);
