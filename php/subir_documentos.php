<?php
// AXM/php/subir_documentos.php
declare(strict_types=1);

session_start();

if (empty($_SESSION['ID'])) {
    header('Location: /AXM/login/');
    exit;
}

$uid = (int) $_SESSION['ID'];

// ========== Configuración de almacenamiento ==========
$baseDir = dirname(__DIR__) . '/documents';      // /AXM/documents
$userDir = $baseDir . '/user_' . $uid;

if (!is_dir($baseDir)) {
    @mkdir($baseDir, 0775, true);
}
if (!is_dir($userDir)) {
    @mkdir($userDir, 0775, true);
}

// Mapeo campo->carpeta
$tipos = [
    'doc_ine' => 'INE',
    'doc_talon' => 'TALON_PAGO',
    'doc_negativa' => 'NEGATIVA',
    'doc_curp' => 'CURP',
];

// Extensiones permitidas
$extPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'heic', 'heif'];

$resultados = [];

// ========== Procesar archivos ==========
foreach ($tipos as $campo => $carpeta) {
    if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        continue; // no se subió nada en este campo
    }

    $f = $_FILES[$campo];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $resultados[] = "$carpeta: error (" . $f['error'] . ")";
        continue;
    }

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extPermitidas, true)) {
        $resultados[] = "$carpeta: extensión no permitida ($ext)";
        continue;
    }

    // Crear carpeta para ese tipo
    $destDir = $userDir . '/' . $carpeta;
    if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
        $resultados[] = "$carpeta: no se pudo crear la carpeta de destino";
        continue;
    }

    $baseName = preg_replace('/[^a-z0-9]+/i', '-', pathinfo($f['name'], PATHINFO_FILENAME));
    if ($baseName === '') {
        $baseName = strtolower($carpeta);
    }

    $nuevoNombre = date('Ymd_His') . '_' . $baseName . '.' . $ext;
    $destPath = $destDir . '/' . $nuevoNombre;

    if (!move_uploaded_file($f['tmp_name'], $destPath)) {
        $resultados[] = "$carpeta: no se pudo guardar el archivo";
        continue;
    }

    // Opcional: registrar en BD
    try {
        require_once __DIR__ . '/../class/class.conexion.php';
        if (class_exists('Conexion')) {
            $cn = new Conexion();

            // Crea esta tabla si no existe o ajusta nombres:
            // documentos_usuario(id, id_usuario, tipo, ruta, nombre_original, fecha_subida)
            $sql = "INSERT INTO documentos_usuario
                    (id_usuario, tipo, ruta, nombre_original, fecha_subida)
                    VALUES (?, ?, ?, ?, NOW())";

            if ($stmt = $cn->prepare($sql)) {
                $rutaRel = 'documents/user_' . $uid . '/' . $carpeta . '/' . $nuevoNombre;
                $tipo = $carpeta;
                $orig = substr((string) $f['name'], 0, 200);

                $stmt->bind_param('isss', $uid, $tipo, $rutaRel, $orig);
                $stmt->execute();
                $stmt->close();
            }
        }
    } catch (\Throwable $e) {
        // Silencioso; si algo falla en el log, no rompemos la subida.
    }

    $resultados[] = "$carpeta: archivo guardado correctamente";
}

// Mensaje para la vista
if (empty($resultados)) {
    $_SESSION['flash_docs'] = 'No se seleccionó ningún archivo.';
} else {
    $_SESSION['flash_docs'] = $resultados;
}

// Volvemos al panel en "complementar"
header('Location: /AXM/#complementar');
exit;
