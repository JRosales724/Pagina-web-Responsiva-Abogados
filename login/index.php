<?php
session_start();
if (isset($_SESSION["ID"])) {
  header("Location: ../");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Iniciar sesión — AXM</title>
  <link rel="icon" type="image/png" href="../img/img_index/logoaxm.jpeg" />

  <!-- MDB -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    /* ===== PALETA (clara del sitio) ===== */
    :root {
      --primary: #3A3A3A;
      --accent: #8A1538;
      --accent-700: #6E102C;
      --base: #F5F7FA;
      --ink: #1B1B1B;
      --paper: #FFFFFF;

      --muted: #6b7280;
      --line: #e5e7eb;
      --banner-url: url('../img/img_gifs/axmlogo2.gif');
    }

    body {
      background: var(--base);
      color: var(--ink);
    }

    main {
      flex: 1 1 auto;
    }

    .auth-wrap {
      max-width: 1160px;
      margin-inline: auto;
    }

    .auth-card {
      border-radius: 22px;
      overflow: hidden;
      background: var(--paper);
      box-shadow: 0 22px 60px rgba(0, 0, 0, .08);
      border: 1px solid var(--line);
      min-height: 560px;
      padding: 0;
    }

    /* ===== Banner izquierdo ===== */
    .left-banner {
      position: relative;
      min-height: 560px;
      display: flex;
      align-items: flex-end;
      padding: 0;
      isolation: isolate;
    }

    .left-banner::before {
      content: "";
      position: absolute;
      inset: 0;
      background: var(--banner-url) center/cover no-repeat;
      filter: saturate(1.02) brightness(.93);
      z-index: -2;
    }

    .left-banner::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, rgba(0, 0, 0, .72) 0%, rgba(0, 0, 0, .55) 38%, rgba(0, 0, 0, .28) 66%, rgba(0, 0, 0, .08) 100%);
      z-index: -1;
    }

    .left-badge {
      position: absolute;
      top: 14px;
      left: 14px;
      background: rgba(0, 0, 0, .5);
      border: 1px solid rgba(255, 255, 255, .18);
      border-radius: 999px;
      font-size: .75rem;
      padding: .35rem .6rem;
      color: #fff;
      text-decoration: none;
    }

    .left-copy {
      padding: 2.6rem 2rem 2.4rem;
      max-width: 560px;
      color: #fff;
    }

    .left-copy p {
      color: rgba(255, 255, 255, .92);
      margin-top: .4rem;
    }

    /* ===== Marca tipográfica (AXM ABOGADOS) ===== */
    .brand-stamp {
      display: inline-flex;
      align-items: baseline;
      gap: .4rem;
      font-weight: 900;
      user-select: none;
      letter-spacing: .4px;
      font-size: clamp(1.3rem, 2.5vw, 1.8rem);
    }

    .brand-stamp--dark .axm {
      color: var(--ink);
    }

    .brand-stamp--light .axm {
      color: #fff;
      text-shadow: 0 2px 6px rgba(0, 0, 0, .35);
    }

    .brand-stamp .abogados {
      color: var(--accent);
    }

    /* ===== Panel formulario ===== */
    .right-pane {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.25rem;
      background: var(--paper);
    }

    .card-pane {
      width: 100%;
      max-width: 500px;
      background: var(--paper);
      border: 1px solid var(--line);
      border-radius: 18px;
      padding: 1.75rem 1.5rem;
      box-shadow: 0 10px 28px rgba(0, 0, 0, .06);
    }

    .form-title {
      font-weight: 900;
      color: var(--primary);
    }

    .helper-text {
      color: var(--muted) !important;
    }

    .input-group .form-control {
      background: #fff;
      border: 1px solid var(--line);
      color: var(--ink);
      border-radius: 12px !important;
    }

    .input-group-text {
      background: #fff;
      border: 1px solid var(--line);
      color: #9ca3af;
      border-radius: 12px !important;
    }

    .btn-primary {
      background: var(--accent);
      border: none;
      border-radius: 12px;
      font-weight: 800;
      box-shadow: 0 10px 24px rgba(138, 21, 56, .22);
    }

    .btn-primary:hover {
      background: var(--accent-700);
    }

    .form-text-link {
      color: var(--muted);
      font-size: .9rem;
      text-decoration: none;
    }

    .form-text-link:hover {
      color: var(--primary);
      text-decoration: underline;
    }

    footer {
      background: #2B2F36;
      color: #E5E7EB;
      font-size: .85rem;
      text-align: center;
      padding: .9rem 0;
    }

    @media (max-width: 991.98px) {
      .left-banner {
        display: none;
      }

      .right-pane {
        padding: 1.25rem;
      }

      .card-pane {
        margin: .5rem auto;
      }
    }

    @media (max-width: 575.98px) {
      .card-pane {
        padding: 1.25rem 1rem;
      }
    }

    /* ====== Animación de cambio de vista ====== */
    .view {
      will-change: opacity, transform;
    }

    .fade-out-up {
      animation: fadeOutUp .25s ease forwards;
    }

    .fade-in-up {
      animation: fadeInUp .28s ease forwards;
    }

    @keyframes fadeOutUp {
      from {
        opacity: 1;
        transform: translateY(0);
      }

      to {
        opacity: 0;
        transform: translateY(-8px);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(8px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Ocultación accesible cuando no está activa la vista */
    .is-hidden {
      display: none !important;
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100">

  <main class="d-flex align-items-center py-4">
    <div class="container auth-wrap">
      <div class="row g-0 auth-card">

        <!-- Banner izquierdo -->
        <div class="col-lg-6 left-banner">
          <a class="left-badge" href="../"><i class="fa-solid fa-arrow-left me-1"></i> volver al sitio</a>
          <div class="left-copy">
            <div class="brand-stamp brand-stamp--light">
              <span class="axm">AXM</span>
              <span class="abogados">ABOGADOS</span>
            </div>
            <p class="mb-0">Consulta tus citas, sube documentos y da seguimiento a tu trámite en un solo lugar.</p>
          </div>
        </div>

        <!-- Panel derecho -->
        <div class="col-lg-6 right-pane">
          <div class="card-pane">

            <!-- Encabezado (centrado) -->
            <div class="text-center mb-3">
              <div class="brand-stamp brand-stamp--dark">
                <span class="axm">AXM</span>
                <span class="abogados">ABOGADOS</span>
              </div>
            </div>

            <!-- TITULO + AYUDA (se reemplazan según la vista) -->
            <h3 id="cardTitle" class="form-title mb-1">Iniciar sesión</h3>
            <p id="cardHelper" class="mb-4 helper-text">Accede con tus credenciales para continuar.</p>

            <!-- ========== VISTA: LOGIN ==========
                 (por defecto visible) -->
            <div id="viewLogin" class="view">
              <form id="login-form" action="../php/login.php" method="POST" novalidate>
                <div class="mb-3">
                  <label class="form-label small" style="color:var(--primary)">Usuario</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username"
                      placeholder="correo@ejemplo.com" required>
                  </div>
                </div>

                <div class="mb-2">
                  <label class="form-label small" style="color:var(--primary)">Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                      placeholder="Tu contraseña" required>
                    <button class="input-group-text" type="button" id="togglePass"
                      aria-label="Mostrar/ocultar contraseña">
                      <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </button>
                  </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label small" for="remember">Mantener sesión iniciada</label>
                  </div>
                  <a href="#" id="linkRecover" class="form-text-link">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">INICIAR SESIÓN</button>

                <div class="text-center mt-3">
                  <span class="small" style="color:var(--muted)">¿No tienes cuenta?
                    <a href="../formularios/formulario_inicio.php" class="form-text-link">Regístrate</a></span>
                </div>
              </form>
            </div>

            <!-- ========== VISTA: RECOVERY ==========
                 (inicia oculta) -->
            <div id="viewRecover" class="view is-hidden" aria-hidden="true">
              <form id="recover-form" novalidate>
                <div class="mb-3">
                  <label class="form-label small" style="color:var(--primary)">Correo electrónico</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" class="form-control" id="recoverEmail" placeholder="tu@mail.com" required>
                  </div>
                </div>

                <p class="small helper-text mb-4">
                  Te enviaremos un enlace para restablecer tu contraseña. Si no lo recibes en unos minutos,
                  revisa tu carpeta de spam.
                </p>

                <button type="submit" class="btn btn-primary w-100 py-2">ENVIAR ENLACE</button>

                <div class="text-center mt-3">
                  <a href="#" id="linkBackLogin" class="form-text-link">Volver a iniciar sesión</a>
                </div>
              </form>
            </div>

          </div>
        </div>

      </div>
    </div>
  </main>

  <footer>
    SISTEMA DESARROLLADO POR JRDEVSOLUTIONS ·
    DESARROLLADO POR EL ING. JESUS ROSALES
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
  <script>
    // ===== Mostrar/ocultar contraseña
    (function () {
      const pass = document.getElementById('password');
      const btn = document.getElementById('togglePass');
      const eye = document.getElementById('eyeIcon');
      btn?.addEventListener('click', () => {
        const type = pass.type === 'password' ? 'text' : 'password';
        pass.type = type;
        eye.classList.toggle('fa-eye');
        eye.classList.toggle('fa-eye-slash');
      });
    })();

    // ===== Validación mínima del login
    document.getElementById('login-form')?.addEventListener('submit', function (e) {
      if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
      this.classList.add('was-validated');
    });

    // ===== Cambio de vistas con animación
    const viewLogin = document.getElementById('viewLogin');
    const viewRecover = document.getElementById('viewRecover');
    const title = document.getElementById('cardTitle');
    const helper = document.getElementById('cardHelper');

    function switchTo(view) { // 'login' | 'recover'
      const out = (view === 'login') ? viewRecover : viewLogin;
      const _in = (view === 'login') ? viewLogin : viewRecover;

      // Actualiza título / ayuda
      if (view === 'login') {
        title.textContent = 'Iniciar sesión';
        helper.textContent = 'Accede con tus credenciales para continuar.';
      } else {
        title.textContent = 'Recuperar contraseña';
        helper.textContent = 'Ingresa el correo asociado a tu cuenta.';
      }

      // Anima salida
      out.classList.remove('fade-in-up');
      out.classList.add('fade-out-up');

      // Al terminar salida, oculto y muestro la otra con entrada
      out.addEventListener('animationend', function handleOut() {
        out.classList.remove('fade-out-up');
        out.classList.add('is-hidden');
        out.setAttribute('aria-hidden', 'true');

        _in.classList.remove('is-hidden');
        _in.removeAttribute('aria-hidden');
        _in.classList.add('fade-in-up');

        // limpiar al terminar la entrada
        _in.addEventListener('animationend', function handleIn() {
          _in.classList.remove('fade-in-up');
          _in.removeEventListener('animationend', handleIn);
        });

        out.removeEventListener('animationend', handleOut);
      });
    }

    // Enlaces
    document.getElementById('linkRecover')?.addEventListener('click', (e) => { e.preventDefault(); switchTo('recover'); });
    document.getElementById('linkBackLogin')?.addEventListener('click', (e) => { e.preventDefault(); switchTo('login'); });

    // ===== Validación mínima de recovery (demo)
    document.getElementById('recover-form')?.addEventListener('submit', function (e) {
      if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); this.classList.add('was-validated'); return; }
      e.preventDefault();
      // Aquí harías el fetch a tu endpoint de recuperación
      // fetch('../api/recover.php', {method:'POST', body: new FormData(this)})
      //   .then(...)

      // Feedback simple
      alert('Si el correo existe, recibirás un enlace de recuperación.');
      switchTo('login');
    });
  </script>
</body>

</html>