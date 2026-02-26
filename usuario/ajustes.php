<?php
// AXM/usuario/ajustes.php  (Ajustes del perfil)
declare(strict_types=1);

$uid     = (int) ($_SESSION['ID'] ?? 0);
$nombre  = trim((string) ($_SESSION['NOMBRE'] ?? $_SESSION['name'] ?? ''));
$correo  = trim((string) ($_SESSION['EMAIL']  ?? ''));
$telefono = trim((string) ($_SESSION['TEL']   ?? ''));
$avatar  = (string) ($_SESSION['FOTO_PERFIL'] ?? ''); // si lo manejas en sesión

if ($nombre === '') {
    $nombre = 'Usuario';
}

// Valores de ejemplo para preferencias (ajusta con tu BD luego)
$pref_idioma    = $_SESSION['PREF_IDIOMA']    ?? 'es';
$pref_tema      = $_SESSION['PREF_TEMA']      ?? 'claro';        // claro|oscuro|sistema
$pref_not_email = !empty($_SESSION['PREF_NOT_EMAIL']);
$pref_not_wapp  = !empty($_SESSION['PREF_NOT_WAPP']);
$pref_not_sms   = !empty($_SESSION['PREF_NOT_SMS']);
?>
<style>
    /* ===== AJUSTES PERFIL (SCOPED) ===== */
    .pf-wrap {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .pf-head {
        background: linear-gradient(135deg, #111 0%, #3A3A3A 40%, #8A1538 100%);
        color: #fff;
        border-radius: 18px;
        padding: 22px 18px;
        box-shadow: 0 18px 34px rgba(0,0,0,.18);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .pf-head::after {
        content:"";
        position:absolute;
        inset:auto -20% -80% -20%;
        height:230px;
        background:radial-gradient(closest-side,rgba(255,255,255,.18),transparent 60%);
        opacity:.7;
    }

    .pf-avatar-big {
        position:relative;
        width:64px;
        height:64px;
        border-radius:999px;
        overflow:hidden;
        flex-shrink:0;
        background:#111;
        box-shadow:0 10px 24px rgba(0,0,0,.3);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.8rem;
        font-weight:800;
    }
    .pf-avatar-big img {
        width:100%;
        height:100%;
        object-fit:cover;
    }

    .pf-title {
        margin:0;
        font-weight:900;
        font-size:clamp(1.3rem,3.6vw,1.9rem);
    }

    .pf-sub {
        margin-top:4px;
        color:#f9fafb;
        opacity:.95;
    }

    .pf-pill {
        display:inline-flex;
        align-items:center;
        gap:8px;
        margin-top:8px;
        padding:6px 10px;
        border-radius:999px;
        font-size:.82rem;
        font-weight:800;
        background:rgba(255,255,255,.16);
        border:1px solid rgba(255,255,255,.4);
    }

    .pf-actions {
        margin-top:10px;
        display:flex;
        flex-wrap:wrap;
        gap:10px;
    }

    .pf-btn-white {
        background:#fff;
        color:#111;
        padding:8px 13px;
        border-radius:999px;
        font-weight:900;
        font-size:.85rem;
        text-decoration:none;
    }

    .pf-btn-ghost {
        background:rgba(255,255,255,.08);
        color:#fff;
        padding:8px 13px;
        border-radius:999px;
        font-weight:800;
        font-size:.85rem;
        border:1px solid rgba(255,255,255,.25);
        text-decoration:none;
    }

    .pf-grid {
        display:grid;
        grid-template-columns: 1.6fr 1.4fr;
        gap:18px;
        margin-top:18px;
    }
    @media(max-width:960px){
        .pf-grid {
            grid-template-columns:1fr;
        }
    }

    .pf-card {
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:16px;
        padding:18px;
        box-shadow:0 10px 26px rgba(0,0,0,.06);
    }
    .pf-card h3 {
        margin:0 0 8px;
        font-size:1.05rem;
        font-weight:900;
        color:#111;
    }
    .pf-muted {
        color:#6b7280;
        font-size:.9rem;
    }

    .pf-form-grid {
        display:grid;
        grid-template-columns: repeat(2,minmax(0,1fr));
        gap:12px;
        margin-top:12px;
    }
    @media(max-width:720px){
        .pf-form-grid {
            grid-template-columns:1fr;
        }
    }

    .pf-label {
        display:block;
        font-size:.85rem;
        font-weight:700;
        color:#111;
        margin-bottom:4px;
    }
    .pf-input, .pf-select {
        width:100%;
        border-radius:10px;
        border:1px solid #e5e7eb;
        padding:8px 10px;
        font-size:.9rem;
    }
    .pf-input:focus, .pf-select:focus {
        outline:none;
        border-color:#8A1538;
        box-shadow:0 0 0 1px #8A153833;
    }

    .pf-footer-actions {
        margin-top:14px;
        display:flex;
        justify-content:flex-end;
        flex-wrap:wrap;
        gap:8px;
    }
    .pf-btn-secondary {
        border-radius:10px;
        padding:8px 14px;
        border:1px solid #e5e7eb;
        background:#f9fafb;
        font-size:.85rem;
        font-weight:700;
        cursor:pointer;
    }
    .pf-btn-primary {
        border-radius:10px;
        padding:8px 14px;
        background:#8A1538;
        color:#fff;
        font-size:.85rem;
        font-weight:800;
        border:none;
        cursor:pointer;
    }
    .pf-btn-primary:hover {
        filter:brightness(1.05);
    }

    /* Preferencias */
    .pf-pref-group {
        margin-top:10px;
        padding:10px;
        border-radius:12px;
        background:#f9fafb;
        border:1px solid #e5e7eb;
        font-size:.85rem;
        color:#374151;
    }
    .pf-pref-item {
        display:flex;
        align-items:center;
        gap:8px;
        margin-top:6px;
    }
    .pf-pref-item label {
        cursor:pointer;
    }

    /* Seguridad */
    .pf-alert-danger {
        margin-top:12px;
        padding:10px;
        border-radius:12px;
        background:#fef2f2;
        border:1px solid #fecaca;
        color:#991b1b;
        font-size:.85rem;
    }
    .pf-btn-danger {
        border-radius:10px;
        padding:8px 14px;
        background:#b91c1c;
        color:#fff;
        font-size:.85rem;
        font-weight:800;
        border:none;
        cursor:pointer;
    }

    /* Fade mini */
    .pf-fade {opacity:0;transform:translateY(8px);animation:pfFade .45s ease .02s forwards;}
    .pf-fade2{opacity:0;transform:translateY(8px);animation:pfFade .45s ease .16s forwards;}
    .pf-fade3{opacity:0;transform:translateY(8px);animation:pfFade .45s ease .3s forwards;}

    @keyframes pfFade {
        to {opacity:1;transform:none;}
    }
</style>

<div class="pf-wrap">
    <!-- HEADER -->
    <section class="pf-head pf-fade">
        <div class="pf-avatar-big">
            <?php if ($avatar): ?>
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Foto de perfil">
            <?php else: ?>
                <?php
                $inicial = mb_substr($nombre, 0, 1, 'UTF-8');
                echo htmlspecialchars(mb_strtoupper($inicial,'UTF-8'));
                ?>
            <?php endif; ?>
        </div>
        <div>
            <h2 class="pf-title">Ajustes del perfil</h2>
            <p class="pf-sub">
                Administra tus datos, seguridad y preferencias de notificación.
            </p>
            <span class="pf-pill">
                <span>👤 Sesión activa como</span>
                <strong><?php echo htmlspecialchars($nombre); ?></strong>
            </span>
            <div class="pf-actions">
                <a href="/AXM/" class="pf-btn-white axm-nav-link" data-view="inicio">Ir al inicio</a>
                <a href="/AXM/" class="pf-btn-ghost axm-nav-link" data-view="consultas">Ver estatus</a>
            </div>
        </div>
    </section>

    <section class="pf-grid">
        <!-- PERFIL BÁSICO + AVATAR -->
        <div class="pf-card pf-fade2">
            <h3>Datos del perfil</h3>
            <p class="pf-muted">
                Actualiza tu nombre, correo y teléfono. Estos datos se usan para contactarte y mostrar tu perfil.
            </p>

            <!-- Avatar -->
            <form method="post" action="/AXM/php/perfil_avatar.php" enctype="multipart/form-data" style="margin-top:12px;">
                <label class="pf-label">Foto de perfil</label>
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="pf-avatar-big" style="width:52px;height:52px;font-size:1.4rem;box-shadow:none;">
                        <?php if ($avatar): ?>
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Foto actual">
                        <?php else: ?>
                            <?php echo htmlspecialchars(mb_strtoupper($inicial ?? 'U','UTF-8')); ?>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <input type="file" name="avatar" accept="image/*"
                            style="font-size:.8rem;margin-bottom:4px;">
                        <div class="pf-muted" style="font-size:.8rem;">
                            Formatos recomendados: JPG o PNG · máx. 2&nbsp;MB.
                        </div>
                    </div>
                </div>
                <div class="pf-footer-actions">
                    <button type="submit" class="pf-btn-primary">Actualizar foto</button>
                </div>
            </form>

            <!-- Datos básicos -->
            <form method="post" action="/AXM/php/perfil_actualizar.php" style="margin-top:16px;">
                <div class="pf-form-grid">
                    <div>
                        <label for="pf_nombre" class="pf-label">Nombre completo</label>
                        <input id="pf_nombre" name="nombre" type="text" class="pf-input"
                               value="<?php echo htmlspecialchars($nombre); ?>" required>
                    </div>
                    <div>
                        <label for="pf_correo" class="pf-label">Correo electrónico</label>
                        <input id="pf_correo" name="correo" type="email" class="pf-input"
                               value="<?php echo htmlspecialchars($correo); ?>" required>
                    </div>
                    <div>
                        <label for="pf_tel" class="pf-label">Teléfono / WhatsApp</label>
                        <input id="pf_tel" name="telefono" type="tel" class="pf-input"
                               value="<?php echo htmlspecialchars($telefono); ?>" placeholder="55 0000 0000">
                    </div>
                    <div>
                        <label for="pf_idioma" class="pf-label">Idioma preferido</label>
                        <select id="pf_idioma" name="idioma" class="pf-select">
                            <option value="es" <?php echo $pref_idioma === 'es' ? 'selected' : ''; ?>>Español</option>
                            <option value="en" <?php echo $pref_idioma === 'en' ? 'selected' : ''; ?>>Inglés</option>
                        </select>
                    </div>
                </div>

                <div class="pf-footer-actions">
                    <button type="reset" class="pf-btn-secondary">Deshacer cambios</button>
                    <button type="submit" class="pf-btn-primary">Guardar perfil</button>
                </div>
            </form>
        </div>

        <!-- PREFERENCIAS + SEGURIDAD -->
        <div class="pf-card pf-fade3">
            <!-- Preferencias -->
            <h3>Preferencias</h3>
            <p class="pf-muted">
                Ajusta cómo quieres recibir avisos y el aspecto de tu panel.
            </p>

            <form method="post" action="/AXM/php/perfil_preferencias.php">
                <div class="pf-pref-group">
                    <strong>Notificaciones</strong>
                    <div class="pf-pref-item">
                        <input type="checkbox" id="pf_not_email" name="not_email" value="1"
                               <?php echo $pref_not_email ? 'checked' : ''; ?>>
                        <label for="pf_not_email">Recibir correos electrónicos</label>
                    </div>
                    <div class="pf-pref-item">
                        <input type="checkbox" id="pf_not_wapp" name="not_wapp" value="1"
                               <?php echo $pref_not_wapp ? 'checked' : ''; ?>>
                        <label for="pf_not_wapp">Mensajes por WhatsApp</label>
                    </div>
                    <div class="pf-pref-item">
                        <input type="checkbox" id="pf_not_sms" name="not_sms" value="1"
                               <?php echo $pref_not_sms ? 'checked' : ''; ?>>
                        <label for="pf_not_sms">SMS (cuando aplique)</label>
                    </div>
                </div>

                <div class="pf-pref-group" style="margin-top:10px;">
                    <strong>Tema visual</strong>
                    <div class="pf-pref-item">
                        <input type="radio" id="pf_tema_claro" name="tema" value="claro"
                               <?php echo $pref_tema === 'claro' ? 'checked' : ''; ?>>
                        <label for="pf_tema_claro">Claro</label>
                    </div>
                    <div class="pf-pref-item">
                        <input type="radio" id="pf_tema_oscuro" name="tema" value="oscuro"
                               <?php echo $pref_tema === 'oscuro' ? 'checked' : ''; ?>>
                        <label for="pf_tema_oscuro">Oscuro</label>
                    </div>
                    <div class="pf-pref-item">
                        <input type="radio" id="pf_tema_sistema" name="tema" value="sistema"
                               <?php echo $pref_tema === 'sistema' ? 'checked' : ''; ?>>
                        <label for="pf_tema_sistema">Usar configuración del sistema</label>
                    </div>
                </div>

                <div class="pf-footer-actions">
                    <button type="submit" class="pf-btn-primary">Guardar preferencias</button>
                </div>
            </form>

            <hr style="margin:16px 0;border:none;border-top:1px solid #e5e7eb;">

            <!-- Seguridad -->
            <h3>Seguridad</h3>
            <p class="pf-muted">
                Cambia tu contraseña periódicamente para mantener tu cuenta protegida.
            </p>

            <form method="post" action="/AXM/php/perfil_password.php" style="margin-top:10px;">
                <div class="pf-form-grid" style="grid-template-columns:1fr;">
                    <div>
                        <label for="pf_pass_actual" class="pf-label">Contraseña actual</label>
                        <input id="pf_pass_actual" name="pass_actual" type="password" class="pf-input" required>
                    </div>
                    <div>
                        <label for="pf_pass_nueva" class="pf-label">Nueva contraseña</label>
                        <input id="pf_pass_nueva" name="pass_nueva" type="password" class="pf-input" required>
                    </div>
                    <div>
                        <label for="pf_pass_conf" class="pf-label">Confirmar nueva contraseña</label>
                        <input id="pf_pass_conf" name="pass_conf" type="password" class="pf-input" required>
                    </div>
                </div>
                <div class="pf-footer-actions">
                    <button type="submit" class="pf-btn-primary">Actualizar contraseña</button>
                </div>
            </form>

            <div class="pf-alert-danger">
                Si detectas actividad extraña en tu cuenta, cambia tu contraseña y avisa al despacho.
            </div>

            <!-- Opcional: desactivar cuenta -->
            <!--
            <div style="margin-top:10px;text-align:right;">
                <form method="post" action="/AXM/php/perfil_desactivar.php"
                      onsubmit="return confirm('¿Seguro que deseas solicitar la desactivación de tu cuenta?');">
                    <button type="submit" class="pf-btn-danger">Solicitar desactivación de cuenta</button>
                </form>
            </div>
            -->
        </div>
    </section>
</div>
