<?php
// AXM/admin/usuarios.php
declare(strict_types=1);

$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));

$usuarios = []; // cada item: ['id','nombre','correo','telefono','estado','expediente','creado','ultima_act']
$stats = [
    'total' => 0,
    'aprobados' => 0,
    'pendientes' => 0,
    'rechazados' => 0,
    'con_expediente' => 0,
];

date_default_timezone_set('America/Mexico_City');

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }

    if (class_exists('Conexion')) {
        $db = new Conexion();

        // Verificar si existe tabla usuarios
        $tieneUsuarios = false;
        if ($res = $db->query("SHOW TABLES LIKE 'usuarios'")) {
            $tieneUsuarios = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneUsuarios) {
            // Tomamos * para adaptarnos a distintos esquemas
            $sql = "SELECT * FROM usuarios ORDER BY id DESC LIMIT 200";
            if ($res = $db->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                    $id = (int) ($row['id'] ?? 0);

                    // Nombre completo
                    $nombre = trim(
                        (($row['nombre'] ?? '') . ' ' .
                            ($row['apellido_paterno'] ?? '') . ' ' .
                            ($row['apellido_materno'] ?? ''))
                    );
                    if ($nombre === '') {
                        $nombre = 'Usuario #' . $id;
                    }

                    // Correo (varios posibles nombres de columna)
                    $correo = trim((string) ($row['correo'] ?? $row['email'] ?? ''));

                    // Teléfono (varias posibles columnas)
                    $telefono = trim((string) (
                        $row['telefono'] ??
                        $row['tel'] ??
                        $row['telefono_movil'] ??
                        ''
                    ));

                    // Estado de validación
                    $estado = strtolower((string) ($row['estado_validacion'] ?? 'pendiente'));
                    if (!in_array($estado, ['pendiente', 'aprobado', 'rechazado'], true)) {
                        $estado = 'pendiente';
                    }

                    // Num. expediente
                    $expediente = trim((string) ($row['Num_expediente'] ?? $row['num_expediente'] ?? ''));

                    // Fechas
                    $creado = (string) ($row['fecha_registro'] ?? $row['created_at'] ?? '');
                    $ultimaAct = (string) ($row['ultima_actualizacion_datos'] ?? $row['updated_at'] ?? '');

                    $usuarios[] = [
                        'id' => $id,
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'telefono' => $telefono,
                        'estado' => $estado,
                        'expediente' => $expediente,
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

// Mock demo si no hay usuarios
if (empty($usuarios)) {
    $usuarios = [
        [
            'id' => 1,
            'nombre' => 'Usuario Demo A',
            'correo' => 'demoA@ejemplo.com',
            'telefono' => '55 1111 1111',
            'estado' => 'pendiente',
            'expediente' => '',
            'creado' => date('Y-m-d 09:15:00', strtotime('-5 days')),
            'ultima_act' => date('Y-m-d 09:15:00', strtotime('-5 days')),
        ],
        [
            'id' => 2,
            'nombre' => 'Usuario Demo B',
            'correo' => 'demoB@ejemplo.com',
            'telefono' => '55 2222 2222',
            'estado' => 'aprobado',
            'expediente' => 'EXP-2025-001',
            'creado' => date('Y-m-d 10:30:00', strtotime('-10 days')),
            'ultima_act' => date('Y-m-d 11:00:00', strtotime('-2 days')),
        ],
        [
            'id' => 3,
            'nombre' => 'Usuario Demo C',
            'correo' => 'demoC@ejemplo.com',
            'telefono' => '55 3333 3333',
            'estado' => 'rechazado',
            'expediente' => 'EXP-2025-002',
            'creado' => date('Y-m-d 11:45:00', strtotime('-15 days')),
            'ultima_act' => date('Y-m-d 13:20:00', strtotime('-1 days')),
        ],
    ];
}

// Calcular estadísticas
foreach ($usuarios as $u) {
    $stats['total']++;
    if ($u['estado'] === 'aprobado') {
        $stats['aprobados']++;
    } elseif ($u['estado'] === 'rechazado') {
        $stats['rechazados']++;
    } else {
        $stats['pendientes']++;
    }

    if ($u['expediente'] !== '') {
        $stats['con_expediente']++;
    }
}

// Formateo helper
function axm_fmt_fecha_admin(?string $f): string
{
    if (!$f)
        return '—';
    $ts = strtotime($f);
    if (!$ts)
        return '—';
    return date('d/m/Y H:i', $ts);
}
?>
<style>
    /* ===== ADMIN USUARIOS (SCOPED) ===== */
    .au-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .au-head {
        border-radius: 18px;
        padding: 18px 18px 14px;
        background: linear-gradient(120deg, #111827 0%, #3B82F6 45%, #8A1538 100%);
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

    .au-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .18), transparent 60%);
        opacity: .7;
    }

    .au-head-main {
        max-width: 620px;
        position: relative;
        z-index: 1;
    }

    .au-title {
        margin: 0;
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        font-weight: 900;
    }

    .au-sub {
        margin: 6px 0 0;
        font-size: .9rem;
        color: #E5E7EB;
    }

    .au-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .au-chip {
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

    .au-chip strong {
        font-weight: 900;
    }

    .au-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 220px;
        position: relative;
        z-index: 1;
    }

    .au-btn-main,
    .au-btn-ghost {
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .au-btn-main {
        background: #F9FAFB;
        color: #111827;
    }

    .au-btn-ghost {
        background: rgba(17, 24, 39, .3);
        color: #F9FAFB;
        border: 1px solid rgba(249, 250, 251, .3);
    }

    .au-btn-ghost:hover {
        background: rgba(17, 24, 39, .5);
    }

    .au-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 16px;
    }

    @media (max-width: 980px) {
        .au-grid {
            grid-template-columns: 1fr;
        }
    }

    .au-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .06);
    }

    .au-card-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .au-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .au-card-sub {
        margin: 0;
        font-size: .85rem;
        color: #6B7280;
    }

    .au-kpi-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }

    .au-kpi {
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        padding: 8px 10px;
        font-size: .8rem;
        min-width: 120px;
    }

    .au-kpi-label {
        color: #6B7280;
        margin-bottom: 2px;
    }

    .au-kpi-value {
        font-size: 1.05rem;
        font-weight: 900;
        color: #111827;
    }

    .au-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 10px 0 6px;
        font-size: .8rem;
        justify-content: space-between;
    }

    .au-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .au-pill-filter {
        border-radius: 999px;
        padding: 4px 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        cursor: pointer;
    }

    .au-pill-filter.active {
        background: #EEF2FF;
        border-color: #C7D2FE;
        color: #3730A3;
        font-weight: 700;
    }

    .au-search {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .au-search input {
        border-radius: 999px;
        border: 1px solid #E5E7EB;
        padding: 5px 8px;
        font-size: .8rem;
    }

    .au-search input:focus {
        outline: none;
        border-color: #8A1538;
        box-shadow: 0 0 0 1px #8A153833;
    }

    .au-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .86rem;
        margin-top: 4px;
    }

    .au-table thead {
        background: #F3F4F6;
    }

    .au-table th,
    .au-table td {
        padding: 7px 6px;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
        vertical-align: top;
    }

    .au-table th {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #6B7280;
        font-weight: 700;
    }

    .au-table tbody tr:nth-child(even) {
        background: #F9FAFB;
    }

    .au-col-nombre {
        font-weight: 700;
        color: #111827;
    }

    .au-col-correo {
        font-size: .84rem;
        color: #374151;
        word-break: break-all;
    }

    .au-col-telefono {
        font-size: .8rem;
        color: #4B5563;
        white-space: nowrap;
    }

    .au-col-expediente {
        font-size: .82rem;
        color: #111827;
        white-space: nowrap;
    }

    .au-col-fecha {
        font-size: .8rem;
        color: #6B7280;
        white-space: nowrap;
    }

    .au-col-estado {
        font-size: .78rem;
    }

    .au-badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }

    .au-badge-aprobado {
        background: #ECFDF3;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .au-badge-pendiente {
        background: #FEF9C3;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    .au-badge-rechazado {
        background: #FEF2F2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }

    .au-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        font-size: .78rem;
    }

    .au-link-btn {
        border-radius: 999px;
        padding: 3px 8px;
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        cursor: pointer;
        text-decoration: none;
        color: #111827;
    }

    .au-link-btn:hover {
        background: #F3F4F6;
    }

    .au-side-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 6px;
        font-size: .85rem;
    }

    .au-side-item {
        padding: 9px 10px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        color: #374151;
    }

    .au-side-item strong {
        color: #111827;
    }

    .au-side-item span {
        font-size: .8rem;
        color: #6B7280;
    }

    .au-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: auFade .4s ease .05s forwards;
    }

    .au-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: auFade .4s ease .16s forwards;
    }

    @keyframes auFade {
        to {
            opacity: 1;
            transform: none;
        }
    }

    .au-row-estado-pendiente {}

    .au-row-estado-aprobado {}

    .au-row-estado-rechazado {}
</style>

<div class="au-wrap">
    <!-- ENCABEZADO -->
    <section class="au-head au-fade">
        <div class="au-head-main">
            <h2 class="au-title">Usuarios del sistema</h2>
            <p class="au-sub">
                Hola <?php echo htmlspecialchars($adminNombre); ?>. Desde aquí puedes revisar los usuarios,
                sus expedientes y el estado de validación de sus datos.
            </p>
            <div class="au-chips">
                <span class="au-chip">
                    Total: <strong><?php echo (int) $stats['total']; ?></strong>
                </span>
                <span class="au-chip">
                    Aprobados: <strong><?php echo (int) $stats['aprobados']; ?></strong>
                </span>
                <span class="au-chip">
                    Pendientes: <strong><?php echo (int) $stats['pendientes']; ?></strong>
                </span>
                <span class="au-chip">
                    Rechazados: <strong><?php echo (int) $stats['rechazados']; ?></strong>
                </span>
                <span class="au-chip">
                    Con expediente: <strong><?php echo (int) $stats['con_expediente']; ?></strong>
                </span>
            </div>
        </div>
        <div class="au-actions">
            <button type="button" class="au-btn-main axm-nav-link" data-view="consultas">
                Ver consultas / expedientes
            </button>
            <button type="button" class="au-btn-ghost axm-nav-link" data-view="notificaciones">
                Centro de notificaciones
            </button>
            <button type="button" class="au-btn-ghost axm-nav-link" data-view="dashboard">
                Volver al panel
            </button>
        </div>
    </section>

    <!-- GRID -->
    <section class="au-grid">
        <!-- LISTA PRINCIPAL -->
        <article class="au-card au-fade2">
            <div class="au-card-header">
                <div>
                    <h3 class="au-card-title">Listado de usuarios</h3>
                    <p class="au-card-sub">
                        Usa los filtros y el buscador para ubicar rápidamente a un usuario.
                    </p>
                </div>
                <div class="au-kpi-row">
                    <div class="au-kpi">
                        <div class="au-kpi-label">Pendientes</div>
                        <div class="au-kpi-value"><?php echo (int) $stats['pendientes']; ?></div>
                    </div>
                    <div class="au-kpi">
                        <div class="au-kpi-label">Aprobados</div>
                        <div class="au-kpi-value"><?php echo (int) $stats['aprobados']; ?></div>
                    </div>
                </div>
            </div>

            <div class="au-filter-row">
                <div class="au-pill-group">
                    <button type="button" class="au-pill-filter active" data-filter="todos">Todos</button>
                    <button type="button" class="au-pill-filter" data-filter="pendiente">Pendientes</button>
                    <button type="button" class="au-pill-filter" data-filter="aprobado">Aprobados</button>
                    <button type="button" class="au-pill-filter" data-filter="rechazado">Rechazados</button>
                </div>
                <div class="au-search">
                    <input type="text" id="au-search" placeholder="Buscar por nombre o correo">
                </div>
            </div>

            <table class="au-table" id="au-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Contacto</th>
                        <th>Expediente</th>
                        <th>Estado</th>
                        <th>Alta / Última act.</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <?php
                        $estado = $u['estado'];
                        $rowClass = 'au-row-estado-' . htmlspecialchars($estado, ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="<?php echo $rowClass; ?>" data-estado="<?php echo htmlspecialchars($estado); ?>">
                            <td><?php echo (int) $u['id']; ?></td>
                            <td class="au-col-nombre">
                                <?php echo htmlspecialchars($u['nombre']); ?>
                            </td>
                            <td>
                                <div class="au-col-correo">
                                    <?php if ($u['correo']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($u['correo']); ?>">
                                            <?php echo htmlspecialchars($u['correo']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span>Sin correo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="au-col-telefono">
                                    <?php echo $u['telefono'] ? htmlspecialchars($u['telefono']) : 'Sin teléfono'; ?>
                                </div>
                            </td>
                            <td class="au-col-expediente">
                                <?php echo $u['expediente'] !== '' ? htmlspecialchars($u['expediente']) : '—'; ?>
                            </td>
                            <td class="au-col-estado">
                                <?php
                                $badgeClass = 'au-badge-pendiente';
                                $label = 'Pendiente';
                                if ($estado === 'aprobado') {
                                    $badgeClass = 'au-badge-aprobado';
                                    $label = 'Aprobado';
                                } elseif ($estado === 'rechazado') {
                                    $badgeClass = 'au-badge-rechazado';
                                    $label = 'Rechazado / Observaciones';
                                }
                                ?>
                                <span class="au-badge-estado <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </span>
                            </td>
                            <td class="au-col-fecha">
                                <div><?php echo htmlspecialchars(axm_fmt_fecha_admin($u['creado'])); ?></div>
                                <div style="font-size:.75rem;">Act:
                                    <?php echo htmlspecialchars(axm_fmt_fecha_admin($u['ultima_act'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="au-actions-row">
                                    <a class="au-link-btn axm-nav-link" data-view="consultas" data-anchor="" href="/AXM/">
                                        Ver expediente
                                    </a>
                                    <a class="au-link-btn axm-nav-link" data-view="notificaciones" href="/AXM/">
                                        Notif.
                                    </a>
                                    <!-- Podrías añadir un link directo a edición si más adelante haces admin/editar_usuario.php -->
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <!-- PANEL LATERAL -->
        <aside class="au-card au-fade2">
            <div class="au-card-header">
                <div>
                    <h3 class="au-card-title">Resumen rápido</h3>
                    <p class="au-card-sub">
                        Vista general para entender el estado de la base de usuarios.
                    </p>
                </div>
            </div>

            <div class="au-side-list">
                <div class="au-side-item">
                    <strong>Usuarios pendientes</strong><br>
                    <span>
                        Tienes <?php echo (int) $stats['pendientes']; ?> usuarios con validación pendiente.
                        Prioriza su revisión para avanzar sus expedientes.
                    </span>
                </div>
                <div class="au-side-item">
                    <strong>Usuarios aprobados</strong><br>
                    <span>
                        <?php echo (int) $stats['aprobados']; ?> usuarios con datos aprobados.
                        Pueden estar ya en fase de trámite o resolución.
                    </span>
                </div>
                <div class="au-side-item">
                    <strong>Con expediente asignado</strong><br>
                    <span>
                        <?php echo (int) $stats['con_expediente']; ?> usuarios tienen un número de expediente vinculado.
                        Esto ayuda a rastrear fácilmente sus casos en juzgado o en ISSSTE.
                    </span>
                </div>
                <div class="au-side-item">
                    <strong>Sugerencia de uso</strong><br>
                    <span>
                        Usa esta vista como base para ir usuario por usuario, revisando si ya completaron
                        documentos y si es necesario enviarles notificaciones adicionales o reagendar citas.
                    </span>
                </div>
            </div>
        </aside>
    </section>
</div>

<script>
    (function () {
        const table = document.getElementById('au-table');
        if (!table) return;

        const filterButtons = document.querySelectorAll('.au-pill-filter');
        const searchInput = document.getElementById('au-search');

        function applyFilters() {
            const activeBtn = document.querySelector('.au-pill-filter.active');
            const estadoFiltro = activeBtn ? (activeBtn.dataset.filter || 'todos') : 'todos';
            const term = (searchInput?.value || '').toLowerCase().trim();

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const estado = (row.getAttribute('data-estado') || '').toLowerCase();
                const texto = (row.textContent || '').toLowerCase();

                let visible = true;

                if (estadoFiltro !== 'todos' && estado !== estadoFiltro) {
                    visible = false;
                }

                if (term && !texto.includes(term)) {
                    visible = false;
                }

                row.style.display = visible ? '' : 'none';
            });
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                applyFilters();
            });
        }
    })();
</script>