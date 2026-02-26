<?php
// AXM/admin/notificaciones.php
declare(strict_types=1);

$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));

date_default_timezone_set('America/Mexico_City');
$hoyYmd = date('Y-m-d');
$inicioSemana = date('Y-m-d', strtotime('monday this week'));

$notificaciones = []; // cada item: ['id'=>int,'usuario'=>'','titulo'=>'','mensaje'=>'','leido'=>bool,'fecha'=>'Y-m-d H:i:s']
$stats = [
    'total' => 0,
    'no_leidas' => 0,
    'hoy' => 0,
    'semana' => 0,
];

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }

    if (class_exists('Conexion')) {
        $db = new Conexion();

        // ¿Existe tabla notificaciones?
        $tieneNotif = false;
        if ($res = $db->query("SHOW TABLES LIKE 'notificaciones'")) {
            $tieneNotif = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneNotif) {
            // Intentar join con usuarios para nombre
            $joinUsuarios = false;
            if ($res = $db->query("SHOW TABLES LIKE 'usuarios'")) {
                $joinUsuarios = (bool) $res->fetch_row();
                $res->close();
            }

            if ($joinUsuarios) {
                $sql = "
                    SELECT 
                        n.id,
                        n.id_usuario,
                        n.titulo,
                        n.mensaje,
                        n.leido,
                        n.creado_en,
                        CONCAT(IFNULL(u.nombre,''),' ',IFNULL(u.apellido_paterno,'')) AS nombre_usuario
                    FROM notificaciones n
                    LEFT JOIN usuarios u ON u.id = n.id_usuario
                    ORDER BY n.creado_en DESC, n.id DESC
                    LIMIT 80
                ";
            } else {
                $sql = "
                    SELECT 
                        id,
                        id_usuario,
                        titulo,
                        mensaje,
                        leido,
                        creado_en,
                        NULL AS nombre_usuario
                    FROM notificaciones
                    ORDER BY creado_en DESC, id DESC
                    LIMIT 80
                ";
            }

            if ($res = $db->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                    $usuario = trim((string) ($row['nombre_usuario'] ?? ''));
                    if ($usuario === '' && !empty($row['id_usuario'])) {
                        $usuario = 'Usuario #' . (int) $row['id_usuario'];
                    } elseif ($usuario === '') {
                        $usuario = 'Usuario sin nombre';
                    }

                    $notificaciones[] = [
                        'id' => (int) ($row['id'] ?? 0),
                        'usuario' => $usuario,
                        'titulo' => (string) ($row['titulo'] ?? ''),
                        'mensaje' => (string) ($row['mensaje'] ?? ''),
                        'leido' => !empty($row['leido']),
                        'fecha' => (string) ($row['creado_en'] ?? ''),
                    ];
                }
                $res->close();
            }
        }
    }
} catch (\Throwable $e) {
    // error_log($e->getMessage());
}

// Mock si no hay nada
if (empty($notificaciones)) {
    $notificaciones = [
        [
            'id' => 1,
            'usuario' => 'Usuario demo 1',
            'titulo' => 'Expediente aprobado',
            'mensaje' => 'Se notificó al usuario que su expediente fue aprobado.',
            'leido' => true,
            'fecha' => date('Y-m-d 10:05:00', strtotime('-1 day')),
        ],
        [
            'id' => 2,
            'usuario' => 'Usuario demo 2',
            'titulo' => 'Faltan documentos',
            'mensaje' => 'Se solicitó subir oficio de negativa y talón de pago reciente.',
            'leido' => false,
            'fecha' => date('Y-m-d 08:40:00'),
        ],
        [
            'id' => 3,
            'usuario' => 'Usuario demo 3',
            'titulo' => 'Recordatorio de cita',
            'mensaje' => 'Cita programada para mañana a las 12:00 hrs.',
            'leido' => false,
            'fecha' => date('Y-m-d 16:20:00', strtotime('-2 days')),
        ],
    ];
}

// Recalcular stats
foreach ($notificaciones as $n) {
    $stats['total']++;
    if (!$n['leido']) {
        $stats['no_leidas']++;
    }

    $f = substr($n['fecha'], 0, 10);
    if ($f === $hoyYmd) {
        $stats['hoy']++;
    }
    if ($f >= $inicioSemana) {
        $stats['semana']++;
    }
}
?>
<style>
    /* ===== ADMIN NOTIFICACIONES (SCOPED) ===== */
    .an-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .an-head {
        border-radius: 18px;
        padding: 18px 18px 14px;
        background: linear-gradient(120deg, #111827 0%, #8A1538 55%, #6E102C 100%);
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

    .an-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .18), transparent 60%);
        opacity: .7;
    }

    .an-head-main {
        max-width: 620px;
        position: relative;
        z-index: 1;
    }

    .an-title {
        margin: 0;
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        font-weight: 900;
    }

    .an-sub {
        margin: 6px 0 0;
        font-size: .9rem;
        color: #E5E7EB;
    }

    .an-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .an-chip {
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

    .an-chip strong {
        font-weight: 900;
    }

    .an-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 220px;
        position: relative;
        z-index: 1;
    }

    .an-btn-main,
    .an-btn-ghost {
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .an-btn-main {
        background: #F9FAFB;
        color: #111827;
    }

    .an-btn-ghost {
        background: rgba(17, 24, 39, .3);
        color: #F9FAFB;
        border: 1px solid rgba(249, 250, 251, .3);
    }

    .an-btn-ghost:hover {
        background: rgba(17, 24, 39, .5);
    }

    .an-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 2.1fr 1.1fr;
        gap: 16px;
    }

    @media (max-width: 980px) {
        .an-grid {
            grid-template-columns: 1fr;
        }
    }

    .an-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .06);
    }

    .an-card-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .an-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .an-card-sub {
        margin: 0;
        font-size: .85rem;
        color: #6B7280;
    }

    .an-kpi-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }

    .an-kpi {
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        padding: 8px 10px;
        font-size: .8rem;
        min-width: 120px;
    }

    .an-kpi-label {
        color: #6B7280;
        margin-bottom: 2px;
    }

    .an-kpi-value {
        font-size: 1.05rem;
        font-weight: 900;
        color: #111827;
    }

    .an-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 8px 0 6px;
        font-size: .8rem;
    }

    .an-pill-filter {
        border-radius: 999px;
        padding: 4px 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        cursor: pointer;
    }

    .an-pill-filter.active {
        background: #EEF2FF;
        border-color: #C7D2FE;
        color: #3730A3;
        font-weight: 700;
    }

    .an-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .86rem;
        margin-top: 4px;
    }

    .an-table thead {
        background: #F3F4F6;
    }

    .an-table th,
    .an-table td {
        padding: 7px 6px;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
        vertical-align: top;
    }

    .an-table th {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #6B7280;
        font-weight: 700;
    }

    .an-table tbody tr:nth-child(even) {
        background: #F9FAFB;
    }

    .an-col-usuario {
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
    }

    .an-col-titulo {
        font-weight: 700;
        color: #111827;
    }

    .an-col-mensaje {
        font-size: .84rem;
        color: #374151;
    }

    .an-col-fecha {
        font-size: .8rem;
        color: #6B7280;
        white-space: nowrap;
    }

    .an-col-estado {
        font-size: .78rem;
    }

    .an-badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }

    .an-badge-leido {
        background: #ECFDF3;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .an-badge-noleido {
        background: #FEF2F2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }

    .an-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        font-size: .78rem;
    }

    .an-link-btn {
        border-radius: 999px;
        padding: 3px 8px;
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        cursor: pointer;
        text-decoration: none;
        color: #111827;
    }

    .an-link-btn:hover {
        background: #F3F4F6;
    }

    /* Lateral derecho */
    .an-side-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 6px;
    }

    .an-side-item {
        padding: 9px 10px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        font-size: .85rem;
        color: #374151;
    }

    .an-side-item strong {
        color: #111827;
    }

    .an-side-item span {
        font-size: .8rem;
        color: #6B7280;
    }

    .an-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: anFade .4s ease .05s forwards;
    }

    .an-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: anFade .4s ease .16s forwards;
    }

    @keyframes anFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="an-wrap">
    <!-- ENCABEZADO -->
    <section class="an-head an-fade">
        <div class="an-head-main">
            <h2 class="an-title">Centro de notificaciones</h2>
            <p class="an-sub">
                Hola <?php echo htmlspecialchars($adminNombre); ?>. Aquí ves las notificaciones enviadas a los usuarios:
                avisos de estatus, recordatorios de cita y solicitudes de documentos.
            </p>
            <div class="an-chips">
                <span class="an-chip">
                    Total: <strong><?php echo (int) $stats['total']; ?></strong>
                </span>
                <span class="an-chip">
                    No leídas: <strong><?php echo (int) $stats['no_leidas']; ?></strong>
                </span>
                <span class="an-chip">
                    Hoy: <strong><?php echo (int) $stats['hoy']; ?></strong>
                </span>
                <span class="an-chip">
                    Esta semana: <strong><?php echo (int) $stats['semana']; ?></strong>
                </span>
            </div>
        </div>
        <div class="an-actions">
            <button type="button" class="an-btn-main axm-nav-link" data-view="consultas">
                Ir a Consultas / Casos
            </button>
            <button type="button" class="an-btn-ghost axm-nav-link" data-view="usuarios">
                Ver usuarios
            </button>
            <button type="button" class="an-btn-ghost axm-nav-link" data-view="dashboard">
                Volver al panel
            </button>
        </div>
    </section>

    <!-- GRID -->
    <section class="an-grid">
        <!-- LISTA PRINCIPAL -->
        <article class="an-card an-fade2">
            <div class="an-card-header">
                <div>
                    <h3 class="an-card-title">Notificaciones recientes</h3>
                    <p class="an-card-sub">
                        Listado de notificaciones generadas por el sistema o por los administradores.
                    </p>
                </div>
                <div class="an-kpi-row">
                    <div class="an-kpi">
                        <div class="an-kpi-label">Total</div>
                        <div class="an-kpi-value"><?php echo (int) $stats['total']; ?></div>
                    </div>
                    <div class="an-kpi">
                        <div class="an-kpi-label">No leídas</div>
                        <div class="an-kpi-value"><?php echo (int) $stats['no_leidas']; ?></div>
                    </div>
                </div>
            </div>

            <div class="an-filter-row">
                <button type="button" class="an-pill-filter active" data-filter="todos">Todos</button>
                <button type="button" class="an-pill-filter" data-filter="noleidos">No leídos</button>
                <button type="button" class="an-pill-filter" data-filter="hoy">Hoy</button>
                <button type="button" class="an-pill-filter" data-filter="semana">Esta semana</button>
            </div>

            <table class="an-table" id="an-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Título</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificaciones as $n): ?>
                        <?php
                        $fechaFmt = '';
                        if (!empty($n['fecha'])) {
                            $ts = strtotime($n['fecha']);
                            if ($ts) {
                                $fechaFmt = date('d/m/Y H:i', $ts);
                            }
                        }
                        $rowClasses = [];
                        if (!$n['leido']) {
                            $rowClasses[] = 'an-row-noleido';
                        }
                        if (substr($n['fecha'], 0, 10) === $hoyYmd) {
                            $rowClasses[] = 'an-row-hoy';
                        }
                        if (substr($n['fecha'], 0, 10) >= $inicioSemana) {
                            $rowClasses[] = 'an-row-semana';
                        }
                        ?>
                        <tr class="<?php echo implode(' ', $rowClasses); ?>">
                            <td class="an-col-usuario">
                                <?php echo htmlspecialchars($n['usuario']); ?>
                            </td>
                            <td class="an-col-titulo">
                                <?php echo htmlspecialchars($n['titulo'] ?: 'Sin título'); ?>
                            </td>
                            <td class="an-col-mensaje">
                                <?php echo htmlspecialchars(mb_strimwidth($n['mensaje'], 0, 120, '…', 'UTF-8')); ?>
                            </td>
                            <td class="an-col-estado">
                                <?php if ($n['leido']): ?>
                                    <span class="an-badge-estado an-badge-leido">Leída</span>
                                <?php else: ?>
                                    <span class="an-badge-estado an-badge-noleido">No leída</span>
                                <?php endif; ?>
                            </td>
                            <td class="an-col-fecha">
                                <?php echo $fechaFmt !== '' ? htmlspecialchars($fechaFmt) : '—'; ?>
                            </td>
                            <td>
                                <div class="an-actions-row">
                                    <a class="an-link-btn"
                                        href="/AXM/php/notificaciones_leer.php?id=<?php echo (int) $n['id']; ?>"
                                        target="_blank" rel="noopener">
                                        Ver
                                    </a>
                                    <!-- En futuro puedes implementar marcar como leída desde aquí por AJAX -->
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <!-- PANEL LATERAL -->
        <aside class="an-card an-fade2">
            <div class="an-card-header">
                <div>
                    <h3 class="an-card-title">Resumen y tips</h3>
                    <p class="an-card-sub">
                        Usa las notificaciones para mantener informados a los usuarios sin saturarlos.
                    </p>
                </div>
            </div>

            <div class="an-side-list">
                <div class="an-side-item">
                    <strong>No leídas</strong><br>
                    <span>
                        Actualmente hay <?php echo (int) $stats['no_leidas']; ?> notificaciones marcadas como no leídas.
                        Esto te da una idea de qué usuarios quizá aún no han visto sus avisos.
                    </span>
                </div>
                <div class="an-side-item">
                    <strong>Actividad de hoy</strong><br>
                    <span>
                        Hoy se han generado <?php echo (int) $stats['hoy']; ?> notificaciones.
                        Ideal para revisar rápidamente los movimientos del día.
                    </span>
                </div>
                <div class="an-side-item">
                    <strong>Esta semana</strong><br>
                    <span>
                        En la semana van <?php echo (int) $stats['semana']; ?> notificaciones.
                        Puedes usar este dato para medir la carga de comunicación con usuarios.
                    </span>
                </div>
                <div class="an-side-item">
                    <strong>Buenas prácticas</strong><br>
                    <span>
                        Agrupa mensajes: en vez de mandar muchas notificaciones cortas,
                        envía una sola con un resumen claro del estatus y los documentos faltantes.
                    </span>
                </div>
            </div>
        </aside>
    </section>
</div>

<script>
    // Filtros simples en el front (solo ocultan/ muestran filas)
    (function () {
        const table = document.getElementById('an-table');
        if (!table) return;

        const buttons = document.querySelectorAll('.an-pill-filter');

        function applyFilter(filter) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.display = '';
                if (filter === 'noleidos' && !row.classList.contains('an-row-noleido')) {
                    row.style.display = 'none';
                }
                if (filter === 'hoy' && !row.classList.contains('an-row-hoy')) {
                    row.style.display = 'none';
                }
                if (filter === 'semana' && !row.classList.contains('an-row-semana')) {
                    row.style.display = 'none';
                }
            });
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                applyFilter(btn.dataset.filter || 'todos');
            });
        });
    })();
</script>