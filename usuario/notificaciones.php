<?php
// AXM/usuario/notificaciones.php
declare(strict_types=1);

$uid = (int) ($_SESSION['ID'] ?? 0);
$nombre = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? ''));
if ($nombre === '') {
    $nombre = 'Usuario';
}

$notificaciones = [];

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }
    if ($uid > 0 && class_exists('Conexion')) {
        $cn = new Conexion();

        // Verificar si existe tabla notificaciones
        $tieneTabla = false;
        if ($res = $cn->query("SHOW TABLES LIKE 'notificaciones'")) {
            $tieneTabla = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneTabla) {
            // Intentar detectar columnas opcionales
            $cols = [];
            if ($res = $cn->query("SHOW COLUMNS FROM notificaciones")) {
                while ($r = $res->fetch_assoc()) {
                    $cols[] = $r['Field'];
                }
                $res->close();
            }

            $tieneTipo = in_array('tipo', $cols, true);
            $tieneLeido = in_array('leido', $cols, true);
            $tieneImportante = in_array('importante', $cols, true);
            $tieneFechaLeido = in_array('fecha_leido', $cols, true);

            $sql = "SELECT id, titulo, mensaje, fecha_creada";
            if ($tieneTipo)
                $sql .= ", tipo";
            if ($tieneLeido)
                $sql .= ", leido";
            if ($tieneImportante)
                $sql .= ", importante";
            if ($tieneFechaLeido)
                $sql .= ", fecha_leido";

            $sql .= " FROM notificaciones WHERE id_usuario = ? ORDER BY fecha_creada DESC, id DESC LIMIT 80";

            if ($st = $cn->prepare($sql)) {
                $st->bind_param('i', $uid);
                if ($st->execute()) {
                    $rs = $st->get_result();
                    while ($r = $rs->fetch_assoc()) {
                        $notificaciones[] = [
                            'id' => (int) ($r['id'] ?? 0),
                            'titulo' => (string) ($r['titulo'] ?? ''),
                            'mensaje' => (string) ($r['mensaje'] ?? ''),
                            'fecha' => (string) ($r['fecha_creada'] ?? ''),
                            'tipo' => $tieneTipo ? (string) ($r['tipo'] ?? '') : '',
                            'leido' => $tieneLeido ? (bool) (int) ($r['leido'] ?? 0) : false,
                            'importante' => $tieneImportante ? (bool) (int) ($r['importante'] ?? 0) : false,
                            'fecha_leido' => $tieneFechaLeido ? (string) ($r['fecha_leido'] ?? '') : '',
                        ];
                    }
                }
                $st->close();
            }
        }
    }
} catch (\Throwable $e) {
    // silencioso; en desarrollo puedes loguearlo
}

// Si no hay nada, usamos dummy para que el diseño no se vea vacío
if (empty($notificaciones)) {
    $notificaciones = [
        [
            'id' => 0,
            'titulo' => 'Recibimos tus documentos',
            'mensaje' => 'Hemos recibido tu INE y talón de pago. En breve un abogado revisará la información.',
            'fecha' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'tipo' => 'documento',
            'leido' => false,
            'importante' => true,
            'fecha_leido' => '',
        ],
        [
            'id' => 0,
            'titulo' => 'Actualización de estatus',
            'mensaje' => 'Tu expediente se encuentra ahora en etapa de revisión.',
            'fecha' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'tipo' => 'estatus',
            'leido' => true,
            'importante' => false,
            'fecha_leido' => date('Y-m-d H:i:s', strtotime('-20 hours')),
        ],
        [
            'id' => 0,
            'titulo' => 'Cita reprogramada',
            'mensaje' => 'Tu cita se reprogramó para mañana a las 12:30 hrs.',
            'fecha' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'tipo' => 'cita',
            'leido' => true,
            'importante' => false,
            'fecha_leido' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
    ];
}

// Helper: icono + clase por tipo
function notif_tipo_meta(string $tipo): array
{
    $t = strtolower(trim($tipo));
    return match ($t) {
        'cita' => ['📅', 'nt-cita'],
        'documento' => ['📎', 'nt-doc'],
        'estatus' => ['⚖️', 'nt-estatus'],
        'sistema' => ['⚙️', 'nt-sistema'],
        default => ['🔔', 'nt-general'],
    };
}

// Helper: formatear fecha simple
function fmt_fecha_ntf(?string $f): string
{
    if (!$f)
        return '';
    $ts = strtotime($f);
    if (!$ts)
        return '';
    return date('d/m/Y H:i', $ts);
}

// Contar no leídas
$noLeidas = array_reduce($notificaciones, fn($c, $n) => $c + (!$n['leido'] ? 1 : 0), 0);
?>
<style>
    /* ===== NOTIFICACIONES (SCOPED) ===== */
    .nt-wrap {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .nt-head {
        background: linear-gradient(135deg, #111 0%, #3A3A3A 40%, #8A1538 100%);
        color: #fff;
        border-radius: 18px;
        padding: 22px 18px;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .18);
        position: relative;
        overflow: hidden;
    }

    .nt-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .16), transparent 60%);
        opacity: .7;
    }

    .nt-title {
        margin: 0;
        font-weight: 900;
        font-size: clamp(1.3rem, 3.6vw, 1.9rem);
    }

    .nt-sub {
        margin-top: 6px;
        color: #f9fafb;
        max-width: 640px;
    }

    .nt-pill {
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

    .nt-actions {
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

    .nt-grid {
        display: grid;
        grid-template-columns: 2fr 1.2fr;
        gap: 18px;
        margin-top: 18px;
    }

    @media(max-width:960px) {
        .nt-grid {
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

    /* Lista de notificaciones */
    .nt-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 6px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .nt-filters {
        display: inline-flex;
        gap: 6px;
        background: #f9fafb;
        padding: 3px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
    }

    .nt-chip {
        border: none;
        background: transparent;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        color: #4b5563;
        cursor: pointer;
    }

    .nt-chip.active {
        background: #111827;
        color: #fff;
    }

    .nt-mark-all {
        font-size: .8rem;
        color: #6b7280;
        cursor: pointer;
        text-decoration: underline;
    }

    .nt-list {
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 420px;
        overflow-y: auto;
    }

    .nt-item {
        position: relative;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 10px;
        align-items: flex-start;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .nt-item.unread {
        border-color: #c7d2fe;
        background: #eef2ff;
    }

    .nt-icon {
        font-size: 1.3rem;
    }

    .nt-title-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 2px;
    }

    .nt-title-line strong {
        font-size: .9rem;
        color: #111827;
    }

    .nt-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #4f46e5;
    }

    .nt-label {
        font-size: .7rem;
        padding: 3px 8px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #111827;
        border: 1px solid #e5e7eb;
    }

    .nt-label.imp {
        background: #fef3c7;
        border-color: #fde68a;
        color: #92400e;
    }

    .nt-text {
        font-size: .85rem;
        color: #374151;
    }

    .nt-meta {
        text-align: right;
        font-size: .78rem;
        color: #6b7280;
        white-space: nowrap;
    }

    .nt-empty {
        margin-top: 10px;
        font-size: .9rem;
        color: #6b7280;
    }

    /* Panel lateral (resumen) */
    .nt-summary {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 8px;
    }

    .nt-summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 10px;
        border-radius: 10px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        font-size: .85rem;
    }

    .nt-summary-label {
        font-weight: 700;
        color: #111827;
    }

    .nt-summary-value {
        font-weight: 800;
        color: #8A1538;
    }

    .nt-legend {
        margin-top: 10px;
        border-radius: 10px;
        border: 1px dashed #e5e7eb;
        padding: 8px 10px;
        font-size: .8rem;
        color: #4b5563;
        background: #f9fafb;
    }

    .nt-legend-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .nt-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
    }

    .fade {
        opacity: 0;
        transform: translateY(8px);
        animation: nFade .45s ease .02s forwards;
    }

    .fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: nFade .45s ease .16s forwards;
    }

    .fade3 {
        opacity: 0;
        transform: translateY(8px);
        animation: nFade .45s ease .3s forwards;
    }

    @keyframes nFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="nt-wrap">
    <!-- HEADER -->
    <section class="nt-head fade">
        <h2 class="nt-title">Notificaciones</h2>
        <p class="nt-sub">
            Aquí verás los avisos importantes sobre tu expediente, citas y documentos.
            Te recomendamos revisar este apartado con frecuencia.
        </p>
        <span class="nt-pill">
            <span>🔔 Notificaciones sin leer</span>
            <strong><?php echo (int) $noLeidas; ?></strong>
        </span>

        <div class="nt-actions">
            <a class="btn-white axm-nav-link" href="/AXM/" data-view="consultas">
                Ver estatus del trámite
            </a>
            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="historial">
                Ir al historial de citas
            </a>
            <a class="btn-ghost axm-nav-link" href="/AXM/" data-view="complementar">
                Completar datos / documentos
            </a>
        </div>
    </section>

    <section class="nt-grid">
        <!-- Lista principal -->
        <div class="card fade2">
            <h3>Bandeja de notificaciones</h3>
            <p class="muted">
                Se muestran las notificaciones más recientes primero. Puedes filtrar por tipo o ver sólo las no leídas.
            </p>

            <div class="nt-toolbar">
                <div class="nt-filters" id="ntFilters">
                    <button type="button" class="nt-chip active" data-filter="all">Todas</button>
                    <button type="button" class="nt-chip" data-filter="unread">No leídas</button>
                    <button type="button" class="nt-chip" data-filter="important">Importantes</button>
                </div>
                <span class="nt-mark-all" id="ntMarkAll">Marcar todas como leídas (visual)</span>
            </div>

            <div class="nt-list" id="ntList">
                <?php if (empty($notificaciones)): ?>
                    <p class="nt-empty">
                        No tienes notificaciones por el momento.
                    </p>
                <?php else: ?>
                    <?php foreach ($notificaciones as $n): ?>
                        <?php
                        [$icono, $tipoClase] = notif_tipo_meta($n['tipo'] ?? '');
                        $clases = 'nt-item ' . $tipoClase;
                        if (!$n['leido']) {
                            $clases .= ' unread';
                        }
                        $fechaTxt = fmt_fecha_ntf($n['fecha'] ?? null);
                        $titulo = trim($n['titulo'] ?? '') ?: 'Aviso del sistema';
                        $msg = trim($n['mensaje'] ?? '');
                        $import = !empty($n['importante']);
                        ?>
                        <article class="<?php echo $clases; ?>"
                            data-type="<?php echo htmlspecialchars($n['tipo'] ?: 'general'); ?>"
                            data-read="<?php echo $n['leido'] ? '1' : '0'; ?>"
                            data-important="<?php echo $import ? '1' : '0'; ?>">
                            <div class="nt-icon"><?php echo $icono; ?></div>
                            <div>
                                <div class="nt-title-line">
                                    <strong><?php echo htmlspecialchars($titulo); ?></strong>
                                    <?php if (!$n['leido']): ?>
                                        <span class="nt-dot"></span>
                                    <?php endif; ?>
                                    <?php if ($import): ?>
                                        <span class="nt-label imp">Importante</span>
                                    <?php endif; ?>
                                    <?php if ($n['tipo']): ?>
                                        <span class="nt-label">
                                            <?php echo htmlspecialchars(ucfirst($n['tipo'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="nt-text">
                                    <?php echo $msg !== '' ? htmlspecialchars($msg) : 'Sin descripción.'; ?>
                                </div>
                            </div>
                            <div class="nt-meta">
                                <?php if ($fechaTxt !== ''): ?>
                                    <div><?php echo htmlspecialchars($fechaTxt); ?></div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen lateral -->
        <div class="card fade3">
            <h3>Resumen rápido</h3>
            <p class="muted">
                Un vistazo general al estado de tus notificaciones.
            </p>

            <?php
            $total = count($notificaciones);
            $importantes = array_reduce($notificaciones, fn($c, $n) => $c + (!empty($n['importante']) ? 1 : 0), 0);
            $leidas = $total - $noLeidas;
            ?>
            <div class="nt-summary">
                <div class="nt-summary-item">
                    <span class="nt-summary-label">Total recibidas</span>
                    <span class="nt-summary-value"><?php echo (int) $total; ?></span>
                </div>
                <div class="nt-summary-item">
                    <span class="nt-summary-label">No leídas</span>
                    <span class="nt-summary-value"><?php echo (int) $noLeidas; ?></span>
                </div>
                <div class="nt-summary-item">
                    <span class="nt-summary-label">Leídas</span>
                    <span class="nt-summary-value"><?php echo (int) $leidas; ?></span>
                </div>
                <div class="nt-summary-item">
                    <span class="nt-summary-label">Importantes</span>
                    <span class="nt-summary-value"><?php echo (int) $importantes; ?></span>
                </div>
            </div>

            <div class="nt-legend">
                <div class="nt-legend-row">
                    <span class="nt-legend-dot" style="background:#4f46e5;"></span>
                    <span>No leídas: resaltadas en morado dentro de la lista.</span>
                </div>
                <div class="nt-legend-row">
                    <span class="nt-legend-dot" style="background:#facc15;"></span>
                    <span>Importantes: etiqueta amarilla “Importante”.</span>
                </div>
                <div class="nt-legend-row">
                    <span class="nt-legend-dot" style="background:#8A1538;"></span>
                    <span>Tipos: cita, documento, estatus, sistema, general.</span>
                </div>
                <p style="margin-top:6px;">
                    Estos indicadores son visuales. Si deseas que las notificaciones se marquen realmente como
                    leídas en la base de datos, podemos conectar este panel con un endpoint más adelante.
                </p>
            </div>
        </div>
    </section>
</div>

<script>
    // Filtros visuales y "marcar como leído" (solo en frontend)
    (function () {
        const list = document.getElementById('ntList');
        const filters = document.getElementById('ntFilters');
        const markAll = document.getElementById('ntMarkAll');

        if (!list || !filters) return;

        const items = Array.from(list.querySelectorAll('.nt-item'));

        function applyFilter(filter) {
            items.forEach(it => {
                const read = it.getAttribute('data-read') === '1';
                const important = it.getAttribute('data-important') === '1';

                let show = true;
                if (filter === 'unread') {
                    show = !read;
                } else if (filter === 'important') {
                    show = important;
                }

                it.style.display = show ? '' : 'none';
            });
        }

        filters.addEventListener('click', (e) => {
            const btn = e.target.closest('.nt-chip');
            if (!btn) return;
            filters.querySelectorAll('.nt-chip').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.getAttribute('data-filter') || 'all';
            applyFilter(filter);
        });

        // Marcar todas como leídas (solo UI)
        if (markAll) {
            markAll.addEventListener('click', () => {
                items.forEach(it => {
                    it.classList.remove('unread');
                    it.setAttribute('data-read', '1');
                    const dot = it.querySelector('.nt-dot');
                    if (dot) dot.remove();
                });
            });
        }

        // Click en notificación individual => marcar como leída (UI)
        list.addEventListener('click', (e) => {
            const item = e.target.closest('.nt-item');
            if (!item) return;
            fetch('/AXM/php/notificaciones_leer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: "id=" + encodeURIComponent(item.dataset.id)
            });
            item.setAttribute('data-read', '1');
            const dot = item.querySelector('.nt-dot');
            if (dot) dot.remove();
        });
    })();
</script>