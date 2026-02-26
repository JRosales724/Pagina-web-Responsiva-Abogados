<?php
// AXM/usuario/historial.php  (Historial de citas y movimientos)
declare(strict_types=1);

$uid = (int) ($_SESSION['ID'] ?? 0);
$nombre = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? ''));
if ($nombre === '') {
    $nombre = 'Usuario';
}

$citas = []; // historial de citas
$movimientos = []; // historial de cambios de estatus

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }
    if ($uid > 0 && class_exists('Conexion')) {
        $cn = new Conexion();

        // ===== Citas =====
        $tieneCitas = false;
        if ($res = $cn->query("SHOW TABLES LIKE 'citas'")) {
            $tieneCitas = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneCitas) {
            // Intentar obtener campos extra de la tabla
            $cols = [];
            if ($res = $cn->query("SHOW COLUMNS FROM citas")) {
                while ($r = $res->fetch_assoc()) {
                    $cols[] = $r['Field'];
                }
                $res->close();
            }
            $tieneTipo = in_array('tipo', $cols, true);
            $tieneCanal = in_array('canal', $cols, true);
            $tieneNota = in_array('nota', $cols, true);

            $sql = "SELECT id, fecha, hora, estado";
            if ($tieneTipo)
                $sql .= ", tipo";
            if ($tieneCanal)
                $sql .= ", canal";
            if ($tieneNota)
                $sql .= ", nota";
            $sql .= " FROM citas WHERE id_usuario = ? ORDER BY fecha DESC, hora DESC LIMIT 80";

            if ($st = $cn->prepare($sql)) {
                $st->bind_param('i', $uid);
                if ($st->execute()) {
                    $rs = $st->get_result();
                    while ($r = $rs->fetch_assoc()) {
                        $citas[] = [
                            'id' => (int) ($r['id'] ?? 0),
                            'fecha' => (string) ($r['fecha'] ?? ''),
                            'hora' => (string) ($r['hora'] ?? ''),
                            'estado' => (string) ($r['estado'] ?? ''),
                            'tipo' => $tieneTipo ? (string) ($r['tipo'] ?? '') : '',
                            'canal' => $tieneCanal ? (string) ($r['canal'] ?? '') : '',
                            'nota' => $tieneNota ? (string) ($r['nota'] ?? '') : '',
                        ];
                    }
                }
                $st->close();
            }
        }

        // ===== Movimientos de estatus =====
        $tieneHist = false;
        if ($res = $cn->query("SHOW TABLES LIKE 'estatus_historial'")) {
            $tieneHist = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneHist) {
            if ($st = $cn->prepare("SELECT estado, nota, fecha FROM estatus_historial WHERE id_usuario = ? ORDER BY fecha DESC, id DESC LIMIT 60")) {
                $st->bind_param('i', $uid);
                if ($st->execute()) {
                    $rs = $st->get_result();
                    while ($r = $rs->fetch_assoc()) {
                        $movimientos[] = [
                            'estado' => (string) ($r['estado'] ?? ''),
                            'nota' => (string) ($r['nota'] ?? ''),
                            'fecha' => (string) ($r['fecha'] ?? ''),
                        ];
                    }
                }
                $st->close();
            }
        }
    }
} catch (\Throwable $e) {
    // silencioso
}

// Si no hay nada en BD, mostramos un pequeño dummy
if (empty($citas)) {
    $citas = [
        [
            'id' => 0,
            'fecha' => date('Y-m-d', strtotime('-5 days')),
            'hora' => '10:00:00',
            'estado' => 'realizada',
            'tipo' => 'Valoración inicial',
            'canal' => 'WhatsApp',
            'nota' => 'Primera revisión de caso.',
        ],
        [
            'id' => 0,
            'fecha' => date('Y-m-d', strtotime('-1 days')),
            'hora' => '12:30:00',
            'estado' => 'cancelada',
            'tipo' => 'Seguimiento',
            'canal' => 'Videollamada',
            'nota' => 'Reprogramar por falta de documentación.',
        ],
    ];
}

if (empty($movimientos)) {
    $movimientos = [
        [
            'estado' => 'registro',
            'nota' => 'Registro inicial en el sistema.',
            'fecha' => date('Y-m-d 09:00:00', strtotime('-7 days')),
        ],
        [
            'estado' => 'documentacion',
            'nota' => 'Documentación inicial recibida.',
            'fecha' => date('Y-m-d 11:20:00', strtotime('-4 days')),
        ],
        [
            'estado' => 'revision',
            'nota' => 'En revisión por el abogado.',
            'fecha' => date('Y-m-d 15:00:00', strtotime('-2 days')),
        ],
    ];
}

// Helper para traducir estado de cita
function label_estado_cita(string $estado): array
{
    $s = strtolower(trim($estado));
    return match ($s) {
        'agendada', 'reprogramada' => ['Agendada', 'estado-badge agendada'],
        'realizada', 'completada' => ['Realizada', 'estado-badge realizada'],
        'cancelada' => ['Cancelada', 'estado-badge cancelada'],
        default => ['Pendiente', 'estado-badge pendiente'],
    };
}

// Helper para traducir estado de expediente a label
function label_estado_expediente_hist(string $estado): string
{
    $e = strtolower(trim($estado));
    return match ($e) {
        'registro' => 'Registro',
        'documentacion',
        'en-documentacion' => 'Documentación',
        'revision',
        'en-revision' => 'Revisión',
        'tramite',
        'en-tramite' => 'Trámite',
        'resolucion',
        'aprobado' => 'Resolución',
        'rechazado',
        'observaciones' => 'Observaciones',
        default => ucfirst($e ?: 'Pendiente'),
    };
}
?>
<style>
    /* ===== HISTORIAL (SCOPED) ===== */
    .hist-wrap {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .hist-head {
        background: linear-gradient(135deg, #111 0%, #3A3A3A 40%, #8A1538 100%);
        color: #fff;
        border-radius: 18px;
        padding: 22px 18px;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .18);
        position: relative;
        overflow: hidden;
    }

    .hist-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .16), transparent 60%);
        opacity: .7;
    }

    .hist-title {
        margin: 0;
        font-weight: 900;
        font-size: clamp(1.3rem, 3.6vw, 1.9rem);
    }

    .hist-sub {
        margin-top: 6px;
        color: #f9fafb;
        max-width: 640px;
    }

    .hist-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 800;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .4);
    }

    .hist-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .btn-white {
        background: #fff;
        color: #111;
        padding: 9px 14px;
        border-radius: 999px;
        font-weight: 900;
        text-decoration: none;
        font-size: .9rem;
    }

    .btn-ghost {
        background: rgba(255, 255, 255, .08);
        color: #fff;
        padding: 9px 14px;
        border-radius: 999px;
        font-weight: 800;
        border: 1px solid rgba(255, 255, 255, .25);
        text-decoration: none;
        font-size: .9rem;
    }

    .btn-ghost:hover {
        background: rgba(255, 255, 255, .16);
    }

    .hist-grid {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr;
        gap: 18px;
        margin-top: 18px;
    }

    @media(max-width:960px) {
        .hist-grid {
            grid-template-columns: 1fr;
        }
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .06);
    }

    .card h3 {
        margin: 0 0 8px;
        font-size: 1.05rem;
        font-weight: 900;
        color: #111;
    }

    .muted {
        color: #6b7280;
        font-size: .9rem;
    }

    /* Citas */
    .citas-group {
        margin-top: 8px;
        border-radius: 12px;
        border: 1px dashed #e5e7eb;
        padding: 10px 12px;
        background: #f9fafb;
    }

    .citas-group-title {
        font-size: .85rem;
        font-weight: 800;
        color: #111;
        margin-bottom: 6px;
    }

    .cita-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 10px;
        align-items: center;
        padding: 8px 10px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #e5e7eb;
        margin-bottom: 6px;
    }

    .cita-main strong {
        font-size: .9rem;
        color: #111;
    }

    .cita-main .muted {
        font-size: .82rem;
    }

    .estado-badge {
        font-size: .75rem;
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .estado-badge .dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: currentColor;
    }

    .estado-badge.agendada {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .estado-badge.realizada {
        background: #ecfdf3;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .estado-badge.cancelada {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .estado-badge.pendiente {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
    }

    .tag-chip {
        font-size: .75rem;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #111827;
        border: 1px solid #e5e7eb;
    }

    /* Línea de tiempo de movimientos */
    .mov-list {
        margin-top: 10px;
        border-left: 2px solid #e5e7eb;
        padding-left: 12px;
        position: relative;
    }

    .mov-item {
        position: relative;
        padding-left: 10px;
        margin-bottom: 12px;
    }

    .mov-dot {
        position: absolute;
        left: -12px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #8A1538;
        box-shadow: 0 0 0 2px #fee2e2;
    }

    .mov-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 2px;
    }

    .mov-label {
        font-size: .85rem;
        font-weight: 800;
        color: #111;
    }

    .mov-date {
        font-size: .78rem;
        color: #6b7280;
    }

    .mov-nota {
        font-size: .85rem;
        color: #374151;
    }

    .fade {
        opacity: 0;
        transform: translateY(8px);
        animation: hFade .45s ease .02s forwards;
    }

    .fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: hFade .45s ease .16s forwards;
    }

    .fade3 {
        opacity: 0;
        transform: translateY(8px);
        animation: hFade .45s ease .3s forwards;
    }

    @keyframes hFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="hist-wrap">
    <!-- HEADER -->
    <section class="hist-head fade">
        <h2 class="hist-title">Historial de citas y movimientos</h2>
        <p class="hist-sub">
            Aquí puedes revisar tus citas pasadas, reprogramaciones y los cambios importantes
            en el estado de tu expediente.
        </p>
        <span class="hist-pill">
            <span>📅 Últimos movimientos</span>
            <strong><?php echo count($citas) + count($movimientos); ?> registros</strong>
        </span>

        <div class="hist-actions">
            <a class="btn-white axm-nav-link" href="/AXM/" data-view="consultas">
                Ver estatus actual
            </a>
            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="complementar">
                Completar datos / documentos
            </a>
            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="notificaciones">
                Ir a notificaciones
            </a>
        </div>
    </section>

    <section class="hist-grid">
        <!-- Citas -->
        <div class="card fade2">
            <h3>Citas registradas</h3>
            <p class="muted">
                Se muestran tus últimas citas agendadas, realizadas, canceladas o reprogramadas.
            </p>

            <?php if (empty($citas)): ?>
                <p class="muted" style="margin-top:10px">
                    Aún no tienes citas registradas. Puedes agendar una desde
                    <a href="/AXM/" class="axm-nav-link" data-view="consultas" style="color:#8A1538;font-weight:700">
                        Consultas
                    </a>.
                </p>
            <?php else: ?>
                <?php
                // Agrupamos por año-mes: "2025-02"
                $grupos = [];
                foreach ($citas as $c) {
                    $f = $c['fecha'] ?: '';
                    $key = $f !== '' ? date('Y-m', strtotime($f)) : 'sin-fecha';
                    $grupos[$key][] = $c;
                }

                // Ordenar por mes descendente
                krsort($grupos);
                ?>

                <?php foreach ($grupos as $ym => $lista): ?>
                    <?php
                    if ($ym === 'sin-fecha') {
                        $titulo = 'Sin fecha definida';
                    } else {
                        $dt = DateTime::createFromFormat('Y-m-d', $ym . '-01') ?: new DateTime();
                        $titulo = ucfirst(strftime('%B %Y', $dt->getTimestamp()));
                        // por si strftime no maneja local, usamos fallback:
                        if (!$titulo || $titulo === 'January 1970') {
                            $titulo = $dt->format('m / Y');
                        }
                    }
                    ?>
                    <div class="citas-group">
                        <div class="citas-group-title"><?php echo htmlspecialchars($titulo); ?></div>
                        <?php foreach ($lista as $c): ?>
                            <?php
                            [$labelEstado, $claseEstado] = label_estado_cita($c['estado']);
                            $fechaTxt = $c['fecha'] ? date('d/m/Y', strtotime($c['fecha'])) : 'Sin fecha';
                            $horaTxt = $c['hora'] ? date('H:i', strtotime($c['hora'])) : '';
                            $tipoTxt = trim((string) ($c['tipo'] ?? '')) ?: 'Cita';
                            $canalTxt = trim((string) ($c['canal'] ?? ''));
                            $notaTxt = trim((string) ($c['nota'] ?? ''));
                            ?>
                            <div class="cita-item">
                                <div>
                                    <span class="<?php echo $claseEstado; ?>">
                                        <span class="dot"></span>
                                        <?php echo htmlspecialchars($labelEstado); ?>
                                    </span>
                                </div>
                                <div class="cita-main">
                                    <strong><?php echo htmlspecialchars($fechaTxt . ($horaTxt ? ' · ' . $horaTxt : '')); ?></strong>
                                    <div class="muted">
                                        <?php echo htmlspecialchars($tipoTxt); ?>
                                        <?php if ($canalTxt !== ''): ?>
                                            · <span class="tag-chip"><?php echo htmlspecialchars($canalTxt); ?></span>
                                        <?php endif; ?>
                                        <?php if ($notaTxt !== ''): ?>
                                            <br><?php echo htmlspecialchars($notaTxt); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <!-- Si en un futuro quieres botón "Ver detalle" o "Reagendar" -->
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Movimientos -->
        <div class="card fade3">
            <h3>Movimientos del expediente</h3>
            <p class="muted">
                Cambios de estatus registrados por el despacho sobre tu expediente.
            </p>

            <div class="mov-list">
                <?php foreach ($movimientos as $m): ?>
                    <?php
                    $etiqueta = label_estado_expediente_hist($m['estado']);
                    $fechaStr = $m['fecha'] ? date('d/m/Y H:i', strtotime($m['fecha'])) : '';
                    $nota = trim((string) ($m['nota'] ?? ''));
                    ?>
                    <div class="mov-item">
                        <span class="mov-dot"></span>
                        <div class="mov-head">
                            <span class="mov-label"><?php echo htmlspecialchars($etiqueta); ?></span>
                            <?php if ($fechaStr !== ''): ?>
                                <span class="mov-date"><?php echo htmlspecialchars($fechaStr); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mov-nota">
                            <?php echo $nota !== '' ? htmlspecialchars($nota) : 'Sin observaciones'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="muted" style="margin-top:10px;font-size:.82rem">
                Si notas algún movimiento que no reconoces, comunícate con el despacho para aclararlo.
            </p>
        </div>
    </section>
</div>