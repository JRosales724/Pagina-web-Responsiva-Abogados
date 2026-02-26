<?php
// AXM/admin/historial.php
declare(strict_types=1);

$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));

// Estructuras base
$movimientos = []; // cada item: ['tipo'=>'estatus|cita','usuario'=>'...','detalle'=>'...','fecha'=>'Y-m-d H:i:s']
$stats = [
    'total' => 0,
    'estatus' => 0,
    'citas' => 0,
    'hoy' => 0,
    'semana' => 0,
];

// Fechas de referencia
date_default_timezone_set('America/Mexico_City');
$hoyYmd = date('Y-m-d');
$inicioSemana = date('Y-m-d', strtotime('monday this week'));

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }

    if (class_exists('Conexion')) {
        $db = new Conexion();

        // ====== 1) Historial de estatus (si existe la tabla) ======
        $tieneHist = false;
        if ($res = $db->query("SHOW TABLES LIKE 'estatus_historial'")) {
            $tieneHist = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneHist) {
            // Intentar traer también nombre del usuario si existe tabla usuarios
            $joinUsuarios = false;
            if ($res = $db->query("SHOW TABLES LIKE 'usuarios'")) {
                $joinUsuarios = (bool) $res->fetch_row();
                $res->close();
            }

            if ($joinUsuarios) {
                $sql = "
                    SELECT 
                        eh.id,
                        eh.id_usuario,
                        eh.estado,
                        eh.nota,
                        eh.fecha,
                        CONCAT(IFNULL(u.nombre,''),' ',IFNULL(u.apellido_paterno,'')) AS nombre_usuario
                    FROM estatus_historial eh
                    LEFT JOIN usuarios u ON u.id = eh.id_usuario
                    ORDER BY eh.fecha DESC, eh.id DESC
                    LIMIT 50
                ";
            } else {
                $sql = "
                    SELECT 
                        id,
                        id_usuario,
                        estado,
                        nota,
                        fecha,
                        NULL AS nombre_usuario
                    FROM estatus_historial
                    ORDER BY fecha DESC, id DESC
                    LIMIT 50
                ";
            }

            if ($res = $db->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                    $fecha = (string) ($row['fecha'] ?? '');
                    $usuarioNombre = trim((string) ($row['nombre_usuario'] ?? ''));
                    if ($usuarioNombre === '' && !empty($row['id_usuario'])) {
                        $usuarioNombre = 'Usuario #' . (int) $row['id_usuario'];
                    } elseif ($usuarioNombre === '') {
                        $usuarioNombre = 'Usuario sin nombre';
                    }

                    $estado = strtolower((string) ($row['estado'] ?? ''));
                    $etiqueta = match ($estado) {
                        'aprobado' => 'Expediente aprobado',
                        'rechazado' => 'Devuelto con observaciones',
                        'pendiente' => 'Pendiente',
                        default => 'Cambio de estatus: ' . ucfirst($estado),
                    };

                    $nota = trim((string) ($row['nota'] ?? ''));
                    if ($nota !== '') {
                        $etiqueta .= ' — ' . $nota;
                    }

                    $movimientos[] = [
                        'tipo' => 'estatus',
                        'usuario' => $usuarioNombre,
                        'detalle' => $etiqueta,
                        'fecha' => $fecha,
                    ];
                }
                $res->close();
            }
        }

        // ====== 2) Historial de citas (si existe la tabla) ======
        $tieneCitas = false;
        if ($res = $db->query("SHOW TABLES LIKE 'citas'")) {
            $tieneCitas = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneCitas) {
            // De nuevo, intentamos traer el nombre del usuario si existe tabla usuarios
            $joinUsuarios = false;
            if ($res = $db->query("SHOW TABLES LIKE 'usuarios'")) {
                $joinUsuarios = (bool) $res->fetch_row();
                $res->close();
            }

            if ($joinUsuarios) {
                $sqlC = "
                    SELECT
                        c.id,
                        c.id_usuario,
                        c.fecha,
                        c.hora,
                        c.estado,
                        c.nota,
                        CONCAT(IFNULL(u.nombre,''),' ',IFNULL(u.apellido_paterno,'')) AS nombre_usuario
                    FROM citas c
                    LEFT JOIN usuarios u ON u.id = c.id_usuario
                    ORDER BY CONCAT(c.fecha,' ',IFNULL(c.hora,'00:00')) DESC, c.id DESC
                    LIMIT 50
                ";
            } else {
                $sqlC = "
                    SELECT
                        id,
                        id_usuario,
                        fecha,
                        hora,
                        estado,
                        nota,
                        NULL AS nombre_usuario
                    FROM citas
                    ORDER BY CONCAT(fecha,' ',IFNULL(hora,'00:00')) DESC, id DESC
                    LIMIT 50
                ";
            }

            if ($res = $db->query($sqlC)) {
                while ($row = $res->fetch_assoc()) {
                    $fecha = (string) ($row['fecha'] ?? '');
                    $hora = (string) ($row['hora'] ?? '');
                    $fechaHora = trim($fecha . ' ' . ($hora ?: '00:00:00'));

                    $usuarioNombre = trim((string) ($row['nombre_usuario'] ?? ''));
                    if ($usuarioNombre === '' && !empty($row['id_usuario'])) {
                        $usuarioNombre = 'Usuario #' . (int) $row['id_usuario'];
                    } elseif ($usuarioNombre === '') {
                        $usuarioNombre = 'Usuario sin nombre';
                    }

                    $estado = strtolower((string) ($row['estado'] ?? ''));
                    $nota = trim((string) ($row['nota'] ?? ''));
                    $etiqueta = match ($estado) {
                        'agendada' => 'Cita agendada',
                        'reprogramada' => 'Cita reprogramada',
                        'cancelada' => 'Cita cancelada',
                        default => 'Movimiento de cita: ' . ucfirst($estado),
                    };
                    if ($nota !== '') {
                        $etiqueta .= ' — ' . $nota;
                    }

                    $movimientos[] = [
                        'tipo' => 'cita',
                        'usuario' => $usuarioNombre,
                        'detalle' => $etiqueta,
                        'fecha' => $fechaHora,
                    ];
                }
                $res->close();
            }
        }
    }
} catch (\Throwable $e) {
    // error_log($e->getMessage());
}

// Si no hay nada, sembramos mock
if (empty($movimientos)) {
    $movimientos = [
        [
            'tipo' => 'estatus',
            'usuario' => 'Usuario demo 1',
            'detalle' => 'Expediente aprobado — Todo en orden',
            'fecha' => date('Y-m-d 09:20:00', strtotime('-1 day')),
        ],
        [
            'tipo' => 'cita',
            'usuario' => 'Usuario demo 2',
            'detalle' => 'Cita agendada — 12:00 hrs',
            'fecha' => date('Y-m-d 12:00:00', strtotime('-2 days')),
        ],
        [
            'tipo' => 'estatus',
            'usuario' => 'Usuario demo 3',
            'detalle' => 'Devuelto con observaciones — Falta oficio de negativa',
            'fecha' => date('Y-m-d 16:30:00', strtotime('-3 days')),
        ],
    ];
}

// Ordenamos por fecha descendente por si vinieron mezclados
usort($movimientos, function (array $a, array $b): int {
    return strcmp($b['fecha'], $a['fecha']);
});

// Recalcular stats
foreach ($movimientos as $m) {
    $stats['total']++;

    if ($m['tipo'] === 'estatus') {
        $stats['estatus']++;
    } elseif ($m['tipo'] === 'cita') {
        $stats['citas']++;
    }

    $fecha = substr($m['fecha'], 0, 10);
    if ($fecha === $hoyYmd) {
        $stats['hoy']++;
    }
    if ($fecha >= $inicioSemana) {
        $stats['semana']++;
    }
}
?>
<style>
    /* ===== ADMIN HISTORIAL (SCOPED) ===== */
    .ah-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .ah-head {
        border-radius: 18px;
        padding: 18px 18px 14px;
        background: #111827;
        color: #F9FAFB;
        box-shadow: 0 18px 34px rgba(0,0,0,.25);
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
    }

    .ah-head-main {
        max-width: 620px;
    }

    .ah-title {
        margin: 0;
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        font-weight: 900;
    }

    .ah-sub {
        margin: 6px 0 0;
        font-size: .9rem;
        color: #E5E7EB;
    }

    .ah-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .ah-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(31,41,55,.8);
        border: 1px solid rgba(249,250,251,.35);
        font-size: .78rem;
        font-weight: 800;
    }

    .ah-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 220px;
    }

    .ah-btn-main,
    .ah-btn-ghost {
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .ah-btn-main {
        background: #F9FAFB;
        color: #111827;
    }

    .ah-btn-ghost {
        background: rgba(17,24,39,.3);
        color: #F9FAFB;
        border: 1px solid rgba(249,250,251,.3);
    }

    .ah-btn-ghost:hover {
        background: rgba(17,24,39,.5);
    }

    .ah-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 2.2fr 1.2fr;
        gap: 16px;
    }

    @media (max-width: 980px) {
        .ah-grid {
            grid-template-columns: 1fr;
        }
    }

    .ah-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 26px rgba(0,0,0,.06);
    }

    .ah-card-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .ah-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .ah-card-sub {
        margin: 0;
        font-size: .85rem;
        color: #6B7280;
    }

    .ah-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4,minmax(0,1fr));
        gap: 8px;
        margin: 6px 0 10px;
    }

    @media (max-width: 768px) {
        .ah-kpi-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }
    }

    .ah-kpi {
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        padding: 8px;
    }

    .ah-kpi-label {
        font-size: .78rem;
        color: #6B7280;
        margin-bottom: 2px;
    }

    .ah-kpi-value {
        font-size: 1.1rem;
        font-weight: 900;
        color: #111827;
    }

    .ah-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .86rem;
        margin-top: 8px;
    }

    .ah-table thead {
        background: #F3F4F6;
    }

    .ah-table th,
    .ah-table td {
        padding: 7px 6px;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
        vertical-align: top;
    }

    .ah-table th {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #6B7280;
        font-weight: 700;
    }

    .ah-table tbody tr:nth-child(even) {
        background: #F9FAFB;
    }

    .ah-col-usuario {
        font-weight: 700;
        color: #111827;
    }

    .ah-col-tipo {
        font-size: .78rem;
    }

    .ah-badge-tipo {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }

    .ah-badge-estatus {
        background: #EEF2FF;
        color: #3730A3;
        border: 1px solid #C7D2FE;
    }

    .ah-badge-cita {
        background: #ECFDF3;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .ah-col-detalle {
        font-size: .85rem;
        color: #374151;
    }

    .ah-col-fecha {
        font-size: .8rem;
        color: #6B7280;
        white-space: nowrap;
    }

    /* Lado derecho: timeline simple / últimos movimientos */
    .ah-side-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 8px;
    }

    .ah-side-item {
        padding: 8px 10px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        font-size: .85rem;
        color: #374151;
        position: relative;
        padding-left: 18px;
    }

    .ah-side-item::before {
        content: "";
        position: absolute;
        left: 6px;
        top: 10px;
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #8A1538;
    }

    .ah-side-item strong {
        color: #111827;
    }

    .ah-side-item span {
        font-size: .78rem;
        color: #6B7280;
    }

    .ah-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: ahFade .4s ease .05s forwards;
    }

    .ah-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: ahFade .4s ease .18s forwards;
    }

    @keyframes ahFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="ah-wrap">
    <!-- ENCABEZADO -->
    <section class="ah-head ah-fade">
        <div class="ah-head-main">
            <h2 class="ah-title">Historial general de movimientos</h2>
            <p class="ah-sub">
                Hola <?php echo htmlspecialchars($adminNombre); ?>. Aquí puedes ver los cambios recientes de estatus
                y las citas relacionadas con los expedientes de los usuarios.
            </p>
            <div class="ah-chips">
                <span class="ah-chip">
                    Movimientos totales: <strong><?php echo (int) $stats['total']; ?></strong>
                </span>
                <span class="ah-chip">
                    Esta semana: <strong><?php echo (int) $stats['semana']; ?></strong>
                </span>
                <span class="ah-chip">
                    Hoy: <strong><?php echo (int) $stats['hoy']; ?></strong>
                </span>
            </div>
        </div>
        <div class="ah-actions">
            <button type="button" class="ah-btn-main axm-nav-link" data-view="consultas">
                Volver a Consultas
            </button>
            <button type="button" class="ah-btn-ghost axm-nav-link" data-view="casos">
                Ver módulo de casos
            </button>
            <button type="button" class="ah-btn-ghost axm-nav-link" data-view="usuarios">
                Ir a Usuarios
            </button>
        </div>
    </section>

    <!-- GRID: tabla + lateral -->
    <section class="ah-grid">
        <!-- TABLA PRINCIPAL -->
        <article class="ah-card ah-fade2">
            <div class="ah-card-header">
                <div>
                    <h3 class="ah-card-title">Movimientos recientes</h3>
                    <p class="ah-card-sub">
                        Últimos cambios de estatus y movimientos de citas registrados en el sistema.
                    </p>
                </div>
                <div class="ah-kpi-grid">
                    <div class="ah-kpi">
                        <div class="ah-kpi-label">Cambios de estatus</div>
                        <div class="ah-kpi-value"><?php echo (int) $stats['estatus']; ?></div>
                    </div>
                    <div class="ah-kpi">
                        <div class="ah-kpi-label">Mov. de citas</div>
                        <div class="ah-kpi-value"><?php echo (int) $stats['citas']; ?></div>
                    </div>
                    <div class="ah-kpi">
                        <div class="ah-kpi-label">Hoy</div>
                        <div class="ah-kpi-value"><?php echo (int) $stats['hoy']; ?></div>
                    </div>
                    <div class="ah-kpi">
                        <div class="ah-kpi-label">Esta semana</div>
                        <div class="ah-kpi-value"><?php echo (int) $stats['semana']; ?></div>
                    </div>
                </div>
            </div>

            <table class="ah-table">
                <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Tipo</th>
                    <th>Detalle</th>
                    <th>Fecha</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($movimientos as $m): ?>
                        <?php
                        $tipo = $m['tipo'] === 'cita' ? 'cita' : 'estatus';
                        $badgeCls = $tipo === 'cita' ? 'ah-badge-cita' : 'ah-badge-estatus';
                        $badgeText = $tipo === 'cita' ? 'Cita' : 'Estatus';

                        $fechaFmt = '';
                        if (!empty($m['fecha'])) {
                            $ts = strtotime($m['fecha']);
                            if ($ts) {
                                $fechaFmt = date('d/m/Y H:i', $ts);
                            }
                        }
                        ?>
                        <tr>
                            <td class="ah-col-usuario">
                                <?php echo htmlspecialchars($m['usuario']); ?>
                            </td>
                            <td class="ah-col-tipo">
                                <span class="ah-badge-tipo <?php echo $badgeCls; ?>">
                                    <?php echo $badgeText; ?>
                                </span>
                            </td>
                            <td class="ah-col-detalle">
                                <?php echo htmlspecialchars($m['detalle']); ?>
                            </td>
                            <td class="ah-col-fecha">
                                <?php echo $fechaFmt !== '' ? htmlspecialchars($fechaFmt) : '—'; ?>
                            </td>
                        </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <!-- LATERAL DERECHO -->
        <aside class="ah-card ah-fade2">
            <div class="ah-card-header">
                <div>
                    <h3 class="ah-card-title">Resumen de actividad</h3>
                    <p class="ah-card-sub">
                        Un vistazo rápido a cómo se ha movido la carga de trabajo.
                    </p>
                </div>
            </div>

            <div class="ah-side-list">
                <div class="ah-side-item">
                    <strong>Cambios de estatus</strong><br>
                    <span>
                        Se han registrado <?php echo (int) $stats['estatus']; ?> cambios de estatus recientes.
                        Revisa que los expedientes con observaciones estén siendo atendidos.
                    </span>
                </div>
                <div class="ah-side-item">
                    <strong>Movimientos de citas</strong><br>
                    <span>
                        Hay <?php echo (int) $stats['citas']; ?> movimientos de citas recientes
                        (agendadas, reprogramadas o canceladas). Úsalos para organizar tus sesiones.
                    </span>
                </div>
                <div class="ah-side-item">
                    <strong>Actividad de hoy</strong><br>
                    <span>
                        Hoy se han registrado <?php echo (int) $stats['hoy']; ?> movimientos.
                        Ideal para hacer un cierre rápido de jornada.
                    </span>
                </div>
                <div class="ah-side-item">
                    <strong>Actividad semanal</strong><br>
                    <span>
                        En lo que va de la semana se han contabilizado
                        <?php echo (int) $stats['semana']; ?> movimientos.
                        Te ayuda a ver la carga global de trabajo.
                    </span>
                </div>
            </div>
        </aside>
    </section>
</div>
