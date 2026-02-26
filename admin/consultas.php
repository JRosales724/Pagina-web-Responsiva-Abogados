<?php
// AXM/admin/consultas.php
declare(strict_types=1);

// Nombre admin (opcional para el encabezado)
$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));

// Datos por defecto
$stats = [
    'total' => 0,
    'pendiente' => 0,
    'aprobado' => 0,
    'rechazado' => 0,
    'otros' => 0,
];

$casos = []; // filas de usuarios/expedientes

try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }

    if (class_exists('Conexion')) {
        $db = new Conexion();

        // ¿Existe tabla usuarios?
        $tieneUsuarios = false;
        if ($res = $db->query("SHOW TABLES LIKE 'usuarios'")) {
            $tieneUsuarios = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneUsuarios) {
            // Conteos por estado_validacion
            if (
                $res = $db->query("
                SELECT estado_validacion, COUNT(*) AS c
                FROM usuarios
                GROUP BY estado_validacion
            ")
            ) {
                while ($row = $res->fetch_assoc()) {
                    $estado = strtolower((string) $row['estado_validacion']);
                    $c = (int) $row['c'];
                    $stats['total'] += $c;
                    if (isset($stats[$estado])) {
                        $stats[$estado] += $c;
                    } else {
                        $stats['otros'] += $c;
                    }
                }
                $res->close();
            }

            // Top de casos para trabajar (pendientes primero)
            if (
                $res = $db->query("
                SELECT 
                    id,
                    CONCAT(IFNULL(nombre,''),' ',IFNULL(apellido_paterno,'')) AS nombre,
                    correo,
                    Num_expediente,
                    estado_validacion,
                    ultima_actualizacion_datos,
                    created_at
                FROM usuarios
                ORDER BY 
                    FIELD(estado_validacion,'pendiente','rechazado','aprobado') ASC,
                    COALESCE(ultima_actualizacion_datos, created_at) DESC
                LIMIT 30
            ")
            ) {
                while ($row = $res->fetch_assoc()) {
                    $casos[] = [
                        'id' => (int) $row['id'],
                        'nombre' => trim((string) $row['nombre']) ?: 'Sin nombre',
                        'correo' => (string) ($row['correo'] ?? ''),
                        'exp' => (string) ($row['Num_expediente'] ?? ''),
                        'estado' => strtolower((string) ($row['estado_validacion'] ?? 'pendiente')),
                        'fecha' => (string) ($row['ultima_actualizacion_datos'] ?: $row['created_at'] ?: ''),
                    ];
                }
                $res->close();
            }
        }
    }
} catch (\Throwable $e) {
    // error_log($e->getMessage());
}

// Mock si no hay datos (para que la vista no quede vacía)
if (empty($casos)) {
    $stats['total'] = 3;
    $stats['pendiente'] = 2;
    $stats['aprobado'] = 1;
    $casos = [
        [
            'id' => 1,
            'nombre' => 'Usuario demo pendiente',
            'correo' => 'demo1@correo.com',
            'exp' => 'AXM-0001',
            'estado' => 'pendiente',
            'fecha' => date('Y-m-d 09:15:00', strtotime('-1 day')),
        ],
        [
            'id' => 2,
            'nombre' => 'Usuario demo aprobado',
            'correo' => 'demo2@correo.com',
            'exp' => 'AXM-0002',
            'estado' => 'aprobado',
            'fecha' => date('Y-m-d 11:30:00', strtotime('-3 days')),
        ],
        [
            'id' => 3,
            'nombre' => 'Usuario demo con observaciones',
            'correo' => 'demo3@correo.com',
            'exp' => 'AXM-0003',
            'estado' => 'rechazado',
            'fecha' => date('Y-m-d 16:45:00', strtotime('-2 days')),
        ],
    ];
}
?>
<style>
    /* ====== ADMIN CONSULTAS (SCOPED) ====== */
    .ac-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .ac-head {
        border-radius: 18px;
        padding: 18px 18px 16px;
        background: #111827;
        color: #F9FAFB;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .25);
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
    }

    .ac-head-main {
        max-width: 620px;
    }

    .ac-title {
        margin: 0;
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        font-weight: 900;
    }

    .ac-sub {
        margin: 6px 0 0;
        font-size: .9rem;
        color: #E5E7EB;
    }

    .ac-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .ac-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(31, 41, 55, .8);
        border: 1px solid rgba(249, 250, 251, .35);
        font-size: .78rem;
        font-weight: 800;
    }

    .ac-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 220px;
    }

    .ac-btn-main,
    .ac-btn-ghost {
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .ac-btn-main {
        background: #F9FAFB;
        color: #111827;
    }

    .ac-btn-ghost {
        background: rgba(17, 24, 39, .3);
        color: #F9FAFB;
        border: 1px solid rgba(249, 250, 251, .3);
    }

    .ac-btn-ghost:hover {
        background: rgba(17, 24, 39, .5);
    }

    .ac-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 2.1fr 1.3fr;
        gap: 16px;
    }

    @media (max-width: 980px) {
        .ac-grid {
            grid-template-columns: 1fr;
        }
    }

    .ac-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .06);
    }

    .ac-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .ac-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .ac-card-sub {
        margin: 0;
        font-size: .85rem;
        color: #6B7280;
    }

    .ac-chip {
        font-size: .75rem;
        padding: 4px 8px;
        border-radius: 999px;
        background: #EEF2FF;
        color: #3730A3;
        font-weight: 700;
    }

    /* MINI KPIs */
    .ac-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-top: 6px;
        margin-bottom: 10px;
    }

    @media (max-width: 700px) {
        .ac-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .ac-kpi {
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        padding: 8px;
    }

    .ac-kpi-label {
        font-size: .78rem;
        color: #6B7280;
        margin-bottom: 2px;
    }

    .ac-kpi-value {
        font-size: 1.1rem;
        font-weight: 900;
        color: #111827;
    }

    /* TABLA de casos */
    .ac-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        font-size: .86rem;
    }

    .ac-table thead {
        background: #F3F4F6;
    }

    .ac-table th,
    .ac-table td {
        padding: 7px 6px;
        border-bottom: 1px solid #E5E7EB;
        text-align: left;
    }

    .ac-table th {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #6B7280;
        font-weight: 700;
    }

    .ac-table tbody tr:nth-child(even) {
        background: #F9FAFB;
    }

    .ac-name {
        font-weight: 700;
        color: #111827;
    }

    .ac-mail {
        font-size: .78rem;
        color: #6B7280;
    }

    .ac-exp {
        font-size: .8rem;
        font-weight: 600;
        color: #111827;
    }

    .ac-date {
        font-size: .78rem;
        color: #9CA3AF;
        white-space: nowrap;
    }

    /* ESTADOS */
    .ac-pill-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 800;
    }

    .ac-pill-pend {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }

    .ac-pill-aprob {
        background: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .ac-pill-rech {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FCA5A5;
    }

    .ac-pill-otro {
        background: #E5E7EB;
        color: #374151;
        border: 1px solid #D1D5DB;
    }

    .ac-btn-row {
        border-radius: 999px;
        padding: 5px 10px;
        font-size: .76rem;
        font-weight: 700;
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        cursor: pointer;
    }

    .ac-btn-row:hover {
        background: #F3F4F6;
    }

    /* LADO DERECHO: “acciones rápidas” / pendientes */
    .ac-side-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
    }

    .ac-side-item {
        padding: 8px 9px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        font-size: .85rem;
        color: #374151;
    }

    .ac-side-item strong {
        color: #111827;
    }

    .ac-side-item span {
        font-size: .78rem;
        color: #6B7280;
    }

    .ac-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: acFade .4s ease .05s forwards;
    }

    .ac-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: acFade .4s ease .18s forwards;
    }

    @keyframes acFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="ac-wrap">
    <!-- ENCABEZADO -->
    <section class="ac-head ac-fade">
        <div class="ac-head-main">
            <h2 class="ac-title">Consultas y expedientes de usuarios</h2>
            <p class="ac-sub">
                Hola <?php echo htmlspecialchars($adminNombre); ?>. Aquí tienes una vista rápida de los expedientes
                de usuarios para revisar, aprobar o devolver con observaciones.
            </p>

            <div class="ac-badges">
                <span class="ac-pill">
                    Total usuarios: <strong><?php echo (int) $stats['total']; ?></strong>
                </span>
                <span class="ac-pill">
                    Pendientes: <strong><?php echo (int) $stats['pendiente']; ?></strong>
                </span>
                <span class="ac-pill">
                    Aprobados: <strong><?php echo (int) $stats['aprobado']; ?></strong>
                </span>
                <span class="ac-pill">
                    Observaciones / rechazados: <strong><?php echo (int) $stats['rechazados']; ?></strong>
                </span>
            </div>
        </div>

        <div class="ac-actions">
            <button type="button" class="ac-btn-main axm-nav-link" data-view="usuarios">
                Ir al módulo de usuarios
            </button>
            <button type="button" class="ac-btn-ghost axm-nav-link" data-view="casos">
                Ver detalle de casos
            </button>
            <button type="button" class="ac-btn-ghost axm-nav-link" data-view="historial">
                Ver historial general
            </button>
        </div>
    </section>

    <!-- GRID: tabla + lado derecho -->
    <section class="ac-grid">
        <!-- TABLA PRINCIPAL -->
        <article class="ac-card ac-fade2">
            <div class="ac-card-header">
                <div>
                    <h3 class="ac-card-title">Lista de casos</h3>
                    <p class="ac-card-sub">
                        Expedientes ordenados por prioridad (pendientes primero).
                    </p>
                </div>
                <span class="ac-chip">
                    Mostrando <?php echo count($casos); ?> registros
                </span>
            </div>

            <div class="ac-kpi-grid">
                <div class="ac-kpi">
                    <div class="ac-kpi-label">Pendientes</div>
                    <div class="ac-kpi-value"><?php echo (int) $stats['pendiente']; ?></div>
                </div>
                <div class="ac-kpi">
                    <div class="ac-kpi-label">Aprobados</div>
                    <div class="ac-kpi-value"><?php echo (int) $stats['aprobado']; ?></div>
                </div>
                <div class="ac-kpi">
                    <div class="ac-kpi-label">Con observaciones</div>
                    <div class="ac-kpi-value"><?php echo (int) $stats['rechazados']; ?></div>
                </div>
                <div class="ac-kpi">
                    <div class="ac-kpi-label">Otros</div>
                    <div class="ac-kpi-value"><?php echo (int) $stats['otros']; ?></div>
                </div>
            </div>

            <table class="ac-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Expediente</th>
                        <th>Estado</th>
                        <th>Último movimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($casos as $c): ?>
                        <?php
                        // Formateo estado
                        $estadoRaw = $c['estado'] ?: 'pendiente';
                        $estadoLabel = match ($estadoRaw) {
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Observaciones / Rechazado',
                            'pendiente' => 'Pendiente',
                            default => ucfirst($estadoRaw),
                        };
                        $estadoClass = match ($estadoRaw) {
                            'aprobado' => 'ac-pill-aprob',
                            'rechazado' => 'ac-pill-rech',
                            'pendiente' => 'ac-pill-pend',
                            default => 'ac-pill-otro',
                        };

                        // Fecha
                        $fechaFmt = '';
                        if (!empty($c['fecha'])) {
                            $ts = strtotime($c['fecha']);
                            if ($ts)
                                $fechaFmt = date('d/m/Y H:i', $ts);
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="ac-name"><?php echo htmlspecialchars($c['nombre']); ?></div>
                                <?php if (!empty($c['correo'])): ?>
                                    <div class="ac-mail"><?php echo htmlspecialchars($c['correo']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($c['exp'])): ?>
                                    <span class="ac-exp"><?php echo htmlspecialchars($c['exp']); ?></span>
                                <?php else: ?>
                                    <span class="ac-exp" style="color:#9CA3AF">Sin expediente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ac-pill-estado <?php echo $estadoClass; ?>">
                                    <?php echo htmlspecialchars($estadoLabel); ?>
                                </span>
                            </td>
                            <td>
                                <div class="ac-date">
                                    <?php echo $fechaFmt !== '' ? htmlspecialchars($fechaFmt) : '—'; ?>
                                </div>
                            </td>
                            <td>
                                <!-- Estos botones sólo cambian de vista por ahora; luego puedes enlazar a un detalle concreto -->
                                <button type="button" class="ac-btn-row axm-nav-link" data-view="casos"
                                    data-anchor="u-<?php echo (int) $c['id']; ?>">
                                    Ver detalle
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <!-- LADO DERECHO -->
        <aside class="ac-card ac-fade2">
            <div class="ac-card-header">
                <div>
                    <h3 class="ac-card-title">Siguiente acciones sugeridas</h3>
                    <p class="ac-card-sub">
                        Una guía rápida para priorizar tu revisión.
                    </p>
                </div>
            </div>

            <div class="ac-side-list">
                <div class="ac-side-item">
                    <strong>Revisar expedientes pendientes</strong><br>
                    <span>
                        Tienes <?php echo (int) $stats['pendiente']; ?> usuarios pendientes de validación.
                        Empieza por ellos para destrabar su trámite.
                    </span>
                </div>
                <div class="ac-side-item">
                    <strong>Atender observaciones / rechazos</strong><br>
                    <span>
                        Hay <?php echo (int) $stats['rechazados']; ?> casos con observaciones.
                        Verifica si el usuario ya subió nueva documentación.
                    </span>
                </div>
                <div class="ac-side-item">
                    <strong>Monitorear aprobados recientes</strong><br>
                    <span>
                        Lleva seguimiento de los <?php echo (int) $stats['aprobado']; ?> expedientes aprobados,
                        para confirmar que se concrete el beneficio.
                    </span>
                </div>
                <div class="ac-side-item">
                    <strong>Ir al módulo de usuarios</strong><br>
                    <span>
                        Desde <em>Usuarios</em> puedes filtrar, editar datos básicos
                        y ver el historial completo de cada persona.
                    </span>
                    <div style="margin-top:8px">
                        <button type="button" class="ac-btn-row axm-nav-link" data-view="usuarios">
                            Abrir módulo de usuarios
                        </button>
                    </div>
                </div>
            </div>
        </aside>
    </section>
</div>