<?php
// AXM/admin/casos.php
declare(strict_types=1);

$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));

date_default_timezone_set('America/Mexico_City');

$casos = []; // cada item: ['id','num','usuario','tipo','etapa','estado','juzgado','creado','ultima_act']
$stats = [
    'total' => 0,
    'analisis' => 0,
    'tramite' => 0,
    'resueltos' => 0,
    'observados' => 0,
];

// Helper para etapa normalizada (para filtros/front)
function axm_caso_etapa(string $raw): string
{
    $raw = strtolower(trim($raw));
    return match (true) {
        str_contains($raw, 'anali') => 'analisis',
        str_contains($raw, 'demanda') => 'tramite',
        str_contains($raw, 'tramite') => 'tramite',
        str_contains($raw, 'juicio') => 'tramite',
        str_contains($raw, 'resol') => 'resuelto',
        str_contains($raw, 'ejecu') => 'resuelto',
        default => 'analisis',
    };
}

function axm_fmt_fecha_caso(?string $f): string
{
    if (!$f)
        return '—';
    $ts = strtotime($f);
    if (!$ts)
        return '—';
    return date('d/m/Y H:i', $ts);
}

// ==== Cargar desde BD si se puede ====
try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }

    if (class_exists('Conexion')) {
        $db = new Conexion();

        // ¿Existe tabla `casos`?
        $tieneCasos = false;
        if ($res = $db->query("SHOW TABLES LIKE 'casos'")) {
            $tieneCasos = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneCasos) {
            // Tomar * para adaptarse a diferentes nombres de columnas
            $sql = "SELECT * FROM casos ORDER BY id DESC LIMIT 200";
            if ($res = $db->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                    $id = (int) ($row['id'] ?? 0);
                    $num = trim((string) ($row['Num_expediente'] ?? $row['num_expediente'] ?? $row['numero'] ?? ''));
                    $tipo = trim((string) ($row['tipo_caso'] ?? $row['tipo'] ?? 'Pensión'));
                    $juz = trim((string) ($row['juzgado'] ?? $row['organo'] ?? ''));

                    // Usuario (nombre o id_usuario)
                    $usuario = '';
                    if (!empty($row['nombre_usuario'])) {
                        $usuario = (string) $row['nombre_usuario'];
                    } elseif (!empty($row['id_usuario'])) {
                        $usuario = 'Usuario #' . (int) $row['id_usuario'];
                    }

                    // Etapa / estado procesal
                    $etapaRaw = (string) ($row['etapa'] ?? $row['fase'] ?? $row['estado_procesal'] ?? 'En análisis');
                    $etapa = axm_caso_etapa($etapaRaw);

                    // Estado (para etiqueta): en-proceso|resuelto|observado etc.
                    $estadoRaw = strtolower((string) ($row['estado'] ?? $row['estatus'] ?? 'en-proceso'));
                    $estado = match (true) {
                        str_contains($estadoRaw, 'resuel') => 'resuelto',
                        str_contains($estadoRaw, 'obser') => 'observado',
                        str_contains($estadoRaw, 'rech') => 'observado',
                        default => 'en-proceso',
                    };

                    $creado = (string) ($row['fecha_registro'] ?? $row['created_at'] ?? '');
                    $ultimaAct = (string) ($row['ultima_actualizacion'] ?? $row['updated_at'] ?? '');

                    $casos[] = [
                        'id' => $id,
                        'num' => $num !== '' ? $num : ('CASO-' . $id),
                        'usuario' => $usuario !== '' ? $usuario : ('Usuario no ligado'),
                        'tipo' => $tipo,
                        'etapa' => $etapa,
                        'estado' => $estado,
                        'juzgado' => $juz,
                        'creado' => $creado,
                        'ultima_act' => $ultimaAct,
                    ];
                }
                $res->close();
            }
        }
    }
} catch (\Throwable $e) {
    // error_log($e->getMessage());
}

// Mock demo si no hay casos
if (empty($casos)) {
    $casos = [
        [
            'id' => 101,
            'num' => 'EXP-2025-001',
            'usuario' => 'Usuario Demo A',
            'tipo' => 'Revisión de pensión ISSSTE',
            'etapa' => 'analisis',
            'estado' => 'en-proceso',
            'juzgado' => 'Sin asignar',
            'creado' => date('Y-m-d 09:30:00', strtotime('-15 days')),
            'ultima_act' => date('Y-m-d 12:15:00', strtotime('-10 days')),
        ],
        [
            'id' => 102,
            'num' => 'EXP-2025-002',
            'usuario' => 'Usuario Demo B',
            'tipo' => 'Demanda por negativa',
            'etapa' => 'tramite',
            'estado' => 'en-proceso',
            'juzgado' => 'JUZGADO 5° ADM',
            'creado' => date('Y-m-d 11:05:00', strtotime('-25 days')),
            'ultima_act' => date('Y-m-d 16:40:00', strtotime('-3 days')),
        ],
        [
            'id' => 103,
            'num' => 'EXP-2025-003',
            'usuario' => 'Usuario Demo C',
            'tipo' => 'Reajuste de pensión',
            'etapa' => 'resuelto',
            'estado' => 'resuelto',
            'juzgado' => 'JUZGADO 2° ADM',
            'creado' => date('Y-m-d 08:00:00', strtotime('-60 days')),
            'ultima_act' => date('Y-m-d 10:00:00', strtotime('-1 days')),
        ],
        [
            'id' => 104,
            'num' => 'EXP-2025-004',
            'usuario' => 'Usuario Demo D',
            'tipo' => 'Negativa con observaciones',
            'etapa' => 'analisis',
            'estado' => 'observado',
            'juzgado' => 'Sin asignar',
            'creado' => date('Y-m-d 13:20:00', strtotime('-8 days')),
            'ultima_act' => date('Y-m-d 18:15:00', strtotime('-2 days')),
        ],
    ];
}

// Calcular stats
foreach ($casos as $c) {
    $stats['total']++;
    if ($c['etapa'] === 'analisis') {
        $stats['analisis']++;
    } elseif ($c['etapa'] === 'tramite') {
        $stats['tramite']++;
    } elseif ($c['etapa'] === 'resuelto') {
        $stats['resueltos']++;
    }

    if ($c['estado'] === 'observado') {
        $stats['observados']++;
    }
}
?>
<style>
    /* ===== ADMIN CASOS (SCOPED) ===== */
    .acs-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .acs-head {
        border-radius: 18px;
        padding: 18px 18px 14px;
        background: linear-gradient(115deg, #111827 0%, #059669 42%, #8A1538 100%);
        color: #F9FAFB;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .25);
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .acs-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .2), transparent 60%);
        opacity: .7;
    }

    .acs-head-main {
        max-width: 640px;
        position: relative;
        z-index: 1;
    }

    .acs-title {
        margin: 0;
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        font-weight: 900;
    }

    .acs-sub {
        margin: 6px 0 0;
        font-size: .9rem;
        color: #E5E7EB;
    }

    .acs-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .acs-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(17, 24, 39, .5);
        border: 1px solid rgba(249, 250, 251, .35);
        font-size: .78rem;
        font-weight: 800;
    }

    .acs-chip strong {
        font-weight: 900;
    }

    .acs-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 220px;
        position: relative;
        z-index: 1;
    }

    .acs-btn-main,
    .acs-btn-ghost {
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .acs-btn-main {
        background: #F9FAFB;
        color: #111827;
    }

    .acs-btn-ghost {
        background: rgba(17, 24, 39, .3);
        color: #F9FAFB;
        border: 1px solid rgba(249, 250, 251, .3);
    }

    .acs-btn-ghost:hover {
        background: rgba(17, 24, 39, .5);
    }

    .acs-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 2.1fr 1fr;
        gap: 16px;
    }

    @media(max-width:980px) {
        .acs-grid {
            grid-template-columns: 1fr;
        }
    }

    .acs-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .06);
    }

    .acs-card-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .acs-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .acs-card-sub {
        margin: 0;
        font-size: .85rem;
        color: #6B7280;
    }

    .acs-kpis {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }

    .acs-kpi {
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        padding: 8px 10px;
        font-size: .8rem;
        min-width: 120px;
    }

    .acs-k-label {
        color: #6B7280;
        margin-bottom: 2px;
    }

    .acs-k-value {
        font-size: 1.05rem;
        font-weight: 900;
        color: #111827;
    }

    .acs-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 10px 0 6px;
        font-size: .8rem;
        justify-content: space-between;
    }

    .acs-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .acs-pill {
        border-radius: 999px;
        padding: 4px 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        cursor: pointer;
    }

    .acs-pill.active {
        background: #DCFCE7;
        border-color: #6EE7B7;
        color: #166534;
        font-weight: 700;
    }

    .acs-search {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .acs-search input {
        border-radius: 999px;
        border: 1px solid #E5E7EB;
        padding: 5px 8px;
        font-size: .8rem;
    }

    .acs-search input:focus {
        outline: none;
        border-color: #8A1538;
        box-shadow: 0 0 0 1px #8A153833;
    }

    .acs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .86rem;
        margin-top: 4px;
    }

    .acs-table thead {
        background: #F3F4F6;
    }

    .acs-table th,
    .acs-table td {
        padding: 7px 6px;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
        vertical-align: top;
    }

    .acs-table th {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #6B7280;
        font-weight: 700;
    }

    .acs-table tbody tr:nth-child(even) {
        background: #F9FAFB;
    }

    .acs-col-num {
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
    }

    .acs-col-usuario {
        color: #111827;
    }

    .acs-col-tipo {
        font-size: .82rem;
        color: #374151;
    }

    .acs-col-juzgado {
        font-size: .8rem;
        color: #4B5563;
        white-space: nowrap;
    }

    .acs-col-fecha {
        font-size: .8rem;
        color: #6B7280;
        white-space: nowrap;
    }

    .acs-badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }

    .acs-badge-proceso {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }

    .acs-badge-resuelto {
        background: #ECFDF3;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .acs-badge-observado {
        background: #FEF2F2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }

    .acs-tag-etapa {
        display: inline-block;
        margin-top: 2px;
        font-size: .75rem;
        padding: 2px 6px;
        border-radius: 999px;
        background: #F3F4F6;
        color: #374151;
    }

    .acs-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        font-size: .78rem;
    }

    .acs-link-btn {
        border-radius: 999px;
        padding: 3px 8px;
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        cursor: pointer;
        text-decoration: none;
        color: #111827;
    }

    .acs-link-btn:hover {
        background: #F3F4F6;
    }

    .acs-side-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 6px;
        font-size: .85rem;
    }

    .acs-side-item {
        padding: 9px 10px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        color: #374151;
    }

    .acs-side-item strong {
        color: #111827;
    }

    .acs-side-item span {
        font-size: .8rem;
        color: #6B7280;
    }

    .acs-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: acsFade .4s ease .05s forwards;
    }

    .acs-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: acsFade .4s ease .16s forwards;
    }

    @keyframes acsFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="acs-wrap">
    <!-- ENCABEZADO -->
    <section class="acs-head acs-fade">
        <div class="acs-head-main">
            <h2 class="acs-title">Casos y expedientes</h2>
            <p class="acs-sub">
                Hola <?php echo htmlspecialchars($adminNombre); ?>. Aquí ves una panorámica de los casos
                vinculados a usuarios: etapa procesal, estado y juzgado (si aplica).
            </p>
            <div class="acs-chips">
                <span class="acs-chip">Total casos: <strong><?php echo (int) $stats['total']; ?></strong></span>
                <span class="acs-chip">En análisis: <strong><?php echo (int) $stats['analisis']; ?></strong></span>
                <span class="acs-chip">En trámite/demanda:
                    <strong><?php echo (int) $stats['tramite']; ?></strong></span>
                <span class="acs-chip">Resueltos: <strong><?php echo (int) $stats['resueltos']; ?></strong></span>
                <span class="acs-chip">Con observaciones:
                    <strong><?php echo (int) $stats['observados']; ?></strong></span>
            </div>
        </div>
        <div class="acs-actions">
            <button type="button" class="acs-btn-main axm-nav-link" data-view="usuarios">
                Ir a usuarios
            </button>
            <button type="button" class="acs-btn-ghost axm-nav-link" data-view="consultas">
                Ver consultas / estatus
            </button>
            <button type="button" class="acs-btn-ghost axm-nav-link" data-view="notificaciones">
                Centro de notificaciones
            </button>
        </div>
    </section>

    <!-- GRID -->
    <section class="acs-grid">
        <!-- LISTA PRINCIPAL -->
        <article class="acs-card acs-fade2">
            <div class="acs-card-header">
                <div>
                    <h3 class="acs-card-title">Listado de casos</h3>
                    <p class="acs-card-sub">
                        Filtra por etapa o busca por expediente / nombre de usuario / juzgado.
                    </p>
                </div>
                <div class="acs-kpis">
                    <div class="acs-kpi">
                        <div class="acs-k-label">En análisis</div>
                        <div class="acs-k-value"><?php echo (int) $stats['analisis']; ?></div>
                    </div>
                    <div class="acs-kpi">
                        <div class="acs-k-label">En trámite</div>
                        <div class="acs-k-value"><?php echo (int) $stats['tramite']; ?></div>
                    </div>
                    <div class="acs-kpi">
                        <div class="acs-k-label">Resueltos</div>
                        <div class="acs-k-value"><?php echo (int) $stats['resueltos']; ?></div>
                    </div>
                </div>
            </div>

            <div class="acs-filter-row">
                <div class="acs-pill-group">
                    <button type="button" class="acs-pill active" data-etapa="todas">Todas las etapas</button>
                    <button type="button" class="acs-pill" data-etapa="analisis">En análisis</button>
                    <button type="button" class="acs-pill" data-etapa="tramite">En trámite/demanda</button>
                    <button type="button" class="acs-pill" data-etapa="resuelto">Resueltos</button>
                </div>
                <div class="acs-search">
                    <input type="text" id="acs-search" placeholder="Buscar por número, usuario o juzgado">
                </div>
            </div>

            <table class="acs-table" id="acs-table">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Usuario</th>
                        <th>Tipo / Juzgado</th>
                        <th>Estado / Etapa</th>
                        <th>Fechas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($casos as $c): ?>
                        <?php
                        $etapa = $c['etapa'];
                        $estado = $c['estado'];
                        ?>
                        <tr data-etapa="<?php echo htmlspecialchars($etapa); ?>">
                            <td class="acs-col-num">
                                <?php echo htmlspecialchars($c['num']); ?>
                            </td>
                            <td class="acs-col-usuario">
                                <?php echo htmlspecialchars($c['usuario']); ?>
                            </td>
                            <td>
                                <div class="acs-col-tipo">
                                    <?php echo htmlspecialchars($c['tipo']); ?>
                                </div>
                                <div class="acs-col-juzgado">
                                    <?php echo $c['juzgado'] !== '' ? htmlspecialchars($c['juzgado']) : 'Juzgado sin asignar'; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'acs-badge-proceso';
                                $labelEstado = 'En proceso';
                                if ($estado === 'resuelto') {
                                    $badgeClass = 'acs-badge-resuelto';
                                    $labelEstado = 'Resuelto';
                                } elseif ($estado === 'observado') {
                                    $badgeClass = 'acs-badge-observado';
                                    $labelEstado = 'Observaciones / Revisión';
                                }
                                $labelEtapa = match ($etapa) {
                                    'analisis' => 'En análisis',
                                    'tramite' => 'En trámite / demanda',
                                    'resuelto' => 'Resolución / ejecución',
                                    default => ucfirst($etapa),
                                };
                                ?>
                                <span class="acs-badge-estado <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($labelEstado); ?>
                                </span>
                                <div class="acs-tag-etapa">
                                    <?php echo htmlspecialchars($labelEtapa); ?>
                                </div>
                            </td>
                            <td class="acs-col-fecha">
                                <div><?php echo htmlspecialchars(axm_fmt_fecha_caso($c['creado'])); ?></div>
                                <div style="font-size:.75rem;">Act:
                                    <?php echo htmlspecialchars(axm_fmt_fecha_caso($c['ultima_act'])); ?></div>
                            </td>
                            <td>
                                <div class="acs-actions-row">
                                    <a class="acs-link-btn axm-nav-link" data-view="consultas" href="/AXM/">
                                        Ver estatus
                                    </a>
                                    <a class="acs-link-btn axm-nav-link" data-view="usuarios" href="/AXM/">
                                        Ir al usuario
                                    </a>
                                    <!-- Más adelante puedes añadir: editar caso, subir documento, ver bitácora, etc. -->
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <!-- PANEL LATERAL -->
        <aside class="acs-card acs-fade2">
            <div class="acs-card-header">
                <div>
                    <h3 class="acs-card-title">Resumen estratégico</h3>
                    <p class="acs-card-sub">
                        Un vistazo rápido para organizar la carga de trabajo del despacho.
                    </p>
                </div>
            </div>

            <div class="acs-side-list">
                <div class="acs-side-item">
                    <strong>Casos en análisis</strong><br>
                    <span>
                        <?php echo (int) $stats['analisis']; ?> caso(s) todavía en fase inicial.
                        Útiles para priorizar qué expedientes requieren información adicional
                        o validación de documentos.
                    </span>
                </div>
                <div class="acs-side-item">
                    <strong>Casos en trámite / demanda</strong><br>
                    <span>
                        <?php echo (int) $stats['tramite']; ?> caso(s) ya radicados o en juzgado.
                        Revisa fechas de audiencias, plazos y oficios pendientes.
                    </span>
                </div>
                <div class="acs-side-item">
                    <strong>Casos resueltos</strong><br>
                    <span>
                        <?php echo (int) $stats['resueltos']; ?> caso(s) con resolución.
                        Puedes usar esta sección como base para estadísticas internas
                        y comunicación con los clientes.
                    </span>
                </div>
                <div class="acs-side-item">
                    <strong>Casos con observaciones</strong><br>
                    <span>
                        <?php echo (int) $stats['observados']; ?> caso(s) marcados con observaciones
                        o incidencias. Coordina con los usuarios para completar información
                        o aclarar puntos clave.
                    </span>
                </div>
            </div>
        </aside>
    </section>
</div>

<script>
    (function () {
        const table = document.getElementById('acs-table');
        if (!table) return;

        const etapaButtons = document.querySelectorAll('.acs-pill');
        const searchInput = document.getElementById('acs-search');

        function aplicarFiltros() {
            const activeBtn = document.querySelector('.acs-pill.active');
            const etapaFiltro = activeBtn ? (activeBtn.dataset.etapa || 'todas') : 'todas';
            const term = (searchInput?.value || '').toLowerCase().trim();

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const etapa = (row.getAttribute('data-etapa') || '').toLowerCase();
                const texto = (row.textContent || '').toLowerCase();

                let visible = true;

                if (etapaFiltro !== 'todas' && etapa !== etapaFiltro) {
                    visible = false;
                }

                if (term && !texto.includes(term)) {
                    visible = false;
                }

                row.style.display = visible ? '' : 'none';
            });
        }

        etapaButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                etapaButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                aplicarFiltros();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                aplicarFiltros();
            });
        }
    })();
</script>