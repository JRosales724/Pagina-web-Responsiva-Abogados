<?php
// AXM/usuario/complementar.php  (Subir / Completar datos)
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uid      = (int) ($_SESSION['ID'] ?? 0);
$nombre   = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? ''));
$correo   = trim((string) ($_SESSION['EMAIL'] ?? ''));
$telSesion = trim((string) ($_SESSION['TEL'] ?? ''));

if ($nombre === '') {
    $nombre = 'Usuario';
}

// ========== Flashes de scripts POST ==========
$flashDatos = $_SESSION['flash_datos'] ?? null;
$flashDocs  = $_SESSION['flash_docs'] ?? null;
unset($_SESSION['flash_datos'], $_SESSION['flash_docs']);

// ========== Datos de usuario desde la BD ==========
$telefono       = $telSesion;
$telefonoAlt    = '';
$estadoRep      = '';
$ciudad         = '';
$curp           = '';
$rfc            = '';
$domicilio      = '';
$tipoPension    = '';
$regimen        = '';

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }
    if ($uid > 0 && class_exists('Conexion')) {
        $cn = new Conexion();
        // Ajusta nombres de columnas según tu tabla `usuarios`
        $sql = "SELECT telefono,
                       telefono_secundario,
                       ciudad,
                       estado,
                       curp,
                       rfc,
                       domicilio,
                       tipo_pension,
                       regimen
                  FROM usuarios
                 WHERE id = ?
                 LIMIT 1";
        if ($st = $cn->prepare($sql)) {
            $st->bind_param('i', $uid);
            if ($st->execute()) {
                if ($row = $st->get_result()->fetch_assoc()) {
                    $telefono    = trim((string) ($row['telefono'] ?? $telefono));
                    $telefonoAlt = trim((string) ($row['telefono_secundario'] ?? ''));
                    $ciudad      = trim((string) ($row['ciudad'] ?? ''));
                    $estadoRep   = trim((string) ($row['estado'] ?? ''));
                    $curp        = trim((string) ($row['curp'] ?? ''));
                    $rfc         = trim((string) ($row['rfc'] ?? ''));
                    $domicilio   = trim((string) ($row['domicilio'] ?? ''));
                    $tipoPension = trim((string) ($row['tipo_pension'] ?? ''));
                    $regimen     = trim((string) ($row['regimen'] ?? ''));
                }
            }
            $st->close();
        }
    }
} catch (\Throwable $e) {
    // silencioso para no romper la vista
}

// ========== Progreso del perfil (visual) ==========
$camposPerfil = [
    $telefono,
    $ciudad,
    $estadoRep,
    $curp,
    $tipoPension,
    $regimen,
    $domicilio,
];

$totalCampos   = count($camposPerfil);
$llenos        = 0;
foreach ($camposPerfil as $c) {
    if (trim((string) $c) !== '') {
        $llenos++;
    }
}
$porcPerfil = $totalCampos > 0 ? (int) round($llenos / $totalCampos * 100) : 0;
if ($porcPerfil < 10 && $llenos > 0) {
    $porcPerfil = 10;   // que no se vea tan feo
}

// ========== Documentos requeridos + estado según carpetas ==========
$docsRequeridos = [
    [
        'id'     => 'ine',
        'label'  => 'Identificación oficial (INE / Pasaporte)',
        'estado' => 'pendiente', // pendiente|subido|rechazado
    ],
    [
        'id'     => 'talon',
        'label'  => 'Talón de pago reciente',
        'estado' => 'pendiente',
    ],
    [
        'id'     => 'negativa',
        'label'  => 'Oficio de negativa o resolución',
        'estado' => 'pendiente',
    ],
    [
        'id'     => 'curp',
        'label'  => 'CURP (PDF o imagen)',
        'estado' => 'pendiente',
    ],
];

// Detectar archivos existentes en /AXM/documents/user_ID/...
$docStatus = [
    'ine'      => false,
    'talon'    => false,
    'negativa' => false,
    'curp'     => false,
];

$baseDocs   = __DIR__ . '/../documents';
$userFolder = $baseDocs . '/user_' . $uid;

$mapFolders = [
    'ine'      => 'INE',
    'talon'    => 'TALON_PAGO',
    'negativa' => 'NEGATIVA',
    'curp'     => 'CURP',
];

foreach ($mapFolders as $slug => $folder) {
    $dir = $userFolder . '/' . $folder;
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        $docStatus[$slug] = !empty($files);
    }
}

// Actualizar estado de docsRequeridos según docStatus
foreach ($docsRequeridos as &$doc) {
    $slug = $doc['id'];
    if (!empty($docStatus[$slug])) {
        $doc['estado'] = 'subido';
    }
}
unset($doc);

// Progreso de documentos
$totalDocs = count($docsRequeridos);
$docsOk    = 0;
foreach ($docsRequeridos as $doc) {
    if ($doc['estado'] === 'subido') {
        $docsOk++;
    }
}
$porcDocs = $totalDocs > 0 ? (int) round($docsOk / $totalDocs * 100) : 0;
?>
<style>
    /* ===== COMPLETAR DATOS (SCOPED) ===== */
    .cmp-wrap {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto
    }

    .cmp-grid {
        display: grid;
        grid-template-columns: 2fr 1.6fr;
        gap: 18px;
        margin-top: 18px
    }

    @media(max-width:960px) {
        .cmp-grid {
            grid-template-columns: 1fr
        }
    }

    .cmp-head {
        background: linear-gradient(135deg, #8A1538 0%, #6E102C 50%, #3A3A3A 100%);
        color: #fff;
        border-radius: 18px;
        padding: 22px 18px;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .18);
        position: relative;
        overflow: hidden
    }

    .cmp-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .18), transparent 60%);
        opacity: .7
    }

    .cmp-title {
        margin: 0;
        font-weight: 900;
        font-size: clamp(1.3rem, 3.6vw, 1.9rem)
    }

    .cmp-sub {
        margin-top: 6px;
        color: #f9fafb;
        max-width: 620px
    }

    .cmp-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 800;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .4)
    }

    .cmp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px
    }

    .btn-white {
        background: #fff;
        color: #111;
        padding: 9px 14px;
        border-radius: 999px;
        font-weight: 900;
        text-decoration: none;
        font-size: .9rem
    }

    .btn-ghost {
        background: rgba(255, 255, 255, .08);
        color: #fff;
        padding: 9px 14px;
        border-radius: 999px;
        font-weight: 800;
        border: 1px solid rgba(255, 255, 255, .25);
        text-decoration: none;
        font-size: .9rem
    }

    .btn-ghost:hover {
        background: rgba(255, 255, 255, .16)
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .06)
    }

    .card h3 {
        margin: 0 0 8px;
        font-size: 1.05rem;
        font-weight: 900;
        color: #111
    }

    .muted {
        color: #6b7280;
        font-size: .9rem
    }

    .cmp-progress {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 10px 0 14px
    }

    .cmp-bar {
        position: relative;
        flex: 1;
        height: 8px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden
    }

    .cmp-bar-fill {
        position: absolute;
        inset: 0;
        width: 0;
        background: #8A1538;
        transition: width .5s ease
    }

    .cmp-pct {
        font-weight: 800;
        color: #111;
        font-size: .9rem
    }

    .cmp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 10px
    }

    @media(max-width:720px) {
        .cmp-form-grid {
            grid-template-columns: 1fr
        }
    }

    .cmp-label {
        display: block;
        font-size: .85rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 4px
    }

    .cmp-input,
    .cmp-select {
        width: 100%;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 8px 10px;
        font-size: .9rem
    }

    .cmp-input:focus,
    .cmp-select:focus {
        outline: none;
        border-color: #8A1538;
        box-shadow: 0 0 0 1px #8A153833
    }

    .cmp-doc-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 8px
    }

    .cmp-doc-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 10px;
        align-items: center;
        padding: 10px;
        border-radius: 12px;
        background: #f9fafb;
        border: 1px solid #e5e7eb
    }

    .cmp-doc-item strong {
        font-size: .9rem;
        color: #111
    }

    .cmp-tag {
        font-size: .75rem;
        font-weight: 800;
        border-radius: 999px;
        padding: 4px 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px
    }

    .cmp-tag.pend {
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #fed7aa
    }

    .cmp-tag.ok {
        background: #ecfdf3;
        color: #166534;
        border: 1px solid #bbf7d0
    }

    .cmp-tag.bad {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca
    }

    .cmp-tag .dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    .cmp-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px dashed #cbd5f5;
        font-size: .8rem;
        font-weight: 700;
        background: #eef2ff;
        color: #3730a3;
        cursor: pointer
    }

    .cmp-upload-btn input {
        display: none
    }

    .cmp-help {
        margin-top: 10px;
        padding: 10px;
        border-radius: 12px;
        background: #f9fafb;
        border: 1px dashed #e5e7eb;
        font-size: .82rem;
        color: #4b5563
    }

    .alert-ok {
        background: #ecfdf3;
        border: 1px solid #bbf7d0;
        color: #166534;
        border-radius: 10px;
        padding: 10px 12px;
        margin: 10px 0;
        font-size: .9rem;
        font-weight: 600;
    }

    .alert-err {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 10px;
        padding: 10px 12px;
        margin: 10px 0;
        font-size: .9rem;
        font-weight: 600;
    }

    .fade {
        opacity: 0;
        transform: translateY(8px);
        animation: cmpFade .45s ease .02s forwards
    }

    .fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: cmpFade .45s ease .16s forwards
    }

    .fade3 {
        opacity: 0;
        transform: translateY(8px);
        animation: cmpFade .45s ease .3s forwards
    }

    @keyframes cmpFade {
        to {
            opacity: 1;
            transform: none
        }
    }
</style>

<div class="cmp-wrap">
    <!-- HEADER -->
    <section class="cmp-head fade">
        <h2 class="cmp-title">Subir / completar datos</h2>
        <p class="cmp-sub">
            Hola <?php echo htmlspecialchars($nombre); ?>. Aquí puedes actualizar tus datos personales
            y subir los documentos necesarios para que el abogado revise tu caso.
        </p>
        <span class="cmp-pill">
            <span>⚙️ Progreso de tu perfil</span>
            <strong><?php echo $porcPerfil; ?>% completo</strong>
        </span>

        <div class="cmp-actions">
            <a class="btn-white axm-nav-link" href="/AXM/" data-view="consultas">
                Ver estatus del trámite
            </a>
            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="historial">
                Ver historial de citas
            </a>
            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="notificaciones">
                Ir a notificaciones
            </a>
        </div>
    </section>

    <?php if ($flashDatos): ?>
        <div class="alert-ok"><?php echo htmlspecialchars($flashDatos); ?></div>
    <?php endif; ?>

    <?php if ($flashDocs): ?>
        <div class="alert-ok">
            <?php
            if (is_array($flashDocs)) {
                echo 'Documentos: ' . htmlspecialchars(implode(' · ', $flashDocs));
            } else {
                echo htmlspecialchars($flashDocs);
            }
            ?>
        </div>
    <?php endif; ?>

    <section class="cmp-grid">
        <!-- FORMULARIO DATOS -->
        <div class="card fade2">
            <h3>Datos personales y de contacto</h3>
            <p class="muted">
                Verifica que tus datos estén actualizados. Estos se usan para las notificaciones y el seguimiento
                de tu caso.
            </p>

            <form method="post" action="/AXM/php/guardar_datos_usuario.php" autocomplete="on">
                <div class="cmp-progress">
                    <div class="cmp-bar">
                        <div class="cmp-bar-fill" data-pct="<?php echo (int) $porcPerfil; ?>"></div>
                    </div>
                    <div class="cmp-pct"><?php echo $porcPerfil; ?>%</div>
                </div>

                <div class="cmp-form-grid">
                    <div>
                        <label class="cmp-label" for="cmp_nombre">Nombre completo</label>
                        <input id="cmp_nombre" name="nombre" type="text" class="cmp-input"
                               value="<?php echo htmlspecialchars($nombre); ?>" required>
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_correo">Correo electrónico</label>
                        <input id="cmp_correo" name="correo" type="email" class="cmp-input"
                               value="<?php echo htmlspecialchars($correo); ?>" required>
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_tel">Teléfono / WhatsApp</label>
                        <input id="cmp_tel" name="telefono" type="tel" class="cmp-input"
                               value="<?php echo htmlspecialchars($telefono); ?>" placeholder="55 0000 0000">
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_tel_alt">Teléfono alterno</label>
                        <input id="cmp_tel_alt" name="telefono_alt" type="tel" class="cmp-input"
                               value="<?php echo htmlspecialchars($telefonoAlt); ?>" placeholder="Opcional">
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_ciudad">Ciudad</label>
                        <input id="cmp_ciudad" name="ciudad" type="text" class="cmp-input"
                               value="<?php echo htmlspecialchars($ciudad); ?>" placeholder="Ej. CDMX">
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_estado">Estado de la República</label>
                        <input id="cmp_estado" name="estado" type="text" class="cmp-input"
                               value="<?php echo htmlspecialchars($estadoRep); ?>" placeholder="Ej. Ciudad de México">
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_curp">CURP</label>
                        <input id="cmp_curp" name="curp" type="text" class="cmp-input"
                               value="<?php echo htmlspecialchars($curp); ?>" placeholder="XXXX000000HDFXXX00">
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_rfc">RFC (opcional)</label>
                        <input id="cmp_rfc" name="rfc" type="text" class="cmp-input"
                               value="<?php echo htmlspecialchars($rfc); ?>" placeholder="XAXX010101000">
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_tipo_pension">Tipo de pensión</label>
                        <select id="cmp_tipo_pension" name="tipo_pension" class="cmp-select">
                            <option value="">Selecciona una opción</option>
                            <option value="jubilacion"        <?php echo $tipoPension === 'jubilacion' ? 'selected' : ''; ?>>Jubilación</option>
                            <option value="edad_tiempo"      <?php echo $tipoPension === 'edad_tiempo' ? 'selected' : ''; ?>>Edad y tiempo</option>
                            <option value="vejez"            <?php echo $tipoPension === 'vejez' ? 'selected' : ''; ?>>Vejez</option>
                            <option value="invalidez"        <?php echo $tipoPension === 'invalidez' ? 'selected' : ''; ?>>Invalidez</option>
                            <option value="riesgo_trabajo"   <?php echo $tipoPension === 'riesgo_trabajo' ? 'selected' : ''; ?>>Riesgo de trabajo</option>
                            <option value="sobrevivencia"    <?php echo $tipoPension === 'sobrevivencia' ? 'selected' : ''; ?>>Sobrevivencia</option>
                        </select>
                    </div>

                    <div>
                        <label class="cmp-label" for="cmp_regimen">Régimen</label>
                        <select id="cmp_regimen" name="regimen" class="cmp-select">
                            <option value="">Selecciona tu régimen</option>
                            <option value="decimo"              <?php echo $regimen === 'decimo' ? 'selected' : ''; ?>>Décimo Transitorio</option>
                            <option value="cuentas_individuales"<?php echo $regimen === 'cuentas_individuales' ? 'selected' : ''; ?>>Cuentas Individuales</option>
                            <option value="otro"                <?php echo $regimen === 'otro' ? 'selected' : ''; ?>>Otro / No estoy seguro</option>
                        </select>
                    </div>

                    <div style="grid-column:1/-1">
                        <label class="cmp-label" for="cmp_domicilio">Domicilio</label>
                        <input id="cmp_domicilio" name="domicilio" type="text" class="cmp-input"
                               value="<?php echo htmlspecialchars($domicilio); ?>"
                               placeholder="Calle, número, colonia, CP, municipio/alcaldía">
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label style="font-size:.82rem;color:#4b5563;">
                        <input type="checkbox" name="acepto" value="1">
                        Confirmo que los datos proporcionados son correctos.
                    </label>
                </div>

                <div style="margin-top:14px;display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap">
                    <button type="reset" class="btn-ghost" style="border-radius:10px">
                        Limpiar
                    </button>
                    <button type="submit" class="btn-white" style="border-radius:10px">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- DOCUMENTOS -->
        <div class="card fade3">
            <h3>Documentos del expediente</h3>
            <p class="muted">
                Sube fotos o PDFs claros. Si algún archivo es ilegible, te lo notificaremos para que lo reemplaces.
            </p>

            <div class="cmp-progress">
                <div class="cmp-bar">
                    <div class="cmp-bar-fill" data-pct="<?php echo (int) $porcDocs; ?>"></div>
                </div>
                <div class="cmp-pct"><?php echo $porcDocs; ?>%</div>
            </div>

            <form method="post" action="/AXM/php/subir_documentos.php" enctype="multipart/form-data">
                <div class="cmp-doc-list">
                    <?php foreach ($docsRequeridos as $doc):
                        $tagClass = 'pend';
                        $tagText  = 'Pendiente';
                        if ($doc['estado'] === 'subido') {
                            $tagClass = 'ok';
                            $tagText  = 'Subido';
                        } elseif ($doc['estado'] === 'rechazado') {
                            $tagClass = 'bad';
                            $tagText  = 'Revisar';
                        }
                        ?>
                        <div class="cmp-doc-item">
                            <div>
                                <strong><?php echo htmlspecialchars($doc['label']); ?></strong>
                                <div class="muted">Formatos: JPG, PNG o PDF · máx. 5 MB</div>
                            </div>

                            <span class="cmp-tag <?php echo $tagClass; ?>">
                                <span class="dot"></span><?php echo $tagText; ?>
                            </span>

                            <label class="cmp-upload-btn">
                                Subir archivo
                                <input type="file" name="doc_<?php echo htmlspecialchars($doc['id']); ?>" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif">
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:14px;display:flex;justify-content:flex-end">
                    <button type="submit" class="btn-white" style="border-radius:10px">
                        Guardar / enviar documentos
                    </button>
                </div>

                <div class="cmp-help">
                    💡 <strong>Tip:</strong> si tienes todos tus documentos listos, súbelos en una sola sesión.
                    Así el abogado puede revisar tu expediente de una vez y avanzar más rápido.
                </div>
            </form>
        </div>
    </section>
</div>

<script>
    // Animar barras de progreso de esta vista
    (function () {
        document.querySelectorAll('.cmp-bar-fill').forEach(function (el) {
            const pct = parseInt(el.getAttribute('data-pct') || '0', 10);
            requestAnimationFrame(() => {
                el.style.width = pct + '%';
            });
        });
    })();
</script>
