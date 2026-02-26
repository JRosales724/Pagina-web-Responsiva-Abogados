<?php
// AXM/partials/footer.php

// Nos aseguramos de tener sesión para saber el rol
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROL_USUARIO'))
    define('ROL_USUARIO', 1);
if (!defined('ROL_ADMIN'))
    define('ROL_ADMIN', 2);
if (!defined('ROL_ABOGADO'))
    define('ROL_ABOGADO', 3);
?>
<footer class="axm-footer">
    <div class="axm-footer-inner">
        <div class="axm-footer-brand">
            <span class="axm-footer-logo">AXM</span>
            <div class="axm-footer-text">
                <strong>AXM ABOGADOS</strong>
                <span>Especialistas en pensiones ISSSTE</span>
            </div>
        </div>

        <nav class="axm-footer-links" aria-label="Enlaces legales">
            <a href="#">Aviso de privacidad</a>
            <span>•</span>
            <a href="#">Términos de uso</a>
            <span>•</span>
            <a href="#">Contacto</a>
        </nav>

        <p class="axm-footer-copy">
            © <?= date('Y') ?> AXM ABOGADOS · Sistema desarrollado por
            <span>JrDevSolutions</span>
        </p>
    </div>
</footer>

<style>
    .axm-footer {
        background: #2B2F36;
        color: #E5E7EB;
        border-top: 1px solid rgba(255, 255, 255, .08);
        margin-top: 32px;
        padding: 18px 0 12px;
        font-size: .86rem;
    }

    .axm-footer-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .axm-footer-brand {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .axm-footer-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 999px;
        background: #8A1538;
        font-weight: 900;
        font-size: .85rem;
    }

    .axm-footer-text span {
        display: block;
        color: #9CA3AF;
        font-size: .78rem;
    }

    .axm-footer-links {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        color: #9CA3AF;
    }

    .axm-footer-links a {
        text-decoration: none;
        color: inherit;
    }

    .axm-footer-links a:hover {
        color: #E5E7EB;
    }

    .axm-footer-copy {
        color: #9CA3AF;
    }

    .axm-footer-copy span {
        font-weight: 600;
        color: #E5E7EB;
    }

    @media (max-width:640px) {
        .axm-footer-inner {
            align-items: flex-start;
        }
    }
</style>

<?php
// ===== Cargar JS del panel según el rol =====
$tipo = (int) ($_SESSION['tipo'] ?? 0);

// JS común para navbar / drawer (admin y usuario)
echo '<script src="/AXM/assets/js/panel.js"></script>';

// JS específico por rol (SPA: cambio de vistas sin cambiar URL)
if ($tipo === ROL_USUARIO) {
    echo '<script src="/AXM/js/panel.usuario.js"></script>';
} elseif ($tipo === ROL_ADMIN) {
    echo '<script src="/AXM/js/panel.admin.js"></script>';
}
// Si en el futuro usas ROL_ABOGADO y le haces su propio JS, lo enganchamos aquí.
?>