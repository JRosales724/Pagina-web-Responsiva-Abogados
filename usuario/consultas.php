<?php
// AXM/usuario/consultas.php  (Estatus del trámite)
declare(strict_types=1);

$uid = (int) ($_SESSION['ID'] ?? 0);
$nombre = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? ''));
if ($nombre === '') {
    $nombre = 'Usuario';
}

// Valores por defecto
$estadoActual = 'pendiente'; // pendiente|en-documentacion|en-revision|en-tramite|aprobado|rechazado
$numExpediente = null;
$historial = []; // [['estado'=>'en-revision','nota'=>'...','fecha'=>'2025-02-01 10:20:00'],...]

/** Helper: normalizar mapeo a etapa de timeline */
function estado_a_etapa(string $estado): string
{
    $estado = strtolower($estado);
    // mapeos “amplios” para lo que salga de la BD
    return match (true) {
        in_array($estado, ['pendiente', 'registro']) => 'registro',
        in_array($estado, ['documentacion', 'en-documentacion', 'docs']) => 'documentacion',
        in_array($estado, ['revision', 'en-revision', 'validacion']) => 'revision',
        in_array($estado, ['tramite', 'en-tramite', 'demanda']) => 'tramite',
        in_array($estado, ['aprobado', 'resuelto', 'resolucion']) => 'resolucion',
        in_array($estado, ['rechazado', 'observaciones', 'devuelto']) => 'rechazado',
        default => 'registro',
    };
}

try {
    // Cargar conexión si existe
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }
    if (class_exists('Conexion') && $uid > 0) {
        $bd = new Conexion();

        // Leer estado actual y expediente del usuario
        if ($stmt = $bd->prepare("SELECT estado_validacion, Num_expediente, ultima_actualizacion_datos FROM usuarios WHERE id = ? LIMIT 1")) {
            $stmt->bind_param('i', $uid);
            if ($stmt->execute()) {
                if ($row = $stmt->get_result()->fetch_assoc()) {
                    $estadoRaw = (string) ($row['estado_validacion'] ?? 'pendiente');
                    // Mapeamos el estado “general” a algo más fino para el timeline
                    $estadoActual = match ($estadoRaw) {
                        'aprobado' => 'aprobado',
                        'rechazado' => 'rechazado',
                        default => 'en-documentacion'
                    };
                    $numExpediente = $row['Num_expediente'] ?? null;
                    $ultimaAct = $row['ultima_actualizacion_datos'] ?? null;
                }
            }
            $stmt->close();
        }

        // Intentar leer histórico si la tabla existe
        $tieneTablaHist = false;
        if ($res = $bd->query("SHOW TABLES LIKE 'estatus_historial'")) {
            $tieneTablaHist = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneTablaHist) {
            if ($stmt = $bd->prepare("SELECT estado, nota, fecha FROM estatus_historial WHERE id_usuario = ? ORDER BY fecha DESC, id DESC LIMIT 40")) {
                $stmt->bind_param('i', $uid);
                if ($stmt->execute()) {
                    $rs = $stmt->get_result();
                    while ($r = $rs->fetch_assoc()) {
                        $historial[] = [
                            'estado' => (string) $r['estado'],
                            'nota' => (string) ($r['nota'] ?? ''),
                            'fecha' => (string) $r['fecha'],
                        ];
                    }
                }
                $stmt->close();
            }
        }
    }
} catch (\Throwable $e) {
    // silencioso: no romper la vista
}

// Fallback de historial si está vacío (datos de ejemplo visuales)
if (empty($historial)) {
    $now = date('Y-m-d H:i:s');
    $historial = [
        ['estado' => 'registro', 'nota' => 'Registro inicial en el sistema', 'fecha' => date('Y-m-d 09:00:00', strtotime('-5 days'))],
        ['estado' => 'documentacion', 'nota' => 'Se cargaron datos y documentos base', 'fecha' => date('Y-m-d 12:10:00', strtotime('-3 days'))],
    ];
    if ($estadoActual === 'aprobado') {
        $historial[] = ['estado' => 'resolucion', 'nota' => 'Expediente aprobado', 'fecha' => $now];
    } elseif ($estadoActual === 'rechazado') {
        $historial[] = ['estado' => 'rechazado', 'nota' => 'Requiere correcciones / observaciones', 'fecha' => $now];
    } else {
        $historial[] = ['estado' => 'revision', 'nota' => 'En revisión por el abogado', 'fecha' => date('Y-m-d 10:00:00', strtotime('-1 day'))];
    }
}

// Etapas del timeline (orden fijo)
$etapas = [
    'registro' => ['label' => 'Registro', 'desc' => 'Alta en el sistema.'],
    'documentacion' => ['label' => 'Documentación', 'desc' => 'Carga de documentos y datos.'],
    'revision' => ['label' => 'Revisión', 'desc' => 'Validación por el abogado.'],
    'tramite' => ['label' => 'Trámite', 'desc' => 'Gestión del proceso.'],
    'resolucion' => ['label' => 'Resolución', 'desc' => 'Resultado final.'],
];

// Deducir etapa actual para pintar el timeline
$etapaActual = estado_a_etapa($estadoActual);
$rechazado = ($etapaActual === 'rechazado');

// Índice de la etapa actual
$claves = array_keys($etapas);
$indiceActual = $rechazado ? array_search('revision', $claves, true) : array_search($etapaActual, $claves, true);
if ($indiceActual === false) {
    $indiceActual = 0;
}

// Porcentaje de progreso (sólo visual)
$porc = (int) round(($indiceActual) / (count($etapas) - 1) * 100);
if ($estadoActual === 'aprobado') {
    $porc = 100;
}
?>
<style>
    /* ===== ESTILOS SCOPE ESTATUS ===== */
    .est-wrap {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto
    }

    .est-head {
        background: linear-gradient(135deg, #111 0%, #3A3A3A 38%, #8A1538 100%);
        color: #fff;
        border-radius: 16px;
        padding: 22px 18px;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .18);
        position: relative;
        overflow: hidden
    }

    .est-head::after {
        content: "";
        position: absolute;
        inset: auto -10% -60% -10%;
        height: 200px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .15), transparent 60%)
    }

    .est-title {
        margin: 0;
        font-weight: 900;
        font-size: clamp(1.3rem, 3.6vw, 1.9rem)
    }

    .est-sub {
        color: #f3f4f6;
        opacity: .95;
        margin-top: 6px
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 800;
        font-size: .82rem;
        border: 1px solid rgba(255, 255, 255, .35);
        background: rgba(255, 255, 255, .12);
        margin-top: 10px
    }

    .num-exp {
        font-weight: 900;
        background: #fff;
        color: #111;
        padding: 6px 10px;
        border-radius: 10px
    }

    .est-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px
    }

    .btn-white {
        background: #fff;
        color: #111;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 900;
        text-decoration: none
    }

    .btn-ghost {
        background: rgba(255, 255, 255, .12);
        color: #fff;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 800;
        border: 1px solid rgba(255, 255, 255, .2);
        text-decoration: none
    }

    .btn-ghost:hover {
        background: rgba(255, 255, 255, .18)
    }

    /* Timeline */
    .tl {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        margin-top: 18px;
        padding: 18px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .06)
    }

    .tl-bar {
        position: relative;
        height: 6px;
        background: #e5e7eb;
        border-radius: 999px;
        margin: 24px 6px
    }

    .tl-fill {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 0;
        background: #8A1538;
        border-radius: 999px;
        box-shadow: 0 0 0 2px rgba(138, 21, 56, .08) inset;
        animation: grow .8s ease forwards
    }

    @keyframes grow {
        to {
            width: var(--w, 0%)
        }
    }

    .tl-steps {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
        margin-top: 10px
    }

    .tl-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 8px
    }

    .tl-dot {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        background: #e5e7eb;
        border: 2px solid #e5e7eb;
        transition: .25s
    }

    .tl-step.done .tl-dot {
        background: #8A1538;
        border-color: #8A1538;
        transform: scale(1.05)
    }

    .tl-step.current .tl-dot {
        background: #fff;
        border-color: #8A1538;
        box-shadow: 0 0 0 4px rgba(138, 21, 56, .18)
    }

    .tl-label {
        font-weight: 800;
        color: #111
    }

    .tl-desc {
        font-size: .85rem;
        color: #6b7280
    }

    /* Historial */
    .hist {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        margin-top: 16px;
        padding: 18px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .06)
    }

    .hist h3 {
        margin: 0 0 10px 0;
        font-size: 1.1rem;
        color: #111;
        font-weight: 900
    }

    .hist-list {
        display: flex;
        flex-direction: column;
        gap: 10px
    }

    .hist-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 12px;
        align-items: center;
        background: #fafbfc;
        border: 1px solid #eef1f5;
        border-radius: 12px;
        padding: 12px
    }

    .badge {
        font-size: .78rem;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: #3730a3;
        font-weight: 800
    }

    .nota {
        color: #374151
    }

    .fech {
        color: #6b7280;
        font-size: .9rem
    }

    /* Aviso rechazo */
    .alert-rech {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #831843;
        border-radius: 12px;
        padding: 12px;
        margin-top: 12px;
        font-weight: 700
    }
</style>

<div class="est-wrap">
    <!-- Encabezado -->
    <section class="est-head">
        <h2 class="est-title">
            Estatus actual:
            <?php
            $map = [
                'aprobado' => 'Aprobado',
                'rechazado' => 'Observaciones / Rechazado',
                'en-documentacion' => 'En documentación',
                'en-revision' => 'En revisión',
                'en-tramite' => 'En trámite',
            ];
            $txtEstado = $map[$estadoActual] ?? 'Pendiente';
            echo htmlspecialchars($txtEstado);
            ?>
        </h2>
        <p class="est-sub">
            Hola <?php echo htmlspecialchars($nombre); ?>. Aquí puedes ver el avance de tu expediente
            <?php if ($numExpediente): ?>
                · <span class="num-exp">Expediente <?php echo htmlspecialchars($numExpediente); ?></span>
            <?php endif; ?>
        </p>
        <span class="pill"><?php echo $porc; ?>% completado</span>

        <div class="est-actions">
            <!-- NOTA: usamos axm-nav-link + data-view, href siempre /AXM/ -->
            <a class="btn-white axm-nav-link" href="/AXM/" data-view="notificaciones">
                Ver notificaciones
            </a>

            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="complementar">
                Subir / completar documentos
            </a>

            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="historial">
                Ver historial de citas
            </a>
        </div>
    </section>

    <!-- Timeline -->
    <section class="tl">
        <?php
        $total = count($etapas) - 1;
        $w = $rechazado ? (($indiceActual) / $total * 100) : ($porc);
        $w = max(0, min(100, (int) $w));
        ?>
        <div class="tl-bar">
            <div class="tl-fill" style="--w: <?php echo $w; ?>%"></div>
        </div>

        <div class="tl-steps">
            <?php
            $i = 0;
            foreach ($etapas as $clave => $meta):
                $done = ($i <= $indiceActual) && !$rechazado;
                $current = ($i === $indiceActual) && !$rechazado;
                if ($rechazado && $clave === 'revision') {
                    $current = true;
                }
                ?>
                <div class="tl-step <?php echo $done ? 'done' : ''; ?> <?php echo $current ? 'current' : ''; ?>">
                    <div class="tl-dot"></div>
                    <div class="tl-label"><?php echo htmlspecialchars($meta['label']); ?></div>
                    <div class="tl-desc"><?php echo htmlspecialchars($meta['desc']); ?></div>
                </div>
                <?php
                $i++;
            endforeach;
            ?>
        </div>

        <?php if ($rechazado): ?>
            <div class="alert-rech">
                Tu expediente requiere correcciones. Revisa las observaciones y vuelve a subir la documentación necesaria.
                <a class="axm-nav-link" href="/AXM/" data-view="complementar" style="text-decoration:underline">
                    Ir a completar datos
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Histórico -->
    <section class="hist">
        <h3>Histórico de estatus</h3>
        <div class="hist-list">
            <?php foreach ($historial as $h):
                $et = estado_a_etapa((string) $h['estado']);
                $lab = [
                    'registro' => 'Registro',
                    'documentacion' => 'Documentación',
                    'revision' => 'Revisión',
                    'tramite' => 'Trámite',
                    'resolucion' => 'Resolución',
                    'rechazado' => 'Observaciones',
                ][$et] ?? ucfirst($et);

                $nota = trim((string) ($h['nota'] ?? ''));
                $fechaFmt = $h['fecha'] ? date('d/m/Y H:i', strtotime($h['fecha'])) : '';
                ?>
                <div class="hist-item">
                    <span class="badge"><?php echo htmlspecialchars($lab); ?></span>
                    <div class="nota">
                        <?php echo $nota !== '' ? htmlspecialchars($nota) : 'Sin observaciones'; ?>
                    </div>
                    <div class="fech"><?php echo htmlspecialchars($fechaFmt); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>