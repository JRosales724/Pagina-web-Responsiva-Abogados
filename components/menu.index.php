<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AXM ABOGADOS</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3A3A3A',
                        accent: '#8A1538',     // Vino
                        accent700: '#6E102C',  // Vino más oscuro
                        base: '#F5F7FA',
                        ink: '#1B1B1B',
                        paper: '#FFFFFF',
                        footbg: '#2B2F36',     // Gris oscuro (no negro)
                        footink: '#E5E7EB'     // Texto claro para contraste
                    },
                    boxShadow: {
                        soft: '0 12px 30px rgba(0,0,0,.06)',
                        card: '0 12px 30px rgba(0,0,0,.08)',
                        wine: '0 8px 24px rgba(138,21,56,.22)'
                    }
                }
            }
        }
    </script>

    <!-- Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap"
        rel="stylesheet" />

    <style>
        :root {
            --primary: #3A3A3A;
            --accent: #8A1538;
            --accent-700: #6E102C;
            --base: #F5F7FA;
            --ink: #1B1B1B;
            --header-offset: 76px;
        }

        html,
        body {
            height: 100%
        }

        html {
            scroll-behavior: smooth
        }

        body {
            font-family: 'Inter', system-ui, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--base);
            color: var(--ink);
            padding-top: var(--header-offset)
        }

        /* Botones */
        .btn-accent {
            background: var(--accent);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(138, 21, 56, .20);
            transition: transform .2s ease, box-shadow .2s ease
        }

        .btn-accent:hover {
            background: var(--accent-700);
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(138, 21, 56, .30)
        }

        .btn-dark {
            background: var(--primary);
            color: #fff;
            font-weight: 800;
            transition: transform .2s ease, filter .2s ease
        }

        .btn-dark:hover {
            filter: brightness(1.05);
            transform: translateY(-2px)
        }

        /* Secciones */
        .section-dark {
            background: #2A2A2A;
            color: #f3f3f3
        }

        .section-muted {
            background: #F5F7FA
        }

        /* Animaciones */
        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .7s ease, transform .7s ease
        }

        .reveal.inview {
            opacity: 1;
            transform: none
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-26px);
            transition: opacity .7s ease, transform .7s ease
        }

        .reveal-left.inview {
            opacity: 1;
            transform: none
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(26px);
            transition: opacity .7s ease, transform .7s ease
        }

        .reveal-right.inview {
            opacity: 1;
            transform: none
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(.96);
            transition: opacity .5s ease, transform .5s ease
        }

        .reveal-scale.inview {
            opacity: 1;
            transform: scale(1)
        }

        .tiltable {
            transform-style: preserve-3d;
            transition: transform .25s ease
        }

        /* Header fijo + shrink */
        .nav-shrink {
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            padding-top: .5rem !important;
            padding-bottom: .5rem !important
        }

        /* Menú activo */
        .nav-link {
            position: relative
        }

        .nav-link.active,
        .nav-link:hover {
            color: var(--accent)
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            height: 2px;
            width: 100%;
            background: var(--accent)
        }

        /* Botón volver arriba */
        #toTop {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 60;
            opacity: 0;
            pointer-events: none;
            transform: translateY(10px);
            transition: opacity .3s ease, transform .3s ease
        }

        #toTop.show {
            opacity: 1;
            pointer-events: auto;
            transform: none
        }

        /* Shine H1 */
        .shine {
            background: linear-gradient(90deg, #111 0%, #111 40%, rgba(255, 255, 255, .78) 50%, #111 60%, #111 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            background-size: 200% auto;
            animation: shine 6s linear infinite
        }

        @keyframes shine {
            0% {
                background-position: 200% center
            }

            100% {
                background-position: -200% center
            }
        }

        /* WhatsApp widget */
        #waButton {
            position: fixed;
            bottom: 90px;
            right: 24px;
            z-index: 60
        }

        #waWidget {
            position: fixed;
            bottom: 150px;
            right: 24px;
            width: clamp(320px, 32vw, 480px);
            max-width: 94vw;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .15);
            opacity: 0;
            pointer-events: none;
            transform: translateY(8px);
            transition: opacity .25s ease, transform .25s ease;
            z-index: 70
        }

        #waWidget.show {
            opacity: 1;
            pointer-events: auto;
            transform: none
        }

        #waHeader {
            background: #25D366;
            color: #fff;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px
        }

        .wa-msg {
            background: #F0F4F7;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: .95rem;
            color: #243b53
        }

        .wa-quick {
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .85rem;
            background: #fff;
            cursor: pointer
        }

        .wa-quick:hover {
            background: #f8fafc
        }

        @media (max-width:640px) {
            #waWidget {
                bottom: 90px;
                right: 16px;
                width: min(94vw, 480px);
                border-radius: 14px
            }
        }

        /* Mobile menu overlay */
        #mobileMenu {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
            z-index: 60
        }

        #mobileMenu.show {
            opacity: 1;
            pointer-events: auto
        }

        #mobileSheet {
            position: absolute;
            top: 0;
            right: 0;
            width: min(88vw, 360px);
            height: 100%;
            background: #fff;
            transform: translateX(100%);
            transition: transform .25s ease;
            box-shadow: -10px 0 30px rgba(0, 0, 0, .16)
        }

        #mobileMenu.show #mobileSheet {
            transform: translateX(0)
        }

        @media (prefers-reduced-motion: reduce) {

            .reveal,
            .reveal-left,
            .reveal-right,
            .reveal-scale,
            .tiltable,
            .shine {
                animation: none !important;
                transition: none !important;
                transform: none !important
            }
        }
    </style>
</head>

<body class="antialiased">
    <!-- Header fijo -->
    <header class="bg-white/90 backdrop-blur border-b border-gray-200 fixed top-0 w-full z-50 text-ink">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="flex items-center gap-2" aria-label="AXM Abogados — Inicio">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[var(--accent)]" viewBox="0 0 24 24"
                    fill="currentColor" aria-hidden="true">
                    <path d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
                </svg>
                <span class="text-2xl font-black">AXM <span class="text-[var(--accent)]">ABOGADOS</span></span>
            </a>

            <!-- NAV desktop -->
            <nav class="hidden md:flex items-center gap-6" aria-label="Principal">
                <a href="#servicios" class="nav-link text-sm font-semibold">Servicios</a>
                <a href="#por-que" class="nav-link text-sm font-semibold">Acerca de nosotros</a>
                <a href="#casos" class="nav-link text-sm font-semibold">Casos</a>
                <a href="#whatsapp" class="nav-link text-sm font-semibold">WhatsApp</a>
                <a href="#faq" class="nav-link text-sm font-semibold">Preguntas</a>
                <a href="#contacto" class="nav-link text-sm font-semibold">Contacto</a>

                <!-- Abrir chatbox WPP -->
                <button id="openWaWidget" class="btn-dark px-3 py-2 rounded-lg">Abrir chat</button>

                <!-- Abrir modal -->
                <button id="openLoginDesktop" type="button" class="btn-accent px-3 py-2 rounded-lg">Ingresar</button>
            </nav>

            <!-- Hamburger -->
            <div class="flex items-center gap-3">
                <button id="hamburger"
                    class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-300"
                    aria-haspopup="dialog" aria-controls="mobileMenu" aria-expanded="false">
                    <span class="sr-only">Abrir menú</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile menu -->
    <div id="mobileMenu" aria-hidden="true">
        <div id="mobileSheet" class="p-6" role="dialog" aria-modal="true" aria-label="Menú móvil">
            <div class="flex items-center justify-between mb-6">
                <span class="text-lg font-extrabold">AXM <span class="text-[var(--accent)]">ABOGADOS</span></span>
                <button id="mobileClose"
                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-gray-300"
                    aria-label="Cerrar menú">✕</button>
            </div>
            <nav class="flex flex-col gap-3">
                <a href="#servicios" class="py-2">Servicios</a>
                <a href="#por-que" class="py-2">¿Por qué nosotros?</a>
                <a href="#casos" class="py-2">Casos de éxito</a>
                <a href="#whatsapp" class="py-2">WhatsApp</a>
                <a href="#faq" class="py-2">FAQ</a>
                <a href="#contacto" class="py-2">Contacto</a>
            </nav>
            <div class="mt-6 grid gap-3">
                <!-- Abrir chatbox WPP -->
                <button id="mobileOpenWA" class="btn-dark px-4 py-3 rounded-lg">Abrir chat</button>
                <!-- Abrir modal -->
                <button id="openLoginMobile" type="button" class="btn-accent px-3 py-2 rounded-lg">Ingresar</button>
            </div>
        </div>
    </div>

    <!-- 1) HERO — CLARO -->
    <section id="hero" class="relative overflow-hidden section-muted">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-10 items-center">
            <!-- Imagen -->
            <div class="relative group h-[340px] sm:h-[460px] lg:h-[520px] order-first reveal-left" data-delay="0"
                aria-hidden="true">
                <div id="heroImg"
                    class="absolute inset-0 rounded-2xl overflow-hidden shadow-[0_30px_60px_rgba(0,0,0,.25)] will-change-transform">
                    <img src="img/img_gifs/axmlogo.gif" alt=""
                        class="w-full h-full object-cover select-none pointer-events-none" loading="eager" />
                    <div class="absolute inset-0 bg-gradient-to-tr from-black/25 via-black/0 to-black/15"></div>
                </div>
                <span
                    class="absolute -left-6 top-6 h-24 w-24 rotate-12 bg-[var(--accent)]/80 blur-[2px] rounded-full opacity-70 animate-pulse"></span>
            </div>

            <!-- Texto -->
            <div class="relative z-10 text-left">
                <div class="inline-block bg-white/70 backdrop-blur-[2px] rounded-lg px-2">
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-5 shine reveal" data-delay="120"
                        style="color:#111">
                        ¿Tienes problemas con tu Pensión del ISSSTE?<br class="hidden sm:block" />Recupera lo que te
                        corresponde.
                    </h1>
                </div>
                <p class="text-lg text-slate-700 max-w-2xl mb-8 reveal" data-delay="220">
                    Defendemos tus derechos generados durante toda tu vida laboral. Sabemos lo frustrante que es
                    enfrentar al sistema.
                    Si te negaron, redujeron o calcularon mal la pensión, te ayudamos a corregirlo.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 reveal" data-delay="320">
                    <a href="formularios/formulario_inicio.php"
                        class="btn-accent px-6 py-3 rounded-xl shadow-wine">Quiero que revisen mi caso</a>
                    <a href="#servicios" class="btn-dark px-6 py-3 rounded-xl">Ver servicios</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 2) LOGROS — OSCURO -->
    <section id="logros" class="section-dark py-20">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-10 text-[var(--accent)] reveal-scale">Nuestros logros</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white text-ink p-8 rounded-xl shadow-soft reveal tiltable">
                    <h3 class="text-4xl font-extrabold text-[var(--accent)]">+15</h3>
                    <p class="mt-2 text-gray-600">Años de experiencia</p>
                </div>
                <div class="bg-white text-ink p-8 rounded-xl shadow-soft reveal tiltable">
                    <h3 class="text-4xl font-extrabold text-[var(--accent)]">98%</h3>
                    <p class="mt-2 text-gray-600">Casos favorables</p>
                </div>
                <div class="bg-white text-ink p-8 rounded-xl shadow-soft reveal tiltable">
                    <h3 class="text-4xl font-extrabold text-[var(--accent)]">100%</h3>
                    <p class="mt-2 text-gray-600">Satisfacción</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3) SERVICIOS — CLARO -->
    <section id="servicios" class="section-muted py-20">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold mb-10 text-[var(--primary)] text-center reveal-scale">
                Especialistas en litigios contra el ISSSTE
            </h2>

            <div class="grid md:grid-cols-3 gap-8">
                <article class="bg-white text-ink p-8 rounded-xl shadow-card reveal-right tiltable">
                    <h3 class="text-xl font-bold mb-2">Cálculo incorrecto de pensión</h3>
                    <p class="text-slate-700">Detectamos errores en años de servicio, salario base o topes. Buscamos el
                        recálculo y los retroactivos que te corresponden.</p>
                </article>
                <article class="bg-white text-ink p-8 rounded-xl shadow-card reveal-right tiltable" data-delay="120">
                    <h3 class="text-xl font-bold mb-2">Negativa de pensión</h3>
                    <p class="text-slate-700">Impugnamos resoluciones de negativas para que se reconozca tu derecho de
                        forma legal y efectiva en el régimen Décimo Transitorio.</p>
                </article>
                <article class="bg-white text-ink p-8 rounded-xl shadow-card reveal-right tiltable" data-delay="240">
                    <h3 class="text-xl font-bold mb-2">Revisamos todo tipo de pensiones</h3>
                    <p class="text-slate-700">Jubilación, Edad y tiempo, Invalidez, Viudez, Riesgo de trabajo y
                        Orfandad. Te guiamos para obtener el mejor beneficio posible.</p>
                </article>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row justify-center gap-3 reveal" data-delay="300">
                <a href="formularios/formulario_inicio.php" class="btn-accent px-6 py-3 rounded-xl shadow-wine">Quiero
                    que revisen mi caso</a>
                <a href="#faq" class="btn-dark px-6 py-3 rounded-xl">Dudas frecuentes</a>
            </div>
        </div>
    </section>

    <!-- 4) ¿POR QUÉ ELEGIRNOS? — OSCURO -->
    <section id="por-que" class="section-dark py-20">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold mb-10 text-[var(--accent)] reveal-scale">La vía rápida y segura para recuperar
                tu pensión</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/5 rounded-xl p-5 reveal tiltable">100% Virtual: atención personalizada, sin
                    traslados.</div>
                <div class="bg-white/5 rounded-xl p-5 reveal tiltable" data-delay="80">+15 años en materia de pensiones
                    ISSSTE.</div>
                <div class="bg-white/5 rounded-xl p-5 reveal tiltable" data-delay="160">Enfoque exclusivo en Derecho
                    Administrativo.</div>
                <div class="bg-white/5 rounded-xl p-5 reveal tiltable" data-delay="240">Honorarios claros y comunicación
                    constante.</div>
            </div>
        </div>
    </section>

    <!-- 5) CASOS DE ÉXITO — CLARO -->
    <section id="casos" class="section-muted py-16">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold mb-10 text-[var(--primary)] reveal-scale">Resultados reales</h2>

            <div class="grid md:grid-cols-3 gap-6">
                <article class="bg-white p-6 rounded-xl shadow-card reveal tiltable">
                    <h3 class="font-bold text-[var(--primary)] mb-2">Incremento de pensión</h3>
                    <p class="text-slate-700 text-sm">Se corrigió el cálculo de la pensión otorgada y se incrementó el
                        monto mensual.</p>
                </article>

                <article class="bg-white p-6 rounded-xl shadow-card reveal tiltable" data-delay="240">
                    <h3 class="font-bold text-[var(--primary)] mb-2">Recuperación de cuenta SAR-ISSSTE</h3>
                    <p class="text-slate-700 text-sm">Recuperación de recursos de la Afore.</p>
                </article>

                <article class="bg-white p-6 rounded-xl shadow-card reveal tiltable" data-delay="120">
                    <h3 class="font-bold text-[var(--primary)] mb-2">Negativa</h3>
                    <p class="text-slate-700 text-sm">Se reconoció un derecho que el ISSSTE no otorgó; se logró que el
                        ISSSTE reconociera mi derecho.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- 6) WHATSAPP CTA — OSCURO -->
    <section id="whatsapp" class="section-dark py-16">
        <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-2 gap-10 items-center">
            <div class="reveal-left">
                <h2 class="text-3xl font-bold mb-4 text-[var(--accent)]">¿Prefieres WhatsApp?</h2>
                <p class="text-gray-200 mb-3">Inicia una conversación por WhatsApp.</p>
                <p class="text-gray-200 mb-6">Podemos atenderte de forma personalizada.</p>
                <ul class="text-gray-300 space-y-2 mb-8">
                    <li class="flex items-start gap-2"><span>✅</span><span>Citas, consultas y revisiones de caso en
                            horario laboral</span></li>
                    <li class="flex items-start gap-2"><span>✅</span><span>Envío de documentos y seguimiento</span></li>
                    <li class="flex items-start gap-2"><span>✅</span><span>Asesoría inicial sin costo</span></li>
                </ul>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a id="waCtaBtn" href="#" target="_blank" rel="noopener" class="px-6 py-3 rounded-xl"
                        style="background:#25D366;color:#0b2816;font-weight:800">
                        <span class="block text-center">Revisar vía WhatsApp</span>
                    </a>
                    <a href="formularios/formulario_inicio.php" class="btn-accent px-6 py-3 rounded-xl shadow-wine">
                        <span class="block text-center">Quiero que revisen mi caso sin costo</span>
                    </a>
                </div>
            </div>

            <!-- Tarjetas WA -->
            <div class="bg-white rounded-2xl p-6 shadow-card reveal-right">
                <h3 class="text-xl font-bold text-[var(--primary)] mb-3">¿Qué podemos revisar por WhatsApp?</h3>
                <div id="waTopics" class="grid sm:grid-cols-2 gap-4 text-ink"><!-- JS inserta tarjetas --></div>
                <p class="text-sm text-slate-600 mt-6">*Envía fotos claras de tus documentos para una revisión ágil.</p>
            </div>
        </div>
    </section>

    <!-- 7) BLOQUE VISUAL — CLARO -->
    <section id="bloque-visual" class="section-muted py-16">
        <div class="max-w-6xl mx-auto px-6">
            <div class="relative overflow-hidden rounded-2xl shadow-card reveal-scale">
                <img src="img/img_index/sraabogadaia.jpeg" alt="Atención legal profesional"
                    class="w-full h-[360px] md:h-[460px] object-cover" />
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/20"></div>
                <div class="absolute inset-0 p-6 md:p-10 lg:p-14 flex flex-col justify-center text-white">
                    <h3 class="text-3xl md:text-4xl font-extrabold leading-tight mb-3">Tu esfuerzo no se discute.</h3>
                    <p class="max-w-2xl text-white/90 text-lg mb-6">
                        Si tu pensión fue negada, reducida o mal calculada, nuestros abogados especialistas revisarán tu
                        caso.
                        <br /><br />Obtén lo que te corresponde.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="formularios/formulario_inicio.php" class="btn-accent px-6 py-3 rounded-xl">Quiero que
                            revisen mi caso</a>
                        <a href="#servicios" class="btn-dark px-6 py-3 rounded-xl">Ver servicios</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8) FAQ — OSCURO -->
    <section id="faq" class="section-dark py-16">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="text-3xl font-bold mb-8 text-[var(--accent)] reveal-scale">Preguntas frecuentes</h2>

            <details class="bg-white rounded-xl p-5 shadow-card mb-3 reveal">
                <summary class="cursor-pointer font-semibold text-ink">¿Qué necesito para empezar?</summary>
                <div class="mt-3 text-slate-700">Talón de pago, oficio de negativa de pensión y documentos básicos de
                    identificación.</div>
            </details>

            <details class="bg-white rounded-xl p-5 shadow-card mb-3 reveal" data-delay="100">
                <summary class="cursor-pointer font-semibold text-ink">¿Cuánto tarda este procedimiento?</summary>
                <div class="mt-3 text-slate-700">Puede variar según el caso y la dependencia. Te damos un estimado tras
                    revisar tu información.</div>
            </details>

            <details class="bg-white rounded-xl p-5 shadow-card mb-3 reveal" data-delay="200">
                <summary class="cursor-pointer font-semibold text-ink">¿Atienden fuera de CDMX?</summary>
                <div class="mt-3 text-slate-700">Sí, brindamos atención virtual a todo México.</div>
            </details>
        </div>
    </section>

    <!-- 9) CONTACTO — CLARO -->
    <section id="contacto" class="section-muted py-20">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-3 text-[var(--primary)] reveal-scale">¿Estás listo para reclamar lo que es
                tuyo?</h2>
            <p class="text-slate-700 mb-8 reveal">Cuéntanos tu caso y un abogado lo revisará sin costo.</p>

            <form onsubmit="event.preventDefault();showMessage();" class="space-y-4 reveal" data-delay="140"
                aria-label="Formulario de contacto">
                <label class="sr-only" for="nombre">Nombre</label>
                <input id="nombre" class="w-full p-3 rounded-lg border border-gray-300 text-ink bg-white"
                    placeholder="Nombre Completo" required />

                <label class="sr-only" for="telefono">Teléfono</label>
                <input id="telefono" class="w-full p-3 rounded-lg border border-gray-300 text-ink bg-white"
                    placeholder="Teléfono (WhatsApp)" required />

                <label class="sr-only" for="tipoCaso">Tipo de caso</label>
                <select id="tipoCaso" class="w-full p-3 rounded-lg border border-gray-300 text-ink bg-white" required>
                    <option value="" disabled selected>Selecciona tu tipo de caso</option>
                </select>

                <label class="sr-only" for="mensaje">Mensaje</label>
                <textarea id="mensaje" rows="3"
                    class="w-full p-3 rounded-lg border border-gray-300 text-ink bg-white hidden"
                    placeholder="Cuéntanos brevemente tu caso"></textarea>

                <div id="messageBox" class="hidden bg-green-500 text-white p-3 rounded-lg text-center font-semibold">
                    Mensaje enviado correctamente</div>
                <button type="submit" class="btn-accent px-6 py-3 rounded-lg">Quiero que revisen mi caso</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-footbg text-footink pt-14">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <a href="#" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[var(--accent)]" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                        <span class="text-xl font-black text-white">AXM <span
                                class="text-[var(--accent)]">ABOGADOS</span></span>
                    </a>
                    <p class="mt-4 text-sm text-gray-300 max-w-xs">Bufete de abogados, especialistas en pensiones
                        ISSSTE. Atención virtual y personalizada.</p>
                    <div class="mt-6 flex items-center gap-3">
                        <a href="#" aria-label="Facebook"
                            class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
                            <svg class="w-5 h-5 text-footink" fill="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    d="M22 12.06C22 6.48 17.52 2 11.94 2S1.88 6.48 1.88 12.06c0 4.99 3.66 9.13 8.44 9.93v-7.02H7.9v-2.91h2.42V9.41c0-2.4 1.43-3.73 3.6-3.73 1.04 0 2.13.18 2.13.18v2.34h-1.2c-1.19 0-1.56.74-1.56 1.5v1.8h2.65l-.42 2.91h-2.23v7.02c4.78-.8 8.44-4.94 8.44-9.93z" />
                            </svg>
                        </a>
                        <a href="#" aria-label="LinkedIn"
                            class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
                            <svg class="w-5 h-5 text-footink" fill="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    d="M4.98 3.5C4.98 4.88 3.86 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1 4.98 2.12 4.98 3.5zM.5 8h4V23h-4V8zm7.5 0h3.83v2.05h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.77 2.65 4.77 6.09V23h-4v-7.03c0-1.68-.03-3.83-2.33-3.83-2.33 0-2.68 1.82-2.68 3.71V23h-4V8z" />
                            </svg>
                        </a>
                        <a href="#" aria-label="X" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
                            <svg class="w-5 h-5 text-footink" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M18.9 2H22l-7.2 8.24L23.5 22h-7.3l-5.7-7.4L3 22H0l7.8-8.9L.8 2h7.4l5.1 6.8L18.9 2z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Enlaces</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#servicios" class="hover:text-white transition">Servicios</a></li>
                        <li><a href="#por-que" class="hover:text-white transition">¿Por qué nosotros?</a></li>
                        <li><a href="#casos" class="hover:text-white transition">Casos de éxito</a></li>
                        <li><a href="#faq" class="hover:text-white transition">Preguntas frecuentes</a></li>
                        <li><a href="#contacto" class="hover:text-white transition">Contacto</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Recursos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="https://issstenet2.issste.gob.mx/gastp4/ua/r/talones/comp-pago?OutputMap=DUA_HTML5"
                                target="_blank" class="hover:text-white transition" rel="noopener">Talones de pago</a>
                        </li>
                        <li><a href="https://www.gob.mx/cms/uploads/attachment/file/27999/trip_imp_comprobante.pdf"
                                target="_blank" class="hover:text-white transition" rel="noopener">Trípticos</a></li>
                        <li><a href="https://www.gob.mx/issste/prensa/publica-issste-calendario-de-pagos-2025-a-pensionados-y-jubilados/"
                                target="_blank" class="hover:text-white transition" rel="noopener">Calendario anual</a>
                        </li>
                        <li><a href="https://www.gob.mx/issste/acciones-y-programas/tramites-del-issste" target="_blank"
                                class="hover:text-white transition" rel="noopener">Blog de pensiones</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Contacto</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[var(--accent)] mt-0.5" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M6.6 10.8c1.6 3.2 3.9 5.5 7.1 7.1l2.4-2.4c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.3 22 2 13.7 2 3c0-.6.4-1 1-1h3.2c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1L6.6 10.8z" />
                            </svg>
                            <a id="js-tel-link" href="#" class="hover:text-white transition"><span
                                    id="js-wa-display">+52 81 3219 1298</span></a>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[var(--accent)] mt-0.5" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path d="M12 13L2 6.76V18a2 2 0 002 2h16a2 2 0 002-2V6.76L12 13z" />
                                <path d="M22 6l-10 7L2 6l10-4 10 4z" />
                            </svg>
                            <span>axm.abogados09@gmail.com</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[var(--accent)] mt-0.5" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z" />
                            </svg>
                            <span>Atención virtual a todo México</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[var(--accent)] mt-0.5" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path d="M6 2h12a2 2 0 012 2v16l-4-3-4 3-4-3-4 3V4a2 2 0 012-2z" />
                            </svg>
                            <span>Lunes a Viernes · 9:00–18:00</span>
                        </li>
                    </ul>
                    <div class="mt-5">
                        <a href="#" class="inline-flex items-center gap-2 btn-accent px-4 py-2 rounded-lg js-wa-link">
                            <svg class="w-5 h-5" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M19.11 17.13c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.15-.42-2.19-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.41.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47 -.84-2.02 -.22-.53 -.45-.45 -.61-.45 -.16 0 -.34-.02 -.52-.02 -.18 0 -.48.07 -.73 .34 -.25 .27-.96 .94-.96 2.29 0 1.34 .98 2.63 1.11 2.81 .14 .18 1.93 2.95 4.74 4.15 .66 .28 1.18 .45 1.58 .57 .66 .21 1.27 .18 1.75 .11 .53 -.08 1.6 -.65 1.83 -1.29 .23 -.64 .23 -1.19 .16 -1.29-.07-.11 -.25-.18 -.52-.32z" />
                            </svg>
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 mt-12 pt-6 pb-8">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-300">&copy; <span id="yearSpan"></span> AXM ABOGADOS. Todos los derechos
                        reservados.</p>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="#" class="hover:text-white transition">Aviso de Privacidad</a>
                        <span class="text-white/20">|</span>
                        <a href="#" class="hover:text-white transition">Términos de Servicio</a>
                        <span class="text-white/20">|</span>
                        <a href="#faq" class="hover:text-white transition">FAQ</a>
                    </div>
                </div>

                <!-- NUEVA LÍNEA CENTRADA -->
                <div class="mt-4 text-center text-xs text-gray-400">
                    Sistema Desarrollado Por <span class="font-semibold">JrDevSolutions</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botón volver arriba -->
    <button id="toTop" aria-label="Volver arriba"
        class="rounded-full bg-[var(--accent)] text-white w-12 h-12 flex items-center justify-center shadow-wine">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    <!-- Botón flotante WhatsApp -->
    <button id="waButton" aria-label="Abrir chat de WhatsApp"
        class="rounded-full w-12 h-12 flex items-center justify-center shadow-lg" style="background:#25D366;color:#fff">
        <svg viewBox="0 0 32 32" class="w-6 h-6" fill="currentColor" aria-hidden="true">
            <path
                d="M19.11 17.13c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.15-.42-2.19-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.41.12-.55.12-.12.27-.32.41-.48.14-.16 .18-.27 .27-.45 .09-.18 .05-.34 -.02-.48 -.07-.14 -.61-1.47 -.84-2.02 -.22-.53 -.45-.45 -.61-.45 -.16 0 -.34-.02 -.52-.02 -.18 0 -.48.07 -.73 .34 -.25 .27-.96 .94-.96 2.29 0 1.34 .98 2.63 1.11 2.81 .14 .18 1.93 2.95 4.74 4.15 .66 .28 1.18 .45 1.58 .57 .66 .21 1.27 .18 1.75 .11 .53-.08 1.6-.65 1.83 -1.29 .23 -.64 .23 -1.19 .16 -1.29-.07-.11 -.25-.18 -.52-.32z" />
            <path
                d="M26.63 5.37A13.48 13.48 0 0016 .56C8.06 .56 1.6 7.01 1.6 14.93c0 2.48 .65 4.9 1.88 7.04L1 30.4l8.66-2.28a14.83 14.83 0 007.34 1.96h.01c7.93 0 14.38-6.46 14.38-14.39 0-3.84-1.49-7.45-4.16-10.32z" />
        </svg>
    </button>

    <!-- Chatbox WhatsApp -->
    <div id="waWidget" role="dialog" aria-modal="true" aria-labelledby="waTitle" aria-hidden="true">
        <div id="waHeader" class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/20">
                    <svg viewBox="0 0 32 32" class="w-5 h-5 text-white" fill="currentColor" aria-hidden="true">
                        <path
                            d="M19.11 17.13c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.15-.42-2.19-1.34-.81-.72-1.36-1.61-1.52-1.88-.16-.27-.02-.41.12-.55.12-.12.27-.32.41-.48.14-.16 .18-.27 .27-.45 .09-.18 .05-.34 -.02-.48 -.07-.14 -.61-1.47 -.84-2.02 -.22-.53 -.45-.45 -.61-.45 -.16 0 -.34-.02 -.52-.02 -.18 0 -.48.07 -.73 .34 -.25 .27-.96 .94-.96 2.29 0 1.34 .98 2.63 1.11 2.81 .14 .18 1.93 2.95 4.74 4.15 .66 .28 1.18 .45 1.58 .57 .66 .21 1.27 .18 1.75 .11 .53 -.08 1.6 -.65 1.83 -1.29 .23 -.64 .23 -1.19 .16 -1.29-.07-.11 -.25-.18 -.52-.32z" />
                        <path
                            d="M26.63 5.37A13.48 13.48 0 0016 .56C8.06 .56 1.6 7.01 1.6 14.93c0 2.48 .65 4.9 1.88 7.04L1 30.4l8.66-2.28a14.83 14.83 0 007.34 1.96h.01c7.93 0 14.38-6.46 14.38-14.39 0-3.84-1.49-7.45-4.16-10.32z" />
                    </svg>
                </span>
                <div>
                    <h3 id="waTitle" class="font-bold">WhatsApp · AXM ABOGADOS</h3>
                    <p class="text-xs text-white/90">Hola! soy tu asistente virtual de AXM ABOGADOS</p>
                </div>
            </div>
            <button id="waClose" aria-label="Cerrar" class="text-white/90 hover:text-white">&times;</button>
        </div>

        <div class="px-4 py-3 space-y-3">
            <div class="wa-msg">Hola 👋 ¿En qué podemos ayudarte hoy?</div>
            <div id="waQuickContainer" class="flex flex-wrap gap-2"><!-- JS inserta quick buttons --></div>
        </div>

        <div class="border-top p-3 bg-[#F8FAFB] rounded-b-2xl" style="border-top:1px solid #e5e7eb;">
            <div class="flex gap-2">
                <label for="waInput" class="sr-only">Escribe tu mensaje</label>
                <input id="waInput" type="text" class="flex-1 rounded-lg border border-gray-300 px-3 py-2"
                    placeholder="Escribe tu mensaje..." />
                <button id="waSend" class="px-4 py-2 rounded-lg font-semibold text-white"
                    style="background:#25D366;">Enviar</button>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">Se abrirá WhatsApp con tu mensaje prellenado.</p>
        </div>
    </div>

    <!-- Login Dialog -->
    <dialog id="loginDialog"
        class="backdrop:bg-black/40 rounded-2xl p-0 w-full max-w-md open:animate-[fadeIn_.2s_ease]">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-[var(--accent)] text-white font-bold">AXM</span>
                <h3 id="loginTitle" class="text-lg font-bold">Ingresa a tu cuenta</h3>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-800" data-close-login
                aria-label="Cerrar">✕</button>
        </div>

        <!-- Body -->
        <form id="loginForm" class="px-5 pt-5 pb-3 space-y-4" aria-labelledby="loginTitle" novalidate>
            <div id="loginError"
                class="hidden rounded-lg border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm" role="alert"
                aria-live="assertive"></div>

            <div class="space-y-1.5">
                <label for="loginEmail" class="text-sm font-semibold text-gray-700">Correo electrónico o usuario</label>
                <input id="loginEmail" type="text" autocomplete="username" required
                    class="w-full p-3 rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[var(--accent)]" />
            </div>
            <div class="space-y-1.5">
                <label for="loginPass" class="text-sm font-semibold text-gray-700">Contraseña</label>
                <input id="loginPass" type="password" autocomplete="current-password" required
                    class="w-full p-3 rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-[var(--accent)]" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2">
                    <input id="rememberMe" type="checkbox" class="rounded border-gray-300" />
                    <span>Recordarme</span>
                </label>
                <a href="/login/recuperar" class="text-[var(--accent)] hover:underline">¿Olvidaste tu contraseña?</a>
            </div>

            <button id="loginSubmit" type="submit"
                class="btn-accent w-full py-3 rounded-lg inline-flex items-center justify-center gap-2">
                <svg id="loginSpinner" class="hidden animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none"
                    aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span id="loginBtnText">Entrar</span>
            </button>

            <p class="text-sm text-center text-gray-500">
                ¿Prefieres la página completa? <a href="../axm/login/" class="text-[var(--accent)] hover:underline">Ir a
                    login</a>
            </p>
        </form>

        <!-- Footer -->
        <div class="px-5 pb-5">
            <button type="button" class="w-full py-2 rounded-lg border border-gray-300 hover:bg-gray-50"
                data-close-login>Cancelar</button>
        </div>
    </dialog>

    <!-- JSON-LD para FAQ (SEO) -->
    <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"FAQPage",
    "mainEntity":[
      {"@type":"Question","name":"¿Qué necesito para empezar?","acceptedAnswer":{"@type":"Answer","text":"Talón de pago, oficio de negativa de pensión e identificación."}},
      {"@type":"Question","name":"¿Cuánto tarda este procedimiento?","acceptedAnswer":{"@type":"Answer","text":"Depende del caso y la dependencia. Damos estimado en la valoración gratuita."}},
      {"@type":"Question","name":"¿Atienden fuera de CDMX?","acceptedAnswer":{"@type":"Answer","text":"Sí. Atención virtual a todo México."}}
    ]
  }
  </script>

    <!-- JS -->
    <script>
        // ====== ENDPOINTS ======
        const LOGIN_ENDPOINT = '../php/login.php'; // ajusta si tu ruta es distinta

        // ====== CONFIG ÚNICA (WhatsApp) ======
        const WA_PHONE = '5218132191298';  // <--- CAMBIA AQUÍ tu número (E.164 sin signos)
        const WA_DISPLAY = '+52 81 3219 1298';
        const WA_BASE = 'https://wa.me/';
        const DEFAULT_MSG = 'Hola, quiero asesoría sobre mi pensión ISSSTE (sitio web).';
        const waUrl = (text) => `${WA_BASE}${WA_PHONE}?text=${encodeURIComponent(text || DEFAULT_MSG)}`;

        // ====== Tipos de caso (para SELECT del formulario)
        const CASE_TYPES = [
            { id: 'cesantia', label: 'Edad y tiempo' },
            { id: 'vejez', label: 'Vejez' },
            { id: 'invalidez', label: 'Invalidez (Enfermedad general)' },
            { id: 'riesgo', label: 'Riesgo de Trabajo (incapacidad)' },
            { id: 'sobrevivencia', label: 'Pensión de Sobrevivencia (viudez/orfandad/ascendientes)' },
            { id: 'decimo', label: 'Régimen Décimo Transitorio (jubilación/retiro por edad y tiempo)' },
            { id: 'cuenta', label: 'Régimen de Cuentas Individuales' },
            { id: 'negativa', label: 'Negativa de pensión' },
            { id: 'calculo', label: 'Incremento de la pensión / diferencias / retroactivos' },
            { id: 'todo', label: 'Todo tipo de pensiones' },
            { id: 'otro', label: 'Otro (especificar)' }
        ];

        // SELECT (sin "todo")
        const tipoCaso = document.getElementById('tipoCaso');
        CASE_TYPES.forEach(t => {
            if (t.id !== 'todo') {
                const opt = document.createElement('option');
                opt.value = t.id; opt.textContent = t.label;
                tipoCaso.appendChild(opt);
            }
        });

        // Subconjunto para WA (sin 'sobrevivencia'; con 'todo')
        const WA_IDS = ['negativa', 'calculo', 'cesantia', 'vejez', 'invalidez', 'todo'];

        // Tarjetas del apartado WhatsApp
        const waTopics = document.getElementById('waTopics');
        WA_IDS.forEach(id => {
            const t = CASE_TYPES.find(x => x.id === id);
            if (!t) return;
            const div = document.createElement('div');
            div.className = 'p-4 rounded-lg border border-gray-200';
            div.textContent = t.label;
            waTopics.appendChild(div);
        });

        // Quick buttons del chat
        const waQuick = document.getElementById('waQuickContainer');
        WA_IDS.forEach(id => {
            const t = CASE_TYPES.find(x => x.id === id);
            if (!t) return;
            const b = document.createElement('button');
            b.className = 'wa-quick';
            b.dataset.msg = `Consulta sobre: ${t.label}`;
            b.textContent = t.label;
            waQuick.appendChild(b);
        });

        // ====== Scroll reveal
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const delay = +e.target.dataset.delay || 0;
                    setTimeout(() => e.target.classList.add('inview'), delay);
                    revealObserver.unobserve(e.target);
                }
            })
        }, { threshold: .12 });
        document.querySelectorAll('.reveal,.reveal-left,.reveal-right,.reveal-scale').forEach(el => revealObserver.observe(el));

        // ====== Parallax en imagen del hero
        const heroImg = document.getElementById('heroImg');
        if (heroImg) {
            const damp = 20;
            heroImg.parentElement.addEventListener('mousemove', (e) => {
                const r = heroImg.getBoundingClientRect();
                const cx = (e.clientX - r.left) / r.width - 0.5;
                const cy = (e.clientY - r.top) / r.height - 0.5;
                heroImg.style.transform = `translate3d(${cx * -damp}px, ${cy * -damp}px, 0) scale(1.03)`;
            });
            heroImg.parentElement.addEventListener('mouseleave', () => {
                heroImg.style.transform = 'translate3d(0,0,0) scale(1)';
            });
        }

        // ====== Tilt en tarjetas
        document.querySelectorAll('.tiltable').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const r = card.getBoundingClientRect();
                const rx = ((e.clientY - r.top) / r.height - .5) * -6;
                const ry = ((e.clientX - r.left) / r.width - .5) * 6;
                card.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) translateZ(2px)`;
            });
            card.addEventListener('mouseleave', () => { card.style.transform = 'none'; });
        });

        // ====== Header offset dinámico
        const header = document.querySelector('header');
        function updateHeaderOffset() {
            if (!header) return;
            const rect = header.getBoundingClientRect();
            document.documentElement.style.setProperty('--header-offset', rect.height + 'px');
        }
        window.addEventListener('load', updateHeaderOffset);
        window.addEventListener('resize', updateHeaderOffset);
        setTimeout(updateHeaderOffset, 60);

        // ====== Navbar shrink + botón toTop
        const toTop = document.getElementById('toTop');
        const hero = document.getElementById('hero');
        const heroEnd = () => hero ? (hero.offsetTop + hero.offsetHeight) : 300;

        function onScroll() {
            if (window.scrollY > 10) header.classList.add('nav-shrink'); else header.classList.remove('nav-shrink');
            if (window.scrollY > heroEnd() - 120) { toTop.classList.add('show'); } else { toTop.classList.remove('show'); }
        }
        window.addEventListener('scroll', () => {
            onScroll();
            if (window.__hdrTO) return;
            window.__hdrTO = setTimeout(() => { updateHeaderOffset(); window.__hdrTO = null; }, 100);
        }, { passive: true });
        onScroll();

        toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

        // ====== Scroll suave con offset
        function smoothScrollTo(id) {
            const el = document.querySelector(id);
            if (!el) return;
            const top = el.getBoundingClientRect().top + window.scrollY - (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-offset')) || 0) + 4;
            window.scrollTo({ top, behavior: 'smooth' });
        }
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', (e) => {
                const href = a.getAttribute('href');
                if (href && href.startsWith('#') && href.length > 1) {
                    e.preventDefault();
                    smoothScrollTo(href);
                    mobileMenu?.classList.remove('show');
                }
            });
        });

        // ====== Scrollspy
        const sections = ['hero', 'servicios', 'por-que', 'casos', 'whatsapp', 'bloque-visual', 'faq', 'contacto']
            .map(id => document.getElementById(id))
            .filter(Boolean);
        const navLinks = Array.from(document.querySelectorAll('.nav-link'));
        const spyObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    navLinks.forEach(link => {
                        const href = link.getAttribute('href') || '';
                        const match = href.replace('#', '') === id;
                        link.classList.toggle('active', match);
                        if (match) { link.setAttribute('aria-current', 'page'); } else { link.removeAttribute('aria-current'); }
                    });
                }
            });
        }, { rootMargin: '-45% 0px -45% 0px', threshold: 0.01 });
        sections.forEach(sec => spyObserver.observe(sec));

        // ====== Mock submit contacto
        function showMessage() {
            const m = document.getElementById('messageBox');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('hidden'), 4000);
            ['nombre', 'telefono', 'mensaje'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('tipoCaso').selectedIndex = 0;
            document.getElementById('mensaje').classList.add('hidden');
        }
        window.showMessage = showMessage;

        // ====== SELECT -> textarea si "Otro"
        const msgField = document.getElementById('mensaje');
        tipoCaso.addEventListener('change', () => {
            if (tipoCaso.value === 'otro') {
                msgField.classList.remove('hidden');
                msgField.focus();
            } else {
                msgField.classList.add('hidden');
            }
        });

        // ====== WhatsApp Widget
        const waBtn = document.getElementById('waButton');
        const waBox = document.getElementById('waWidget');
        const waClose = document.getElementById('waClose');
        const waSend = document.getElementById('waSend');
        const waInput = document.getElementById('waInput');
        const waCtaBtn = document.getElementById('waCtaBtn');
        const openWaWidgetBtn = document.getElementById('openWaWidget');
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileClose = document.getElementById('mobileClose');
        const mobileOpenWA = document.getElementById('mobileOpenWA');

        function openWAWidget() { waBox.classList.add('show'); waInput && waInput.focus(); }
        function closeWAWidget() { waBox.classList.remove('show'); }

        waBtn.addEventListener('click', openWAWidget);
        if (openWaWidgetBtn) openWaWidgetBtn.addEventListener('click', openWAWidget);
        waClose.addEventListener('click', closeWAWidget);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeWAWidget(); });

        // Quick buttons -> input
        document.getElementById('waQuickContainer').addEventListener('click', (e) => {
            const btn = e.target.closest('.wa-quick');
            if (!btn || !waInput) return;
            waInput.value = btn.dataset.msg || '';
            waInput.focus();
        });

        const sendWA = (msg) => window.open(waUrl(msg), '_blank', 'noopener');
        waSend.addEventListener('click', () => {
            const val = (waInput?.value || '').trim();
            sendWA(val || DEFAULT_MSG);
        });
        waInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); waSend.click(); } });

        // ====== Enlaces dinámicos y año
        document.addEventListener('DOMContentLoaded', () => {
            if (waCtaBtn) { waCtaBtn.href = waUrl(DEFAULT_MSG); }
            document.querySelectorAll('.js-wa-link').forEach(a => {
                a.href = waUrl(DEFAULT_MSG);
                a.setAttribute('target', '_blank');
                a.setAttribute('rel', 'noopener');
            });
            const telLink = document.getElementById('js-tel-link');
            const telText = document.getElementById('js-wa-display');
            if (telText) telText.textContent = WA_DISPLAY;
            if (telLink) telLink.href = `tel:+${WA_PHONE}`;
            const yearSpan = document.getElementById('yearSpan');
            if (yearSpan) yearSpan.textContent = new Date().getFullYear();
        });

        // ====== Mobile menu
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                mobileMenu.classList.add('show');
                hamburger.setAttribute('aria-expanded', 'true');
            });
        }
        if (mobileClose) {
            mobileClose.addEventListener('click', () => {
                mobileMenu.classList.remove('show');
                hamburger.setAttribute('aria-expanded', 'false');
            });
        }
        if (mobileOpenWA) {
            mobileOpenWA.addEventListener('click', () => {
                mobileMenu.classList.remove('show');
                openWAWidget();
            });
        }
        mobileMenu?.addEventListener('click', (e) => {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('show');
                hamburger?.setAttribute('aria-expanded', 'false');
            }
        });

        // ===== Login Dialog =====
        const loginDialog = document.getElementById('loginDialog');
        const openLoginDesktop = document.getElementById('openLoginDesktop');
        const openLoginMobile = document.getElementById('openLoginMobile');
        const loginForm = document.getElementById('loginForm');
        const loginError = document.getElementById('loginError');
        const loginSubmit = document.getElementById('loginSubmit');
        const loginSpinner = document.getElementById('loginSpinner');
        const loginBtnText = document.getElementById('loginBtnText');

        function openLogin() {
            if (!loginDialog) return;
            loginDialog.showModal();
            document.documentElement.classList.add('overflow-hidden');
            const email = document.getElementById('loginEmail');
            setTimeout(() => email?.focus(), 0);
        }
        function closeLogin() {
            if (!loginDialog) return;
            loginDialog.close();
            document.documentElement.classList.remove('overflow-hidden');
        }

        openLoginDesktop?.addEventListener('click', openLogin);
        openLoginMobile?.addEventListener('click', () => {
            mobileMenu?.classList.remove('show');
            hamburger?.setAttribute('aria-expanded', 'false');
            openLogin();
        });

        // Cerrar por botones con data-close-login
        loginDialog?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-close-login]');
            if (btn) closeLogin();
        });

        // Cerrar por click en backdrop
        loginDialog?.addEventListener('mousedown', (e) => {
            const rect = loginDialog.getBoundingClientRect();
            const clickedInDialog = (
                e.clientX >= rect.left && e.clientX <= rect.right &&
                e.clientY >= rect.top && e.clientY <= rect.bottom
            );
            if (!clickedInDialog) closeLogin();
        });

        // Limpiar overflow al cerrar
        loginDialog?.addEventListener('close', () => {
            document.documentElement.classList.remove('overflow-hidden');
        });

        // Focus trap sencillo
        loginDialog?.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;
            const focusables = loginDialog.querySelectorAll('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])');
            const f = Array.from(focusables).filter(el => !el.hasAttribute('disabled'));
            if (!f.length) return;
            const first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
            else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
        });

        // ====== Login (AJAX) ======
        function setLoading(state) {
            if (!loginSubmit) return;
            loginSubmit.disabled = state;
            loginSpinner.classList.toggle('hidden', !state);
            loginBtnText.textContent = state ? 'Entrando...' : 'Entrar';
        }
        function showLoginError(msg) {
            if (!loginError) return;
            loginError.textContent = msg || 'No se pudo iniciar sesión.';
            loginError.classList.remove('hidden');
        }
        function clearLoginError() {
            if (!loginError) return;
            loginError.textContent = '';
            loginError.classList.add('hidden');
        }

        loginForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearLoginError();

            const username = document.getElementById('loginEmail')?.value?.trim();
            const password = document.getElementById('loginPass')?.value || '';
            const remember = document.getElementById('rememberMe')?.checked || false;

            if (!username || !password) {
                showLoginError('Completa usuario y contraseña.');
                return;
            }

            try {
                setLoading(true);
                const r = await fetch(LOGIN_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ username, password, remember })
                });

                // Si el backend respondió JSON (nuestro login.php lo hace)
                const ct = r.headers.get('Content-Type') || '';
                if (ct.includes('application/json')) {
                    const data = await r.json();
                    if (!data.ok) {
                        showLoginError(data.error || 'Credenciales inválidas.');
                        setLoading(false);
                        return;
                    }
                    // Éxito: cerrar modal y recargar
                    closeLogin();
                    window.location.reload();
                    return;
                }

                // Si por alguna razón no vino JSON (fallback): redirigir a home
                if (r.ok) {
                    closeLogin();
                    window.location.href = '/';
                } else {
                    showLoginError('No se pudo iniciar sesión.');
                    setLoading(false);
                }
            } catch (err) {
                showLoginError('Error de red, intenta de nuevo.');
                setLoading(false);
            }
        });
    </script>
</body>

</html>