<?php
// AXM/admin/ajustes.php
declare(strict_types=1);

$adminNombre = trim((string) ($_SESSION['ADMIN_NOMBRE'] ?? $_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? 'Administrador'));
$adminCorreo = trim((string) ($_SESSION['ADMIN_EMAIL'] ?? $_SESSION['EMAIL'] ?? ''));
$adminTel = trim((string) ($_SESSION['ADMIN_TEL'] ?? $_SESSION['TEL'] ?? ''));
$adminUsuario = trim((string) ($_SESSION['ADMIN_USER'] ?? $_SESSION['username'] ?? 'admin'));
$adminRol = 'Administrador';
?>
<style>
    /* ===== ADMIN AJUSTES (SCOPED) ===== */
    .aaj-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 20px;
    }

    .aaj-head {
        border-radius: 18px;
        padding: 18px 18px 14px;
        background: linear-gradient(115deg, #111827 0%, #8A1538 45%, #6E102C 100%);
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

    .aaj-head::after {
        content: "";
        position: absolute;
        inset: auto -20% -70% -20%;
        height: 220px;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .2), transparent 60%);
        opacity: .7;
    }

    .aaj-head-main {
        max-width: 640px;
        position: relative;
        z-index: 1;
    }

    .aaj-title {
        margin: 0;
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        font-weight: 900;
    }

    .aaj-sub {
        margin: 6px 0 0;
        font-size: .9rem;
        color: #E5E7EB;
    }

    .aaj-chip-row {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .aaj-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(17, 24, 39, .4);
        border: 1px solid rgba(249, 250, 251, .4);
        font-size: .78rem;
        font-weight: 800;
    }

    .aaj-chip strong {
        font-weight: 900;
    }

    .aaj-head-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 220px;
        position: relative;
        z-index: 1;
    }

    .aaj-btn-main,
    .aaj-btn-ghost {
        border-radius: 999px;
        padding: 8px 12px;
        font-size: .86rem;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
    }

    .aaj-btn-main {
        background: #F9FAFB;
        color: #111827;
    }

    .aaj-btn-ghost {
        background: rgba(17, 24, 39, .3);
        color: #F9FAFB;
        border: 1px solid rgba(249, 250, 251, .3);
    }

    .aaj-btn-ghost:hover {
        background: rgba(17, 24, 39, .5);
    }

    .aaj-grid {
        margin-top: 18px;
        display: grid;
        grid-template-columns: 1.6fr 1.2fr;
        gap: 16px;
    }

    @media(max-width:960px) {
        .aaj-grid {
            grid-template-columns: 1fr;
        }
    }

    .aaj-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        padding: 16px 15px 14px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .06);
    }

    .aaj-card-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: baseline;
        margin-bottom: 10px;
    }

    .aaj-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #111827;
    }

    .aaj-card-sub {
        margin: 2px 0 0;
        font-size: .85rem;
        color: #6B7280;
    }

    .aaj-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    @media(max-width:720px) {
        .aaj-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .aaj-label {
        display: block;
        font-size: .83rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 3px;
    }

    .aaj-input {
        width: 100%;
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        padding: 8px 10px;
        font-size: .9rem;
    }

    .aaj-input:focus {
        outline: none;
        border-color: #8A1538;
        box-shadow: 0 0 0 1px #8A153833;
    }

    .aaj-input[readonly] {
        background: #F9FAFB;
        color: #4B5563;
    }

    .aaj-actions-row {
        margin-top: 14px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .aaj-btn-secondary {
        border-radius: 10px;
        padding: 7px 12px;
        font-size: .85rem;
        font-weight: 700;
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        cursor: pointer;
    }

    .aaj-btn-primary {
        border-radius: 10px;
        padding: 7px 12px;
        font-size: .85rem;
        font-weight: 800;
        border: none;
        background: #8A1538;
        color: #FFFFFF;
        cursor: pointer;
    }

    .aaj-btn-primary:hover {
        filter: brightness(1.05);
    }

    .aaj-switch-row {
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .aaj-switch {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 8px 9px;
        border-radius: 10px;
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
    }

    .aaj-switch-main {
        font-size: .86rem;
        color: #111827;
        font-weight: 600;
    }

    .aaj-switch-sub {
        font-size: .78rem;
        color: #6B7280;
    }

    .aaj-switch input {
        width: 38px;
        height: 20px;
    }

    .aaj-note {
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 10px;
        background: #FEF3C7;
        border: 1px solid #FDE68A;
        font-size: .8rem;
        color: #92400E;
    }

    .aaj-fade {
        opacity: 0;
        transform: translateY(8px);
        animation: aajFade .4s ease .05s forwards;
    }

    .aaj-fade2 {
        opacity: 0;
        transform: translateY(8px);
        animation: aajFade .4s ease .16s forwards;
    }

    .aaj-fade3 {
        opacity: 0;
        transform: translateY(8px);
        animation: aajFade .4s ease .27s forwards;
    }

    @keyframes aajFade {
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="aaj-wrap">
    <!-- ENCABEZADO -->
    <section class="aaj-head aaj-fade">
        <div class="aaj-head-main">
            <h2 class="aaj-title">Ajustes del perfil de administrador</h2>
            <p class="aaj-sub">
                Administra tus datos, credenciales y preferencias de notificación. Estos ajustes
                sólo afectan a tu cuenta de administrador, no a los usuarios finales.
            </p>
            <div class="aaj-chip-row">
                <span class="aaj-chip">
                    Rol: <strong><?php echo htmlspecialchars($adminRol); ?></strong>
                </span>
                <span class="aaj-chip">
                    Usuario: <strong><?php echo htmlspecialchars($adminUsuario); ?></strong>
                </span>
                <?php if ($adminCorreo): ?>
                    <span class="aaj-chip">
                        Correo: <strong><?php echo htmlspecialchars($adminCorreo); ?></strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="aaj-head-actions">
            <button type="button" class="aaj-btn-main axm-nav-link" data-view="dashboard">
                Ir al dashboard
            </button>
            <button type="button" class="aaj-btn-ghost axm-nav-link" data-view="usuarios">
                Ir a usuarios
            </button>
            <button type="button" class="aaj-btn-ghost axm-nav-link" data-view="notificaciones">
                Ver notificaciones
            </button>
        </div>
    </section>

    <!-- GRID -->
    <section class="aaj-grid">
        <!-- PERFIL / DATOS BÁSICOS -->
        <article class="aaj-card aaj-fade2">
            <div class="aaj-card-header">
                <div>
                    <h3 class="aaj-card-title">Datos del perfil</h3>
                    <p class="aaj-card-sub">
                        Nombre, correo y teléfono que se utilizan para acceder al sistema y recibir avisos.
                    </p>
                </div>
            </div>

            <form method="post" action="/AXM/php/admin_actualizar_perfil.php" autocomplete="on">
                <div class="aaj-form-grid">
                    <div>
                        <label class="aaj-label" for="adm_nombre">Nombre completo</label>
                        <input id="adm_nombre" name="nombre" type="text" class="aaj-input"
                            value="<?php echo htmlspecialchars($adminNombre); ?>" required>
                    </div>

                    <div>
                        <label class="aaj-label" for="adm_usuario">Usuario (login)</label>
                        <input id="adm_usuario" name="usuario" type="text" class="aaj-input"
                            value="<?php echo htmlspecialchars($adminUsuario); ?>" required>
                    </div>

                    <div>
                        <label class="aaj-label" for="adm_correo">Correo electrónico</label>
                        <input id="adm_correo" name="correo" type="email" class="aaj-input"
                            value="<?php echo htmlspecialchars($adminCorreo); ?>" required>
                    </div>

                    <div>
                        <label class="aaj-label" for="adm_tel">Teléfono / WhatsApp</label>
                        <input id="adm_tel" name="telefono" type="tel" class="aaj-input"
                            value="<?php echo htmlspecialchars($adminTel); ?>" placeholder="55 0000 0000">
                    </div>

                    <div>
                        <label class="aaj-label">Rol</label>
                        <input type="text" class="aaj-input" value="<?php echo htmlspecialchars($adminRol); ?>"
                            readonly>
                    </div>

                    <div>
                        <label class="aaj-label" for="adm_avatar">Avatar (opcional)</label>
                        <input id="adm_avatar" name="avatar" type="file" class="aaj-input"
                            accept="image/png,image/jpeg,image/jpg">
                    </div>
                </div>

                <div class="aaj-actions-row">
                    <button type="reset" class="aaj-btn-secondary">Cancelar cambios</button>
                    <button type="submit" class="aaj-btn-primary">Guardar perfil</button>
                </div>
            </form>
        </article>

        <!-- SEGURIDAD Y NOTIFICACIONES -->
        <article class="aaj-card aaj-fade3">
            <div class="aaj-card-header">
                <div>
                    <h3 class="aaj-card-title">Seguridad y notificaciones</h3>
                    <p class="aaj-card-sub">
                        Cambia tu contraseña y define cómo quieres recibir alertas del sistema.
                    </p>
                </div>
            </div>

            <!-- Cambiar contraseña -->
            <form method="post" action="/AXM/php/admin_cambiar_password.php" autocomplete="off">
                <div class="aaj-form-grid">
                    <div>
                        <label class="aaj-label" for="adm_pass_actual">Contraseña actual</label>
                        <input id="adm_pass_actual" name="password_actual" type="password" class="aaj-input"
                            autocomplete="current-password" required>
                    </div>
                    <div>
                        <label class="aaj-label" for="adm_pass_nueva">Nueva contraseña</label>
                        <input id="adm_pass_nueva" name="password_nueva" type="password" class="aaj-input"
                            autocomplete="new-password" required>
                    </div>
                    <div>
                        <label class="aaj-label" for="adm_pass_conf">Confirmar nueva contraseña</label>
                        <input id="adm_pass_conf" name="password_confirm" type="password" class="aaj-input"
                            autocomplete="new-password" required>
                    </div>
                </div>

                <div class="aaj-actions-row">
                    <span style="flex:1;align-self:center;font-size:.78rem;color:#6B7280;">
                        Se recomienda actualizar tu contraseña cada 3–6 meses.
                    </span>
                    <button type="submit" class="aaj-btn-primary">Actualizar contraseña</button>
                </div>
            </form>

            <hr style="margin:14px 0;border:none;border-top:1px solid #E5E7EB;">

            <!-- Preferencias de notificaciones -->
            <form method="post" action="/AXM/php/admin_guardar_preferencias.php">
                <div class="aaj-switch-row">
                    <label class="aaj-switch">
                        <div>
                            <div class="aaj-switch-main">Notificaciones por correo</div>
                            <div class="aaj-switch-sub">
                                Recibir resúmenes de casos, nuevos usuarios y alertas importantes.
                            </div>
                        </div>
                        <input type="checkbox" name="notif_email" value="1" checked>
                    </label>

                    <label class="aaj-switch">
                        <div>
                            <div class="aaj-switch-main">Recordatorios de audiencias / plazos</div>
                            <div class="aaj-switch-sub">
                                Recordatorios previos a fechas clave del expediente.
                            </div>
                        </div>
                        <input type="checkbox" name="notif_plazos" value="1" checked>
                    </label>

                    <label class="aaj-switch">
                        <div>
                            <div class="aaj-switch-main">Alertas por WhatsApp (manual)</div>
                            <div class="aaj-switch-sub">
                                El sistema señalará qué casos tienen alertas; el envío por WhatsApp se realiza
                                manualmente.
                            </div>
                        </div>
                        <input type="checkbox" name="notif_whatsapp" value="1">
                    </label>
                </div>

                <div class="aaj-actions-row">
                    <button type="submit" class="aaj-btn-primary">Guardar preferencias</button>
                </div>

                <div class="aaj-note">
                    🔐 <strong>Nota:</strong> estas opciones son un punto de partida. Más adelante puedes
                    conectar un servicio de correo/WhatsApp real y usar estos campos para activar
                    o desactivar envíos automáticos.
                </div>
            </form>
        </article>
    </section>
</div>

<script>
    // Micro validación de contraseña (cliente) opcional
    (function () {
        const form = document.querySelector('form[action$="admin_cambiar_password.php"]');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            const pass = form.querySelector('#adm_pass_nueva');
            const pass2 = form.querySelector('#adm_pass_conf');
            if (!pass || !pass2) return;
            if (pass.value !== pass2.value) {
                e.preventDefault();
                alert('Las contraseñas nuevas no coinciden.');
                pass2.focus();
            }
        });
    })();
</script>