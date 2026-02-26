<?php
// AXM/usuario/inicio.php
declare(strict_types=1);

$nombre = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? ''));
if ($nombre === '') {
    $nombre = 'Bienvenido/a';
}

// Datos por defecto
$estadoExpediente = 'pendiente';
$porcentaje       = 35;   // fallback visual
$proximaCita      = null; // ['fecha'=>'','hora'=>'','estado'=>'','id'=>]
$citaEstado       = 'sin-cita'; // proxima|hoy|vencida|sin-cita
$ctaTexto         = 'Agendar cita';
$ctaVista         = 'consultas';
$ctaAnchor        = '';   // para data-anchor opcional

// Intento de lectura real si existe Conexion y tabla usuarios/citas
try {
    if (!class_exists('Conexion')) {
        @require_once __DIR__ . '/../class/class.conexion.php';
    }
    if (class_exists('Conexion')) {
        $bd = new Conexion();

        // 1) Estado del expediente
        if (!empty($_SESSION['ID'])) {
            $uid = (int) $_SESSION['ID'];
            if ($stmt = $bd->prepare("
                SELECT estado_validacion, Num_expediente, ultima_actualizacion_datos
                FROM usuarios
                WHERE id = ?
                LIMIT 1
            ")) {
                $stmt->bind_param('i', $uid);
                if ($stmt->execute()) {
                    $res = $stmt->get_result()->fetch_assoc();
                    if ($res) {
                        $estadoExpediente = $res['estado_validacion'] ?: $estadoExpediente;
                        $porcentaje = match ($estadoExpediente) {
                            'aprobado'  => 100,
                            'rechazado' => 50,
                            'pendiente' => 35,
                            default     => 45
                        };
                        $numExpediente = $res['Num_expediente'] ?? null;
                        $ultimaAct     = $res['ultima_actualizacion_datos'] ?? null;
                    }
                }
                $stmt->close();
            }
        }

        // 2) Próxima cita (ajusta si tu tabla se llama distinto)
        if (!empty($_SESSION['ID'])) {
            $uid = (int) $_SESSION['ID'];
            if ($stmt = $bd->prepare("
                SELECT id, fecha, hora, estado
                FROM citas
                WHERE id_usuario = ? AND estado IN ('agendada','reprogramada')
                ORDER BY CONCAT(fecha,' ',IFNULL(hora,'00:00')) ASC
                LIMIT 1
            ")) {
                $stmt->bind_param('i', $uid);
                if ($stmt->execute()) {
                    $c = $stmt->get_result()->fetch_assoc();
                    if ($c) {
                        $proximaCita = [
                            'fecha'  => $c['fecha'] ?? null,
                            'hora'   => $c['hora'] ?? null,
                            'estado' => $c['estado'] ?? null,
                            'id'     => (int) $c['id'],
                        ];
                    }
                }
                $stmt->close();
            }
        }
    }
} catch (\Throwable $e) {
    // Silencioso: no rompemos el dashboard si no hay tablas aún.
}

// Calcular estado de la cita
date_default_timezone_set('America/Mexico_City');
if (!empty($proximaCita['fecha'])) {
    $h      = $proximaCita['hora'] ?: '00:00';
    $tsCita = strtotime($proximaCita['fecha'] . ' ' . $h);
    $hoy0   = strtotime(date('Y-m-d') . ' 00:00');
    $man0   = strtotime('+1 day', $hoy0);

    if ($tsCita < $hoy0) {
        $citaEstado = 'vencida';
        $ctaTexto   = 'Reagendar cita';
        $ctaVista   = 'consultas';
        $ctaAnchor  = 'reagendar';
    } elseif ($tsCita >= $hoy0 && $tsCita < $man0) {
        $citaEstado = 'hoy';
        $ctaTexto   = 'Ver detalles de hoy';
        $ctaVista   = 'consultas';
        $ctaAnchor  = 'hoy';
    } else {
        $citaEstado = 'proxima';
        $ctaTexto   = 'Ver detalles';
        $ctaVista   = 'consultas';
        $ctaAnchor  = 'proxima';
    }
}
?>
<style>
    /* ===== Dashboard (scoped) ===== */
    .dash-wrap{padding:24px;max-width:1100px;margin:0 auto}
    .dash-hero{position:relative;border-radius:18px;overflow:hidden;background:linear-gradient(135deg,#8A1538 0%,#6E102C 50%,#3A3A3A 100%);color:#fff;padding:28px 26px;box-shadow:0 18px 38px rgba(0,0,0,.18)}
    .dash-hero::after{content:"";position:absolute;inset:-40% -20% auto auto;width:60%;height:180%;background:radial-gradient(closest-side,rgba(255,255,255,.12),transparent 60%);transform:rotate(18deg)}
    .dash-hello{font-weight:900;letter-spacing:.3px;font-size:clamp(1.3rem,3.6vw,2rem)}
    .dash-sub{color:#fff;opacity:.92;margin-top:6px;max-width:720px}
    .dash-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
    .btn-ghost{background:rgba(255,255,255,.12);color:#fff;padding:10px 14px;border-radius:12px;font-weight:800;border:1px solid rgba(255,255,255,.2)}
    .btn-ghost:hover{background:rgba(255,255,255,.18)}
    .btn-white{background:#fff;color:#111;padding:10px 14px;border-radius:12px;font-weight:900}
    .dash-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px;margin-top:18px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px;box-shadow:0 10px 26px rgba(0,0,0,.06)}
    .span-8{grid-column:span 8}
    .span-4{grid-column:span 4}
    @media (max-width:900px){.span-8,.span-4{grid-column:span 12}}
    .kpi{display:flex;align-items:center;gap:12px}
    .kpi .dot{width:12px;height:12px;border-radius:999px}
    .kpi h4{margin:0;font-size:1.05rem;font-weight:800;color:#111}
    .muted{color:#6b7280;font-size:.92rem}
    .status-pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;font-weight:800;font-size:.85rem}
    .pill-pend{background:#fff3cd;color:#7a5d00;border:1px solid #ffe08a}
    .pill-aprob{background:#e7f8ee;color:#0a6847;border:1px solid #b9e6cf}
    .pill-rech{background:#fde2e1;color:#8a1c1c;border:1px solid #f5b5b3}
    .ring{--p:<?php echo (int) $porcentaje; ?>;width:86px;height:86px;border-radius:50%;background:conic-gradient(#8A1538 var(--p)*1%,#e5e7eb 0);position:relative}
    .ring::after{content:attr(data-pct) '%';position:absolute;inset:8px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;color:#8A1538}
    .list{display:flex;flex-direction:column;gap:10px}
    .li{display:flex;justify-content:space-between;align-items:center;padding:12px;border:1px solid #eef1f5;border-radius:10px;background:#fafbfc}
    .li strong{font-weight:800;color:#111}
    .badge{font-size:.78rem;padding:6px 10px;border-radius:999px;background:#eef2ff;color:#3730a3;font-weight:800}
    .tag{font-size:.75rem;padding:4px 8px;border-radius:8px;background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0}
    .fade{opacity:0;transform:translateY(10px);animation:fadeIn .55s ease .05s forwards}
    .fade2{opacity:0;transform:translateY(10px);animation:fadeIn .55s ease .18s forwards}
    .fade3{opacity:0;transform:translateY(10px);animation:fadeIn .55s ease .3s forwards}
    @keyframes fadeIn{to{opacity:1;transform:none}}
    .pulse{animation:pulse 1.8s ease-in-out infinite}
    @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.03)}}
    .confetti{position:absolute;inset:0;pointer-events:none}
    .confetti span{position:absolute;width:6px;height:6px;border-radius:2px;opacity:.85;animation:drop 2.2s linear infinite}
    @keyframes drop{0%{transform:translateY(-10%) rotate(0)}100%{transform:translateY(120%) rotate(360deg)}}
</style>

<div class="dash-wrap">
    <!-- HERO -->
    <section class="dash-hero fade">
        <div class="confetti" aria-hidden="true">
            <?php
            $seed = (int) ($_SESSION['ID'] ?? 1);
            mt_srand($seed);
            for ($i = 0; $i < 18; $i++) {
                $l = mt_rand(2, 98);
                $d = mt_rand(0, 120) / 10;
                $c = ['#fff','#fcd34d','#fca5a5','#93c5fd','#86efac'][mt_rand(0, 4)];
                echo "<span style='left:{$l}%;background:{$c};animation-delay:{$d}s'></span>";
            }
            ?>
        </div>
        <h2 class="dash-hello">
            ¡Hola, <?php echo htmlspecialchars($nombre); ?>! <span class="pulse">👋</span>
        </h2>
        <p class="dash-sub">
            Este es tu panel personal. Aquí puedes revisar el estado de tu trámite, tus citas
            y subir la documentación que haga falta.
        </p>
        <div class="dash-actions">
            <a href="#"
               class="btn-white axm-nav-link"
               data-view="consultas">Consultar estatus</a>

            <a href="#"
               class="btn-ghost axm-nav-link"
               data-view="complementar">Subir/Completar datos</a>

            <a href="#"
               class="btn-ghost axm-nav-link"
               data-view="historial">Ver historial</a>

            <a href="#"
               class="btn-ghost axm-nav-link"
               data-view="notificaciones">Notificaciones</a>
        </div>
    </section>

    <!-- GRID -->
    <section class="dash-grid">
        <!-- Estado del expediente -->
        <div class="card span-8 fade2">
            <div style="display:flex;gap:16px;align-items:center;justify-content:space-between;flex-wrap:wrap">
                <div class="kpi">
                    <div class="dot" style="background:#8A1538"></div>
                    <div>
                        <h4>Estado de tu expediente</h4>
                        <div class="muted">
                            <?php if (!empty($numExpediente)): ?>
                                Expediente <strong><?php echo htmlspecialchars($numExpediente); ?></strong>
                                <?php if (!empty($ultimaAct)): ?>
                                    · Última actualización:
                                    <strong><?php echo date('d/m/Y H:i', strtotime($ultimaAct)); ?></strong>
                                <?php endif; ?>
                            <?php else: ?>
                                Revisa y completa tus datos para acelerar la validación.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php
                $pillClass = match ($estadoExpediente) {
                    'aprobado'  => 'status-pill pill-aprob',
                    'rechazado' => 'status-pill pill-rech',
                    default     => 'status-pill pill-pend'
                };
                $pillText = ucfirst($estadoExpediente);
                ?>
                <span class="<?php echo $pillClass; ?>">
                    <?php echo htmlspecialchars($pillText); ?>
                </span>
            </div>

            <div style="display:flex;gap:18px;align-items:center;margin-top:18px;flex-wrap:wrap">
                <div class="ring" data-pct="<?php echo (int) $porcentaje; ?>"></div>
                <div style="flex:1;min-width:260px">
                    <div class="muted" style="margin-bottom:6px">Siguiente paso sugerido</div>
                    <div class="list">
                        <?php if ($estadoExpediente === 'pendiente'): ?>
                            <div class="li">
                                <div>
                                    <strong>Completa tu información</strong>
                                    <div class="muted">Sube documentos clave para validar tu caso.</div>
                                </div>
                                <a href="#"
                                   class="badge axm-nav-link"
                                   data-view="complementar">Completar</a>
                            </div>
                        <?php elseif ($estadoExpediente === 'aprobado'): ?>
                            <div class="li">
                                <div>
                                    <strong>Seguimiento</strong>
                                    <div class="muted">Tu expediente fue aprobado. Revisa notificaciones.</div>
                                </div>
                                <a href="#"
                                   class="badge axm-nav-link"
                                   data-view="notificaciones">Ver</a>
                            </div>
                        <?php elseif ($estadoExpediente === 'rechazado'): ?>
                            <div class="li">
                                <div>
                                    <strong>Revisión de observaciones</strong>
                                    <div class="muted">Atiende los comentarios del abogado.</div>
                                </div>
                                <a href="#"
                                   class="badge axm-nav-link"
                                   data-view="consultas"
                                   data-anchor="observaciones">Abrir</a>
                            </div>
                        <?php else: ?>
                            <div class="li">
                                <div>
                                    <strong>Consulta tu estatus</strong>
                                    <div class="muted">Verifica el progreso de tu trámite.</div>
                                </div>
                                <a href="#"
                                   class="badge axm-nav-link"
                                   data-view="consultas">Estatus</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próxima cita -->
        <div class="card span-4 fade3">
            <div class="kpi" style="margin-bottom:10px">
                <div class="dot" style="background:#10b981"></div>
                <h4>Tu cita</h4>
            </div>

            <?php if ($proximaCita): ?>
                <div class="li" style="background:#f0fdf4;border-color:#bbf7d0">
                    <div>
                        <strong>
                            <?php
                            $f = date('d/m/Y', strtotime($proximaCita['fecha']));
                            $h = $proximaCita['hora']
                                ? date('H:i', strtotime($proximaCita['hora']))
                                : 'Por confirmar';
                            echo $f . ' · ' . $h;
                            ?>
                        </strong>
                        <div class="muted">
                            Estado:
                            <span class="tag">
                                <?php echo htmlspecialchars($proximaCita['estado']); ?>
                            </span>
                            <?php if ($citaEstado === 'hoy'): ?>
                                <span class="tag" style="background:#dcfce7;border-color:#bbf7d0">Hoy</span>
                            <?php elseif ($citaEstado === 'vencida'): ?>
                                <span class="tag" style="background:#fee2e2;border-color:#fecaca">Vencida</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="#"
                       class="badge axm-nav-link"
                       data-view="<?php echo htmlspecialchars($ctaVista); ?>"
                       <?php if ($ctaAnchor): ?>
                           data-anchor="<?php echo htmlspecialchars($ctaAnchor); ?>"
                       <?php endif; ?>
                    >
                        <?php echo htmlspecialchars($ctaTexto); ?>
                    </a>
                </div>
            <?php else: ?>
                <p class="muted" style="margin:8px 0 12px">
                    No tienes una cita programada.
                </p>
                <a href="#"
                   class="btn-white axm-nav-link"
                   data-view="consultas"
                   data-anchor="agendar">Agendar ahora</a>
            <?php endif; ?>

            <div style="margin-top:14px" class="muted">
                ¿Necesitas cambiar fecha u hora? Puedes reagendar desde
                <a href="#"
                   class="axm-nav-link"
                   data-view="consultas"
                   data-anchor="reagendar"
                   style="font-weight:700;color:#8A1538">Consultas</a>.
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="card span-12 fade3"
             style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
            <a href="#"
               class="li axm-nav-link"
               data-view="consultas">
                <div>
                    <strong>Estatus del trámite</strong>
                    <div class="muted">Progreso y observaciones</div>
                </div>
                <span class="badge">Abrir</span>
            </a>

            <a href="#"
               class="li axm-nav-link"
               data-view="complementar">
                <div>
                    <strong>Completar datos</strong>
                    <div class="muted">Sube documentos faltantes</div>
                </div>
                <span class="badge">Subir</span>
            </a>

            <a href="#"
               class="li axm-nav-link"
               data-view="historial">
                <div>
                    <strong>Historial</strong>
                    <div class="muted">Citas y movimientos</div>
                </div>
                <span class="badge">Ver</span>
            </a>

            <a href="#"
               class="li axm-nav-link"
               data-view="ajustes">
                <div>
                    <strong>Ajustes del perfil</strong>
                    <div class="muted">Idioma, notificaciones, seguridad</div>
                </div>
                <span class="badge">Editar</span>
            </a>
        </div>
    </section>
</div>

<script>
    // micro animación para el porcentaje
    (function () {
        const ring = document.querySelector('.ring');
        if (!ring) return;
        const target = parseInt(ring.getAttribute('data-pct') || '0', 10);
        let p = 0;
        const step = Math.max(1, Math.round(target / 40));
        const t = setInterval(() => {
            p += step;
            if (p >= target) { p = target; clearInterval(t); }
            ring.style.setProperty('--p', p);
            ring.setAttribute('data-pct', p);
        }, 16);
    })();
</script>
