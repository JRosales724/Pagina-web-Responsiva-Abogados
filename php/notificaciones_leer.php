<?php
declare(strict_types=1);
session_start();

// Debe estar logueado
$uid = (int) ($_SESSION['ID'] ?? 0);
if ($uid <= 0) {
    exit(json_encode([
        'ok' => false,
        'msg' => 'No autorizado'
    ]));
}

// Validar parámetro de notificación
$idNotif = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($idNotif <= 0) {
    exit(json_encode([
        'ok' => false,
        'msg' => 'ID inválido'
    ]));
}

// Cargar conexión
try {
    if (!class_exists('Conexion')) {
        require_once __DIR__ . '/../class/class.conexion.php';
    }

    if (!class_exists('Conexion')) {
        throw new Exception("Clase de conexión no encontrada.");
    }

    $cn = new Conexion();

    // Confirmar que la notificación pertenece al usuario logueado
    $sqlCheck = "SELECT id FROM notificaciones WHERE id = ? AND id_usuario = ? LIMIT 1";
    $st = $cn->prepare($sqlCheck);
    $st->bind_param('ii', $idNotif, $uid);
    $st->execute();
    $rs = $st->get_result();

    if (!$rs->fetch_assoc()) {
        exit(json_encode([
            'ok' => false,
            'msg' => 'Notificación no encontrada o no pertenece al usuario.'
        ]));
    }
    $st->close();

    // Marcar como leída
    $sqlUpdate = "UPDATE notificaciones SET leido = 1, fecha_leido = NOW() WHERE id = ? AND id_usuario = ? LIMIT 1";
    $up = $cn->prepare($sqlUpdate);
    $up->bind_param('ii', $idNotif, $uid);
    $up->execute();
    $up->close();

    echo json_encode([
        'ok' => true,
        'msg' => 'Notificación marcada como leída.'
    ]);
    exit();

} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Error interno.',
        'error' => $e->getMessage()
    ]);
    exit();
}
