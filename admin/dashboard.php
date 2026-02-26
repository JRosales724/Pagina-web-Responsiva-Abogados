<?php
// AXM/admin/dashboard.php
declare(strict_types=1);

// Nombre del admin desde sesión
$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));

// Métricas por defecto (vals "maqueta" si no hay BD)
$stats = [
    'totalUsuarios' => 0,
    'pendientes' => 0,
    'aprobados' => 0,
    'rechazados' => 0,
    'citasHoy' => 0,
    'citasPendientes' => 0,
];

$ultimosUsuarios = [];   // [['nombre'=>'','correo'=>'','fecha'=>''], ...]
$ultimosMovs = [];    // [['tipo'=>'','detalle'=>'','fecha'=>''], ...]

// Intentar leer datos reales si existe Conexion y tablas
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
            // Total usuarios
            if ($res = $db->query("SELECT COUNT(*) AS total FROM usuarios")) {
                if ($row = $res->fetch_assoc()) {
                    $stats['totalUsuarios'] = (int) $row['total'];
                }
                $res->close();
            }

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
                    if ($estado === 'pendiente')
                        $stats['pendientes'] += $c;
                    if ($estado === 'aprobado')
                        $stats['aprobados'] += $c;
                    if ($estado === 'rechazado')
                        $stats['rechazados'] += $c;
                }
                $res->close();
            }

            // Últimos usuarios registrados / actualizados
            if (
                $res = $db->query("
                SELECT 
                    id,
                    CONCAT(IFNULL(nombre,''), ' ', IFNULL(apellido_paterno,'')) AS nombre,
                    correo,
                    created_at,
                    ultima_actualizacion_datos
                FROM usuarios
                ORDER BY COALESCE(ultima_actualizacion_datos, created_at) DESC
                LIMIT 6
            ")
            ) {
                while ($row = $res->fetch_assoc()) {
                    $nombreU = trim((string) $row['nombre']);
                    if ($nombreU === '')
                        $nombreU = 'Sin nombre';
                    $fechaRef = $row['ultima_actualizacion_datos'] ?: $row['created_at'];
                    $ultimosUsuarios[] = [
                        'nombre' => $nombreU,
                        'correo' => (string) ($row['correo'] ?? ''),
                        'fecha' => (string) $fechaRef,
                    ];
                }
                $res->close();
            }
        }

        // ¿Existe tabla citas?
        $tieneCitas = false;
        if ($res = $db->query("SHOW TABLES LIKE 'citas'")) {
            $tieneCitas = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneCitas) {
            date_default_timezone_set('America/Mexico_City');
            $hoy = date('Y-m-d');

            // Citas de hoy
            if (
                $stmt = $db->prepare("
                SELECT COUNT(*) AS total 
                FROM citas 
                WHERE fecha = ? AND estado IN ('agendada','reprogramada')
            ")
            ) {
                $stmt->bind_param('s', $hoy);
                if ($stmt->execute()) {
                    if ($res = $stmt->get_result()) {
                        if ($row = $res->fetch_assoc()) {
                            $stats['citasHoy'] = (int) $row['total'];
                        }
                        $res->close();
                    }
                }
                $stmt->close();
            }

            // Citas pendientes próximas (hoy en adelante)
            if (
                $stmt = $db->prepare("
                SELECT COUNT(*) AS total 
                FROM citas 
                WHERE fecha >= ? AND estado IN ('agendada','reprogramada')
            ")
            ) {
                $stmt->bind_param('s', $hoy);
                if ($stmt->execute()) {
                    if ($res = $stmt->get_result()) {
                        if ($row = $res->fetch_assoc()) {
                            $stats['citasPendientes'] = (int) $row['total'];
                        }
                        $res->close();
                    }
                }
                $stmt->close();
            }

            // Últimos movimientos (simple ejemplo tomando citas recientes)
            if (
                $res = $db->query("
                SELECT c.id, c.fecha, c.hora, c.estado,
                       CONCAT(IFNULL(u.nombre,''),' ',IFNULL(u.apellido_paterno,'')) AS usuario
                FROM citas c
                LEFT JOIN usuarios u ON u.id = c.id_usuario
                ORDER BY CONCAT(c.fecha,' ',IFNULL(c.hora,'00:00')) DESC
                LIMIT 6
            ")
            ) {
                while ($row = $res->fetch_assoc()) {
                    $usuario = trim((string) $row['usuario']);
                    if ($usuario === '')
                        $usuario = 'Usuario sin nombre';

                    $detalle = sprintf(
                        'Cita %s con %s',
                        (string) ($row['estado'] ?? 'sin-estado'),
                        $usuario
                    );

                    $fechaRef = trim((string) $row['fecha'] . ' ' . ($row['hora'] ?? ''));
                    $ultimosMovs[] = [
                        'tipo' => 'cita',
                        'detalle' => $detalle,
                        'fecha' => $fechaRef,
                    ];
                }
                $res->close();
            }
        }

        // ¿Existe tabla estatus_historial? para más movimientos
        $tieneHist = false;
        if ($res = $db->query("SHOW TABLES LIKE 'estatus_historial'")) {
            $tieneHist = (bool) $res->fetch_row();
            $res->close();
        }

        if ($tieneHist) {
            if (
                $res = $db->query("
                SELECT h.estado, h.nota, h.fecha,
                       CONCAT(IFNULL(u.nombre,''),' ',IFNULL(u.apellido_paterno,'')) AS usuario
                FROM estatus_historial h
                LEFT JOIN usuarios u ON u.id = h.id_usuario
                ORDER BY h.fecha DESC, h.id DESC
                LIMIT 6
            ")
            ) {
                while ($row = $res->fetch_assoc()) {
                    $usuario = trim((string) $row['usuario']);
                    if ($usuario === '')
                        $usuario = 'Usuario sin nombre';

                    $estado = (string) $row['estado'];
                    $nota = trim((string) ($row['nota'] ?? ''));

                    $detalle = "Cambio de estado a '{$estado}' para {$usuario}";
                    if ($nota !== '') {
                        $detalle .= " · {$nota}";
                    }

                    $ultimosMovs[] = [
                        'tipo' => 'estatus',
                        'detalle' => $detalle,
                        'fecha' => (string) $row['fecha'],
                    ];
                }
                $res->close();
            }
        }
    }
} catch (\Throwable $e) {
    // En desarrollo podrías loguear: error_log($e->getMessage());
    // Pero no rompemos el dashboard.
}

// Si no hay datos en listas, rellenamos un pequeño “mock” amigable
if (empty($ultimosUsuarios)) {
    $ultimosUsuarios = [
        ['nombre' => 'Usuario demo 1', 'correo' => 'demo1@correo.com', 'fecha' => date('Y-m-d 09:00:00', strtotime('-2 days'))],
        ['nombre' => 'Usuario demo 2', 'correo' => 'demo2@correo.com', 'fecha' => date('Y-m-d 11:30:00', strtotime('-1 days'))],
    ];
}
if (empty($ultimosMovs)) {
    $ultimosMovs = [
        ['tipo' => 'estatus', 'detalle' => 'Expediente de usuario demo marcado como "En revisión"', 'fecha' => date('Y-m-d 10:00:00', strtotime('-1 hours'))],
        ['tipo' => 'cita', 'detalle' => 'Nueva cita agendada por usuario demo', 'fecha' => date('Y-m-d 12:15:00')],
    ];
}
?>
<style>
    /* ====== ADMIN DASHBOARD (SCOPED) ====== */
    .adm-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .adm-hero {
        position: relative;
        border-radius: 18px;
        padding: 22px 20px;
        background: linear-gradient(135deg, #111827 0%, #1F2937 40%, #8A1538 100%);
        color: #F9FAFB;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .25);
        overflow: hidden;
    }

    .adm-hero::after {
        content: "";
        position: absolute;
        inset: -30% -10% auto auto;
        background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, .12), transparent 60%);
        opacity: .9;
    }

    .adm-hero-title {
        position: relative;
        margin: 0;
        font-weight: 900;
        font-size: clamp(1.4rem, 3.8vw, 2rem);
        letter-spacing: .3px;
    }

    .adm-hero-sub {
        position: relative;
        margin-top: 6px;
        color: #E5E7EB;
        max-width: 640px;
        font-size: .95rem;
    }

    .adm-hero-badges {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .adm-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        border: 1px solid rgba(255, 255, 255, .35);
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(6px);
    }

    .adm-hero-actions {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .adm-btn-main {
        background: #F9FAFB;
        color: #111827;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: .9rem;
        font-weight: 900;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .adm-btn-ghost {
        background: rgba(15, 23, 42, .5);
        color: #F9FAFB;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: .9rem;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid rgba(249, 250, 251, .28);
        cursor: pointer;
    }

    .adm-btn-ghost:hover {
        background: rgba(15, 23, 42, .75);
    }

    .adm-grid-main {
        display: grid;
        grid-template-columns: 2.1fr 1.4fr;
        gap: 18px;
        margin-top: 18px;
    }

    @media (max-width: 980px) {
        .adm-grid-main {
            grid-template-columns: 1fr;
        }
    }

    .adm-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 28px rgba(0, 0, 0, .06);
    }

    .adm-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .adm-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .adm-card-sub {
        margin: 0;
        color: #6B7280;
        font-size: .85rem;
    }

    .adm-chip {
        font-size: .75rem;
        padding: 4px 8px;
        border-radius: 999px;
        background: #EEF2FF;
        color: #3730A3;
        font-weight: 700;
    }

    /* KPIs */
    .adm-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    @media (max-width: 900px) {
        .adm-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .adm-kpi-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .adm-kpi {
        border-radius: 12px;
        padding: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
    }

    .adm-kpi-label {
        font-size: .78rem;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .adm-kpi-value {
        font-size: 1.3rem;
        font-weight: 900;
        color: #111827;
    }

    .adm-kpi-foot {
        margin-top: 2px;
        font-size: .78rem;
        color: #9CA3AF;
    }

    .adm-kpi-foot strong {
        font-weight: 700;
        color: #16A34A;
    }

    .adm-kpi-foot.bad strong {
        color: #DC2626;
    }

    /* Lista de usuarios */
    .adm-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 6px;
    }

    .adm-list-item {
        display: grid;
        grid-template-columns: 1.4fr 1.2fr auto;
        gap: 8px;
        align-items: center;
        padding: 8px 8px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
    }

    @media (max-width: 700px) {
        .adm-list-item {
            grid-template-columns: 1.3fr 1.4fr;
            grid-template-rows: auto auto;
        }
    }

    .adm-list-name {
        font-size: .9rem;
        font-weight: 700;
        color: #111827;
    }

    .adm-list-mail {
        font-size: .8rem;
        color: #6B7280;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .adm-list-date {
        font-size: .8rem;
        color: #9CA3AF;
        text-align: right;
    }

    /* Últimos movimientos */
    .adm-mov-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 6px;
    }

    .adm-mov-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 8px;
        align-items: center;
        padding: 8px;
        border-radius: 10px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
    }

    .adm-mov-badge {
        font-size: .75rem;
        padding: 4px 9px;
        border-radius: 999px;
        font-weight: 700;
    }

    .adm-mov-badge.tipo-cita {
        background: #ECFEFF;
        color: #0E7490;
        border: 1px solid #A5F3FC;
    }

    .adm-mov-badge.tipo-estatus {
        background: #EEF2FF;
        color: #3730A3;
        border: 1px solid #C7D2FE;
    }

    .adm-mov-detail {
        font-size: .85rem;
        color: #374151;
    }

    .adm-mov-date {
        font-size: .78rem;
        color: #9CA3AF;
        text-align: right;
    }

    /* Animaciones suaves */
    .adm-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: admFade .4s ease .05s forwards;
    }

    .adm-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: admFade .4s ease .18s forwards;
    }

    .adm-fade3 {
        opacity: 0;
        transform: translateY(8px);
        animation: admFade .4s ease .3s forwards;
    }

    @keyframes admFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="adm-wrap">
    <!-- HERO -->
    <section class="adm-hero adm-fade">
        <h2 class="adm-hero-title">
            Panel de administración · Hola <?php echo htmlspecialchars($adminNombre); ?> 👋
        </h2>
        <p class="adm-hero-sub">
            Aquí puedes monitorear los expedientes, usuarios y citas del despacho.
            Usa este panel como vista rápida para priorizar qué atender primero.
        </p>

        <div class="adm-hero-badges">
            <span class="adm-pill">
                👥 Usuarios: <strong><?php echo (int) $stats['totalUsuarios']; ?></strong>
            </span>
            <span class="adm-pill">
                ⏳ Pendientes: <strong><?php echo (int) $stats['pendientes']; ?></strong>
            </span>
            <span class="adm-pill">
                📅 Citas hoy: <strong><?php echo (int) $stats['citasHoy']; ?></strong>
            </span>
        </div>

        <div class="adm-hero-actions">
            <a class="adm-btn-main axm-nav-link" href="/AXM/" data-view="consultas">Ver consultas</a>
            <a class="adm-btn-ghost axm-nav-link" href="/AXM/" data-view="usuarios">Gestión de usuarios</a>
            <a class="adm-btn-ghost axm-nav-link" href="/AXM/" data-view="casos">Casos y expedientes</a>
        </div>
    </section>

    <!-- GRID PRINCIPAL -->
    <section class="adm-grid-main">
        <!-- Columna izquierda -->
        <div class="adm-fade2">
            <!-- KPI EXPEDIENTES -->
            <article class="adm-card" style="margin-bottom:12px">
                <div class="adm-card-header">
                    <div>
                        <h3 class="adm-card-title">Resumen de expedientes</h3>
                        <p class="adm-card-sub">Estado global de los usuarios en el sistema.</p>
                    </div>
                    <span class="adm-chip">Hoy</span>
                </div>

                <div class="adm-kpi-grid">
                    <div class="adm-kpi">
                        <div class="adm-kpi-label">Total usuarios</div>
                        <div class="adm-kpi-value"><?php echo (int) $stats['totalUsuarios']; ?></div>
                        <div class="adm-kpi-foot">Registro general</div>
                    </div>
                    <div class="adm-kpi">
                        <div class="adm-kpi-label">Pendientes</div>
                        <div class="adm-kpi-value"><?php echo (int) $stats['pendientes']; ?></div>
                        <div class="adm-kpi-foot bad">
                            <strong><?php echo $stats['totalUsuarios'] > 0 ? round($stats['pendientes'] / max(1, $stats['totalUsuarios']) * 100) : 0; ?>%</strong>
                            en espera
                        </div>
                    </div>
                    <div class="adm-kpi">
                        <div class="adm-kpi-label">Aprobados</div>
                        <div class="adm-kpi-value"><?php echo (int) $stats['aprobados']; ?></div>
                        <div class="adm-kpi-foot">
                            <strong><?php echo $stats['totalUsuarios'] > 0 ? round($stats['aprobados'] / max(1, $stats['totalUsuarios']) * 100) : 0; ?>%</strong>
                            resueltos
                        </div>
                    </div>
                    <div class="adm-kpi">
                        <div class="adm-kpi-label">Rechazados / observaciones</div>
                        <div class="adm-kpi-value"><?php echo (int) $stats['rechazados']; ?></div>
                        <div class="adm-kpi-foot bad">
                            Requieren revisión
                        </div>
                    </div>
                </div>
            </article>

            <!-- LISTA DE USUARIOS -->
            <article class="adm-card">
                <div class="adm-card-header">
                    <div>
                        <h3 class="adm-card-title">Últimos usuarios actualizados</h3>
                        <p class="adm-card-sub">Cambios recientes en expedientes.</p>
                    </div>
                    <button type="button" class="adm-btn-ghost axm-nav-link" data-view="usuarios">
                        Ver todos
                    </button>
                </div>

                <div class="adm-list">
                    <?php foreach ($ultimosUsuarios as $u): ?>
                            <?php
                            $fechaFmt = '';
                            if (!empty($u['fecha'])) {
                                $ts = strtotime($u['fecha']);
                                if ($ts)
                                    $fechaFmt = date('d/m/Y H:i', $ts);
                            }
                            ?>
                            <div class="adm-list-item">
                                <div class="adm-list-name">
                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                </div>
                                <div class="adm-list-mail">
                                    <?php echo htmlspecialchars($u['correo']); ?>
                                </div>
                                <div class="adm-list-date">
                                    <?php echo htmlspecialchars($fechaFmt); ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>

        <!-- Columna derecha -->
        <div class="adm-fade3">
            <!-- Citas -->
            <article class="adm-card" style="margin-bottom:12px">
                <div class="adm-card-header">
                    <div>
                        <h3 class="adm-card-title">Citas y agenda</h3>
                        <p class="adm-card-sub">
                            Resumen rápido de tu carga de trabajo.
                        </p>
                    </div>
                    <span class="adm-chip">
                        Hoy: <?php echo (int) $stats['citasHoy']; ?> · Pendientes: <?php echo (int) $stats['citasPendientes']; ?>
                    </span>
                </div>

                <p class="adm-card-sub" style="margin-bottom:8px">
                    Puedes gestionar y reagendar desde la sección de <strong>Consultas</strong> o
                    directamente en el módulo de <strong>Historial</strong>.
                </p>

                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px">
                    <button type="button" class="adm-btn-main axm-nav-link" data-view="consultas">
                        Ver consultas activas
                    </button>
                    <button type="button" class="adm-btn-ghost axm-nav-link" data-view="historial">
                        Ver historial de citas
                    </button>
                </div>
            </article>

            <!-- Últimos movimientos -->
            <article class="adm-card">
                <div class="adm-card-header">
                    <div>
                        <h3 class="adm-card-title">Últimos movimientos</h3>
                        <p class="adm-card-sub">Acciones recientes sobre citas y estatus.</p>
                    </div>
                </div>

                <div class="adm-mov-grid">
                    <?php foreach ($ultimosMovs as $m): ?>
                            <?php
                            $tipo = strtolower((string) $m['tipo']);
                            $badgeClass = $tipo === 'cita' ? 'tipo-cita' : 'tipo-estatus';
                            $badgeText = $tipo === 'cita' ? 'Cita' : 'Estatus';
                            $fechaFmt = '';
                            if (!empty($m['fecha'])) {
                                $ts = strtotime($m['fecha']);
                                if ($ts)
                                    $fechaFmt = date('d/m/Y H:i', $ts);
                            }
                            ?>
                            <div class="adm-mov-item">
                                <span class="adm-mov-badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($badgeText); ?>
                                </span>
                                <div class="adm-mov-detail">
                                    <?php echo htmlspecialchars($m['detalle']); ?>
                                </div>
                                <div class="adm-mov-date">
                                    <?php echo htmlspecialchars($fechaFmt); ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>
    </section>
</div>
