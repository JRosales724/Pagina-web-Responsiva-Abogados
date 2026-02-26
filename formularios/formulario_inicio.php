<?php
// formularios/formulario_inicio.php
session_start();
// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf_token'];
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AXM ABOGADOS — Asistente de Pensión (Formulario por pasos)</title>

    <!-- CSRF -->
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>">

    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap"
        rel="stylesheet" />

    <style>
        :root {
            --accent: #8A1538;
            --accent-700: #6E102C;
            --primary: #3A3A3A;
            --base: #F5F7FA;
            --paper: #FFFFFF;
            --ink: #1B1B1B;
            --progress-bg: #eef2f6;
            --progress-bar: #8A1538;
        }

        html,
        body {
            height: 100%
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            font-size: 18px;
            background:
                radial-gradient(1200px 700px at 12% 8%, #ffffff 0, #f7f9fc 40%, #eef3f8 100%), var(--base);
        }

        .wizard-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
        }

        .wizard-card {
            width: 100%;
            max-width: 1100px;
            background: var(--paper);
            border: 1px solid #eaeef3;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 22px 58px rgba(0, 0, 0, .10);
        }

        .wizard-header {
            border-bottom: 1px solid #eef2f6;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #ffffff 0, #fbfcfe 100%);
        }

        .brand {
            display: flex;
            align-items: center;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-700));
            color: #fff;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: .65rem;
            box-shadow: 0 6px 16px rgba(138, 21, 56, .35);
            letter-spacing: .5px
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: .2px;
            color: var(--primary);
            font-size: 1.15rem
        }

        .brand-sub {
            color: #64748b;
            font-weight: 600
        }

        .wizard-body {
            padding: 1.75rem 1.75rem 1.25rem;
        }

        .progress {
            height: 20px;
            background: var(--progress-bg);
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar {
            background: var(--progress-bar);
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .step {
            display: none;
            animation: fadeIn .25s ease-out
        }

        .step.active {
            display: block
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .btn {
            padding: .75rem 1.25rem;
            font-size: 1.05rem;
            border-radius: 12px
        }

        .btn-accent {
            background: var(--accent);
            border-color: var(--accent-700);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(138, 21, 56, .20)
        }

        .btn-outline-accent {
            color: var(--accent);
            border-color: var(--accent);
            font-weight: 700
        }

        .btn-dark {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            font-weight: 800
        }

        .list-group-item-action {
            border-radius: 12px;
            border: 1px solid #e8eef4;
            transition: box-shadow .15s ease, border-color .15s ease, transform .15s ease;
            font-size: 1.05rem;
            padding: .9rem 1.1rem;
            background: #fff
        }

        .list-group-item-action:hover {
            border-color: var(--accent);
            box-shadow: 0 10px 20px rgba(138, 21, 56, .12);
            transform: translateY(-1px)
        }

        label.form-label,
        .form-label {
            font-weight: 700;
            color: var(--primary)
        }

        .form-control {
            border-radius: 12px;
            border-color: #dfe6ee;
            height: calc(2.8rem + 2px);
            font-size: 1.05rem;
            transition: border-color .15s ease, box-shadow .15s ease
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 .2rem rgba(138, 21, 56, .15)
        }

        .result-box {
            border: 1px solid #e6ecf2;
            border-left: 6px solid var(--accent);
            background: #fff;
            padding: 1.25rem;
            border-radius: 12px;
            font-size: 1.05rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .04)
        }

        .result-bad {
            border-left-color: #C4583C;
            background: #fff7f5;
            border-color: #f3d1c8
        }

        .result-warn {
            border-left-color: #E7A23B;
            background: #fff9ee;
            border-color: #f4e1ba
        }

        .result-neutral {
            border-left-color: #8C7862;
            background: #fbf8f3;
            border-color: #e8dccb
        }

        .step-footer {
            margin-top: 1.5rem;
            display: flex;
            gap: .75rem;
            flex-wrap: wrap
        }

        .footer-muted {
            color: #94a3b8;
            font-size: 1rem
        }

        @media (max-width: 575px) {
            .brand-title {
                font-size: 1rem
            }

            .form-control {
                height: calc(2.2rem + 2px)
            }
        }
    </style>
</head>

<body>
    <div class="wizard-shell">
        <div class="wizard-card reveal-scale">
            <div class="wizard-header">
                <div class="brand">
                    <div class="brand-mark">AXM</div>
                    <div>
                        <div class="brand-title">AXM ABOGADOS — Asistente de Pensión</div>
                        <small class="brand-sub">Formulario por pasos</small>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <a href="../" class="btn btn-outline-secondary mr-2" title="Regresar al inicio">Inicio</a>
                    <small class="text-muted d-none d-sm-inline">Atención profesional</small>
                </div>
            </div>

            <div class="wizard-body">
                <!-- Progreso -->
                <div class="progress mb-4" role="progressbar" aria-label="Progreso" aria-valuemin="0"
                    aria-valuemax="100">
                    <div id="progressBar" class="progress-bar" style="width: 8%">Paso 1</div>
                </div>

                <!-- PASO 1 -->
                <div id="step_inicio" class="step active" data-step="1" aria-current="step">
                    <h2 class="mb-3">¿Eres pensionado federal?</h2>
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_tipo_tramite"
                            data-record="pensionado_federal:si">Sí</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="step_trabajador_activo"
                            data-record="pensionado_federal:no">No</button>
                    </div>
                    <div class="step-footer"></div>
                </div>

                <!-- PASO 2A (Pensionado) -->
                <div id="step_tipo_tramite" class="step" data-step="2">
                    <h2 class="mb-3">Selecciona el tipo de trámite que deseas realizar o revisar</h2>
                    <div class="list-group mb-3">
                        <button type="button" class="list-group-item list-group-item-action mb-2 tiltable"
                            data-next="step_mensualidad_incremento" data-record="tipo_tramite:incremento">Incremento de
                            pensión</button>
                        <button type="button" class="list-group-item list-group-item-action mb-2 tiltable"
                            data-next="step_mensualidad_jub_inv" data-record="tipo_tramite:jubilacion_invalidez">Pensión
                            por Jubilación / Edad y Tiempo / Invalidez</button>
                        <button type="button" class="list-group-item list-group-item-action mb-2 tiltable"
                            data-next="step_rt_modalidad" data-record="tipo_tramite:riesgo_trabajo">Pensión por Riesgo
                            de Trabajo</button>
                        <button type="button" class="list-group-item list-group-item-action tiltable"
                            data-next="step_cancelada" data-record="tipo_tramite:cancelada_no_pagan">Te cancelaron la
                            pensión o no te la pagan</button>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                    </div>
                </div>

                <!-- PASO 2B (No pensionado: trabajador activo) -->
                <div id="step_trabajador_activo" class="step" data-step="2">
                    <h2 class="mb-3">¿Eres trabajador activo de la federal?</h2>
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_negaron_pension"
                            data-record="trabajador_activo:si">Sí</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="end_no_ayuda"
                            data-record="trabajador_activo:no">No</button>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-link p-0" data-next="step_cambio_regimen_intro"
                            data-record="ruta:cambio_regimen">Quiero cambiar de régimen pensionario (Cuentas
                            individuales ⇄ Décimo Transitorio)</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <!-- Cambio de régimen -->
                <div id="step_cambio_regimen_intro" class="step" data-step="3">
                    <h2 class="mb-2">Cambio de régimen pensionario</h2>
                    <p class="text-muted">Validaremos requisitos básicos según tu diagrama.</p>
                    <div class="d-flex flex-wrap">
                        <button type="button" class="btn btn-dark mr-2 mb-2" data-next="step_cambio_regimen_q1"
                            data-record="cambio_regimen:si">Continuar</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_cambio_regimen_q1" class="step" data-step="4">
                    <h3 class="mb-3">¿Trabajaste antes del 2007?</h3>
                    <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_cambio_regimen_q2"
                        data-record="cambio_regimen_pre2007:si">Sí</button>
                    <button type="button" class="btn btn-outline-accent mb-2" data-next="end_no_ayuda"
                        data-record="cambio_regimen_pre2007:no">No</button>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_cambio_regimen_q2" class="step" data-step="5">
                    <h3 class="mb-3">¿Has trabajado de forma continua?</h3>
                    <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_cambio_regimen_q3"
                        data-record="cambio_regimen_continua:si">Sí</button>
                    <button type="button" class="btn btn-outline-accent mb-2" data-next="step_cambio_regimen_q3"
                        data-record="cambio_regimen_continua:no">No</button>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_cambio_regimen_q3" class="step" data-step="6">
                    <h3 class="mb-3">¿Reingresaste después del 2007?</h3>
                    <button type="button" class="btn btn-outline-accent mr-2 mb-2" data-next="end_no_ayuda"
                        data-record="cambio_regimen_reingreso_2007:si">Sí</button>
                    <button type="button" class="btn btn-accent mb-2" data-next="step_cambio_regimen_q4"
                        data-record="cambio_regimen_reingreso_2007:no">No</button>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_cambio_regimen_q4" class="step" data-step="7">
                    <h3 class="mb-3">¿Elegiste Cuentas Individuales?</h3>
                    <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_cambio_regimen_q5"
                        data-record="cambio_regimen_eligio_cuent_indivi:si">Sí</button>
                    <button type="button" class="btn btn-outline-accent mb-2" data-next="step_cambio_regimen_q5"
                        data-record="cambio_regimen_eligio_cuent_indivi:no">No</button>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_cambio_regimen_q5" class="step" data-step="8">
                    <h3 class="mb-3">¿Empezaste a laborar después del 2007?</h3>
                    <button type="button" class="btn btn-outline-accent mr-2 mb-2" data-next="end_no_ayuda"
                        data-record="cambio_regimen_post2007:si">Sí</button>
                    <button type="button" class="btn btn-accent mb-2" data-next="end_posible_elegible"
                        data-record="cambio_regimen_post2007:no">No</button>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <!-- Incremento de pensión -->
                <div id="step_mensualidad_incremento" class="step" data-step="3">
                    <h2 class="mb-2">Incremento de pensión</h2>
                    <label class="form-label">Señala cuál es tu mensualidad</label>
                    <div class="form-row">
                        <div class="col-sm-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="incre_pensi_rango" id="incre_r1"
                                    value="5k-10k">
                                <label class="custom-control-label" for="incre_r1">5,000 a 10,000</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="incre_pensi_rango" id="incre_r2"
                                    value="11k-20k">
                                <label class="custom-control-label" for="incre_r2">11,000 a 20,000</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="incre_pensi_rango" id="incre_r3"
                                    value="21k-30k">
                                <label class="custom-control-label" for="incre_r3">21,000 a 30,000</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="incre_pensi_rango" id="incre_r4"
                                    value=">31k">
                                <label class="custom-control-label" for="incre_r4">Más de 31,000</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label">Agregar el Importe Mensual (concepto 01 del talón)</label>
                        <input id="incre_pensi_importe" type="number" min="0" class="form-control"
                            placeholder="Ej. 27500" />
                        <small class="form-text">Se usa para evaluar contra el tope salarial vigente.</small>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-dark" id="btnIncContinuar">Evaluar</button>
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                    </div>
                </div>

                <!-- Jubilación / Invalidez -->
                <div id="step_mensualidad_jub_inv" class="step" data-step="3">
                    <h2 class="mb-2">Pensión por Jubilación / Edad y Tiempo / Invalidez</h2>
                    <label class="form-label">Señala cuál es tu mensualidad</label>
                    <div class="form-row">
                        <div class="col-sm-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="jubil_invalid_rango" id="ji_r1"
                                    value="5k-10k">
                                <label class="custom-control-label" for="ji_r1">5,000 a 10,000</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="jubil_invalid_rango" id="ji_r2"
                                    value="11k-20k">
                                <label class="custom-control-label" for="ji_r2">11,000 a 20,000</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="jubil_invalid_rango" id="ji_r3"
                                    value="21k-30k">
                                <label class="custom-control-label" for="ji_r3">21,000 a 30,000</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="jubil_invalid_rango" id="ji_r4"
                                    value=">31k">
                                <label class="custom-control-label" for="ji_r4">Más de 31,000</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label class="form-label">Agregar el Importe Mensual (concepto 01 del talón)</label>
                        <input id="jubil_invalid_importe" type="number" min="0" class="form-control"
                            placeholder="Ej. 27500" />
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-dark" id="btnJiContinuar">Evaluar</button>
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                    </div>
                </div>

                <!-- Riesgo de Trabajo: elección parcial/total -->
                <div id="step_rt_modalidad" class="step" data-step="3">
                    <h2 class="mb-2">Riesgo de Trabajo</h2>
                    <p class="text-muted">Selecciona tu situación</p>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_rt_parcial"
                            data-record="riesgo_trab_modalidad:parcial">Incapacidad Parcial</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="step_rt_total"
                            data-record="riesgo_trab_modalidad:total">Incapacidad Total</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <!-- RT Parcial -->
                <div id="step_rt_parcial" class="step" data-step="4">
                    <h3 class="mb-2">Incapacidad Parcial — Detalles</h3>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Fecha de inicio de pensión</label>
                            <input id="riesgo_trab_parcial_fecha" type="date" class="form-control" />
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Porcentaje de la pensión (ej. 40%)</label>
                            <input id="riesgo_trab_parcial_porcentaje" type="text" class="form-control"
                                placeholder="Ej. 40%" />
                        </div>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-dark" id="btnRtParcialContinuar">Continuar</button>
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                    </div>
                </div>

                <!-- RT Total -->
                <div id="step_rt_total" class="step" data-step="4">
                    <h3 class="mb-2">Incapacidad Total — Mensualidad y fecha</h3>
                    <div class="form-row">
                        <div class="col-sm-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="riesgo_trab_rango" id="rt_r1"
                                    value="5k-10k">
                                <label class="custom-control-label" for="rt_r1">5,000 a 10,000</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="riesgo_trab_rango" id="rt_r2"
                                    value="11k-20k">
                                <label class="custom-control-label" for="rt_r2">11,000 a 20,000</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="riesgo_trab_rango" id="rt_r3"
                                    value="21k-30k">
                                <label class="custom-control-label" for="rt_r3">21,000 a 30,000</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" name="riesgo_trab_rango" id="rt_r4"
                                    value=">30k">
                                <label class="custom-control-label" for="rt_r4">Más de 30,000</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label class="form-label">Agregar el Importe Mensual (concepto 01 del talón)</label>
                        <input id="riesgo_trab_importe" type="number" min="0" class="form-control"
                            placeholder="Ej. 27500" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fecha de inicio de pensión</label>
                        <input id="riesgo_trab_total_fecha" type="date" class="form-control" />
                    </div>

                    <div class="step-footer">
                        <button type="button" class="btn btn-dark" id="btnRtTotalContinuar">Continuar</button>
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                    </div>
                </div>

                <!-- Cancelada/no pagan -->
                <div id="step_cancelada" class="step" data-step="3">
                    <h2 class="mb-2">¿El descuento está en tu talón de pago?</h2>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-accent mr-2 mb-2" data-next="end_descontado_primer_pago"
                            data-record="descuento_en_talon:si">Sí</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="end_sin_descuento"
                            data-record="descuento_en_talon:no">No</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <!-- Negaron pensión -->
                <div id="step_negaron_pension" class="step" data-step="3">
                    <h2 class="mb-2">¿Te negaron una pensión?</h2>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_tipo_pension_negada"
                            data-record="negaron_pension:si">Sí</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="end_no_ayuda"
                            data-record="negaron_pension:no">No</button>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-link p-0" data-next="step_cambio_regimen_intro"
                            data-record="ruta:cambio_regimen">Quiero intentar cambio de régimen</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_tipo_pension_negada" class="step" data-step="4">
                    <h3 class="mb-2">Señala el tipo de pensión que te negaron</h3>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-outline-accent mr-2 mb-2" data-next="end_en_revision"
                            data-record="tipo_pension_negada:viudez">Viudez</button>
                        <button type="button" class="btn btn-outline-accent mr-2 mb-2" data-next="step_rt_dictamen"
                            data-record="tipo_pension_negada:riesgo">Riesgo de Trabajo</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="step_inv_dictamen"
                            data-record="tipo_pension_negada:invalidez">Invalidez</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_rt_dictamen" class="step" data-step="5">
                    <h3 class="mb-2">Riesgo de trabajo — ¿Tienes dictamen?</h3>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-accent mr-2 mb-2" data-next="step_rt_dictamen_estado"
                            data-record="riesgo_trab_dictamen:si">Sí</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="end_no_ayuda"
                            data-record="riesgo_trab_dictamen:no">No</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_rt_dictamen_estado" class="step" data-step="6">
                    <h3 class="mb-2">Estado del dictamen</h3>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-outline-accent mr-2 mb-2" data-next="end_en_revision"
                            data-record="riesgo_trab_dictamen_estado:aprobado">Aprobado</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="end_en_revision"
                            data-record="riesgo_trab_dictamen_estado:negado">Negado</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="step_inv_dictamen" class="step" data-step="5">
                    <h3 class="mb-2">Invalidez — ¿Tienes dictamen?</h3>
                    <div class="d-flex flex-wrap mb-2">
                        <button type="button" class="btn btn-outline-accent mr-2 mb-2" data-next="end_en_revision"
                            data-record="invalidez_dictamen:si_negado">Sí, negado</button>
                        <button type="button" class="btn btn-outline-accent mb-2" data-next="end_no_ayuda"
                            data-record="invalidez_dictamen:no">No</button>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <!-- RESULTADOS -->
                <div id="end_no_ayuda" class="step" data-step="9">
                    <div class="result-box result-bad">
                        <h2>Por el momento no podemos apoyarte con tu trámite</h2>
                        <p class="mb-0">Somos especialistas en pensiones del gobierno federal.</p>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="end_pocas_posibilidades" class="step" data-step="9">
                    <div class="result-box result-warn">
                        <h2>Si existen posibilidades de revisar tu pensión</h2>
                        <p class="mb-0">Si existen posibilidades de revisar tu pensión, es necesario que continúes con
                            el formulario.</p>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                        <button type="button" class="btn btn-accent" id="btnCapturarDatos">Dejar mis datos</button>
                    </div>
                </div>

                <div id="end_no_trabajable" class="step" data-step="9">
                    <div class="result-box result-bad">
                        <h2>No se puede trabajar tu caso</h2>
                        <p class="mb-0">El importe es igual o superior al tope salarial vigente.</p>
                    </div>
                    <div class="step-footer"><button type="button"
                            class="btn btn-outline-secondary btn-back-step">Regresar</button></div>
                </div>

                <div id="end_posible_elegible" class="step" data-step="9">
                    <div class="result-box">
                        <h2>Posible elegibilidad</h2>
                        <p class="mb-0">Cumples criterios preliminares para cambio de régimen. Se requiere revisión
                            documental.</p>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                        <button type="button" class="btn btn-accent" id="btnCapturarDatos2">Dejar mis datos</button>
                    </div>
                </div>

                <div id="end_descontado_primer_pago" class="step" data-step="9">
                    <div class="result-box result-neutral">
                        <h2>Solo te descontaron en el primer pago</h2>
                        <p class="mb-0">Conserva talón y evidencia. Podemos orientarte con un análisis puntual.</p>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                        <button type="button" class="btn btn-accent" id="btnCapturarDatos3">Dejar mis datos</button>
                    </div>
                </div>

                <div id="end_sin_descuento" class="step" data-step="9">
                    <div class="result-box result-neutral">
                        <h2>Sin descuento en talón</h2>
                        <p class="mb-0">Si hubo cancelación/no pago, requiere revisión manual.</p>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                        <button type="button" class="btn btn-accent" id="btnCapturarDatos4">Dejar mis datos</button>
                    </div>
                </div>

                <div id="end_revision_manual" class="step" data-step="9">
                    <div class="result-box result-neutral">
                        <h2>Revisión manual requerida</h2>
                        <p class="mb-0">La ruta de Incapacidad Parcial requiere evaluación del expediente.</p>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                        <button type="button" class="btn btn-accent" id="btnCapturarDatos5">Dejar mis datos</button>
                    </div>
                </div>

                <div id="end_en_revision" class="step" data-step="9">
                    <div class="result-box">
                        <h2>Podemos revisar tu caso</h2>
                        <p class="mb-0">Según tus respuestas, procede valoración del expediente.</p>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                        <button type="button" class="btn btn-accent" id="btnCapturarDatos6">Dejar mis datos</button>
                    </div>
                </div>

                <!-- CAPTURA DE DATOS -->
                <div id="contacto_form" class="step" data-step="10">
                    <h2 class="mb-2">Déjanos tus datos</h2>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label">Nombre</label>
                            <input id="c_nombre" type="text" class="form-control" placeholder="Tu nombre" />
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Apellido paterno</label>
                            <input id="c_ap_pat" type="text" class="form-control" placeholder="Apellido paterno" />
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Apellido materno</label>
                            <input id="c_ap_mat" type="text" class="form-control" placeholder="Apellido materno" />
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input id="c_tel" type="tel" class="form-control" placeholder="(844) 123 4667" />
                            <small class="form-text">Solo números, paréntesis, espacios y guiones. Ej: (844) 123
                                4667</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Correo</label>
                            <input id="c_mail" type="email" class="form-control" placeholder="correo@dominio.com" />
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">Organización en la que laboraba</label>
                            <input id="inst_laboraba" type="text" class="form-control"
                                placeholder="Ingresa la instución en la cual laborabas">
                        </div>
                        <div class="form-group col-12">
                            <label class="form-label">Comentarios (opcional)</label>
                            <textarea id="c_com" class="form-control" rows="3"
                                placeholder="Agrega detalles de tu caso"></textarea>
                        </div>
                    </div>
                    <div class="step-footer">
                        <button type="button" class="btn btn-accent" id="btnEnviarDatos">Continuar</button>
                        <button type="button" class="btn btn-outline-secondary btn-back-step">Regresar</button>
                    </div>
                </div>

                <div class="pt-3 text-right">
                    <span class="footer-muted">AXM ABOGADOS</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <div class="modal fade" id="modalConfirmCita" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¿Agendar una cita de asesoría?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas agendar una cita de asesoría sin costo para revisar tu caso?</p>
                </div>
                <div class="modal-footer">
                    <button id="noCitaBtn" type="button" class="btn btn-outline-secondary"
                        data-dismiss="modal">No</button>
                    <button id="siCitaBtn" type="button" class="btn btn-accent">Sí, agendar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal agenda (tipo/fecha/hora) -->
    <div class="modal fade" id="modalAgenda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agendar cita</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tipo de cita</label>
                        <select id="tipo_cita" class="form-control">
                            <option value="virtual" selected>Virtual</option>
                            <option value="presencial">Presencial</option>
                            <option value="telefonica">Telefónica</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input id="appt_date" type="date" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input id="appt_time" type="time" class="form-control" />
                    </div>
                    <small class="text-muted d-block">Horario de atención: Lunes a Viernes, 09:00 a 18:00.</small>
                </div>
                <div class="modal-footer">
                    <button id="cancelAppt" type="button" class="btn btn-outline-secondary"
                        data-dismiss="modal">Cancelar</button>
                    <button id="confirmAppt" type="button" class="btn btn-accent">Confirmar cita</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal preferencia de contacto -->
    <div class="modal fade" id="modalContactoPref" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¿Cómo prefieres que te contactemos?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pref_contact" id="pref_mail" value="mail"
                            checked>
                        <label class="form-check-label" for="pref_mail">Correo electrónico (usaremos: <span
                                id="prefMailSpan"></span>)</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="pref_contact" id="pref_call" value="call">
                        <label class="form-check-label" for="pref_call">Llamada telefónica (horario de oficina)</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="pref_contact" id="pref_none" value="none">
                        <label class="form-check-label" for="pref_none">No deseo ser contactado</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="cancelPref" type="button" class="btn btn-outline-secondary"
                        data-dismiss="modal">Cancelar</button>
                    <button id="confirmPref" type="button" class="btn btn-accent">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        // ===== Config =====
        let TOPE_SALARIAL = 33000;

        // Horario de atención fijo
        const BUSINESS = { start: "09:00", end: "18:00", weekdays: [1, 2, 3, 4, 5] }; // 1=Lun ... 5=Vie

        // ===== Estado =====
        const answers = new Map();
        const steps = Array.from(document.querySelectorAll('.step'));
        const progressBar = document.getElementById('progressBar');
        const historyStack = [];

        // Persistencia (en sesión)
        const STORAGE_KEY = 'axm_form_answers_v1';
        const rangoMin = { '5k-10k': 5000, '11k-20k': 11000, '21k-30k': 21000, '>31k': 31000, '>30k': 30000 };

        function saveToStorage() { try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Object.fromEntries(answers))); } catch (e) { } }
        function loadFromStorage() {
            try {
                const raw = sessionStorage.getItem(STORAGE_KEY);
                if (!raw) return;
                const obj = JSON.parse(raw);
                for (const k in obj) answers.set(k, obj[k]);
            } catch (e) { }
        }

        // Inicializar
        loadFromStorage();
        window.addEventListener('load', () => {
            restoreInputsFromAnswers();
            updateProgress();
            initApptMinDates();
        });

        function restoreInputsFromAnswers() {
            // radios
            document.querySelectorAll('input[type="radio"]').forEach(r => {
                const name = r.name; if (!name) return;
                const val = answers.get(name);
                if (val && String(r.value) === String(val)) r.checked = true;
            });
            // importes
            ['incre_pensi_importe', 'jubil_invalid_importe', 'riesgo_trab_importe'].forEach(id => {
                const el = document.getElementById(id);
                if (el && answers.has(id)) el.value = answers.get(id);
            });
            // contacto
            if (answers.has('c_nombre')) {
                ['c_nombre', 'c_ap_pat', 'c_ap_mat', 'c_tel', 'c_mail', 'c_com', 'inst_laboraba'].forEach(k => {
                    const el = document.getElementById(k); if (el && answers.has(k)) el.value = answers.get(k);
                });
            }
            // fechas/porcentaje RT
            if (answers.has('riesgo_trab_parcial_fecha')) document.getElementById('riesgo_trab_parcial_fecha').value = answers.get('riesgo_trab_parcial_fecha');
            if (answers.has('riesgo_trab_parcial_porcentaje')) document.getElementById('riesgo_trab_parcial_porcentaje').value = answers.get('riesgo_trab_parcial_porcentaje');
            if (answers.has('riesgo_trab_total_fecha')) document.getElementById('riesgo_trab_total_fecha').value = answers.get('riesgo_trab_total_fecha');

            // cita (si existía)
            if (answers.has('appt_date')) document.getElementById('appt_date').value = answers.get('appt_date');
            if (answers.has('appt_time')) document.getElementById('appt_time').value = answers.get('appt_time');
            if (answers.has('tipo_de_cita')) document.getElementById('tipo_cita').value = answers.get('tipo_de_cita');
        }

        function showStep(id, pushHistory = true) {
            const current = document.querySelector('.step.active');
            if (current && pushHistory) historyStack.push(current.id);
            steps.forEach(s => s.classList.remove('active'));
            const el = document.getElementById(id);
            if (el) el.classList.add('active');
            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateProgress() {
            const active = document.querySelector('.step.active');
            const stepNum = active ? Number(active.dataset.step || 1) : 1;
            const maxStep = Math.max(...steps.map(s => Number(s.dataset.step || 1)));
            const pct = Math.min(100, Math.max(8, Math.round((stepNum / Math.max(1, maxStep)) * 100)));
            progressBar.style.width = pct + '%';
            progressBar.textContent = 'Paso ' + stepNum;
        }

        function addToResumenKv(k, v) { if (!k) return; answers.set(k, v); saveToStorage(); }
        function addToResumen(kv) {
            if (!kv || typeof kv !== 'string') return;
            const parts = kv.split(':'); const key = parts.shift().trim(); const val = parts.join(':').trim();
            if (!key) return; addToResumenKv(key, val);
        }

        function evaluarImporte(importe) {
            if (isNaN(importe) || importe <= 0) return null;
            if (importe >= TOPE_SALARIAL) return 'end_no_trabajable';
            if (importe < TOPE_SALARIAL) return 'end_pocas_posibilidades';
            return null;
        }

        // ===== Utilidades de cita =====
        function readCsrf() { const meta = document.querySelector('meta[name="csrf-token"]'); return meta ? meta.getAttribute('content') : ''; }
        function isBusinessDay(dateStr) {
            const d = new Date(dateStr + 'T00:00:00'); // local
            let wd = d.getDay(); // 0..6  (0=Dom)
            // Convertir a 1..5 (Lun..Vie)
            return BUSINESS.weekdays.includes(wd === 0 ? 7 : wd); // incluye 1..5
        }
        function isFuture(dateStr, timeStr) {
            const dt = new Date(dateStr + 'T' + (timeStr || '00:00') + ':00');
            return dt.getTime() > Date.now();
        }
        function withinHours(timeStr) {
            if (!timeStr) return false;
            return (timeStr >= BUSINESS.start && timeStr <= BUSINESS.end);
        }
        function initApptMinDates() {
            const dateInput = document.getElementById('appt_date');
            const timeInput = document.getElementById('appt_time');
            if (!dateInput || !timeInput) return;
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            dateInput.min = `${yyyy}-${mm}-${dd}`;
            timeInput.min = BUSINESS.start;
            timeInput.max = BUSINESS.end;
        }

        // Clicks globales (navegación y registro de respuestas)
        document.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button'); if (!btn) return;

            if (btn.classList.contains('btn-back-step')) {
                if (!historyStack.length) return;
                const prev = historyStack.pop(); showStep(prev, false); return;
            }
            if (btn.id && /^btnCapturarDatos/.test(btn.id)) { showStep('contacto_form'); return; }
            if (btn.dataset && btn.dataset.next === 'contacto_form') { showStep('contacto_form'); return; }

            if (btn.dataset && btn.dataset.record) addToResumen(btn.dataset.record);
            if (btn.dataset && btn.dataset.next) { showStep(btn.dataset.next); return; }
        });

        // Guardar radios
        document.querySelectorAll('input[type="radio"]').forEach(r => {
            r.addEventListener('change', (e) => {
                const name = e.target.name; const value = e.target.value;
                if (name) addToResumenKv(name, value);
            });
        });

        // HANDLERS de importes / validaciones (con SweetAlert2)
        document.addEventListener('DOMContentLoaded', () => {
            // Incremento
            const incBtn = document.getElementById('btnIncContinuar');
            if (incBtn) incBtn.addEventListener('click', () => {
                const imp = Number(document.getElementById('incre_pensi_importe').value);
                const radios = document.querySelectorAll('input[name="incre_pensi_rango"]');
                let sel = null; radios.forEach(r => { if (r.checked) sel = r.value; });
                if (!imp) { Swal.fire('Falta el importe', 'Captura el importe mensual.', 'warning'); return; }
                if (sel && rangoMin[sel] && imp < rangoMin[sel]) { Swal.fire('Importe inválido', 'El importe no puede ser menor al rango seleccionado.', 'warning'); return; }
                addToResumenKv('incre_pensi_rango', sel || '');
                addToResumenKv('incre_pensi_importe', imp);
                const end = evaluarImporte(imp);
                if (end) showStep(end); else Swal.fire('Ups', 'No se pudo evaluar.', 'info');
            });

            // Jubilación/Invalidez
            const jiBtn = document.getElementById('btnJiContinuar');
            if (jiBtn) jiBtn.addEventListener('click', () => {
                const imp = Number(document.getElementById('jubil_invalid_importe').value);
                const radios = document.querySelectorAll('input[name="jubil_invalid_rango"]');
                let sel = null; radios.forEach(r => { if (r.checked) sel = r.value; });
                if (!imp) { Swal.fire('Falta el importe', 'Captura el importe mensual.', 'warning'); return; }
                if (sel && rangoMin[sel] && imp < rangoMin[sel]) { Swal.fire('Importe inválido', 'El importe no puede ser menor al rango seleccionado.', 'warning'); return; }
                addToResumenKv('jubil_invalid_rango', sel || '');
                addToResumenKv('jubil_invalid_importe', imp);
                const end = evaluarImporte(imp);
                if (end) showStep(end); else Swal.fire('Ups', 'No se pudo evaluar.', 'info');
            });

            // RT Total
            const rtTotalBtn = document.getElementById('btnRtTotalContinuar');
            if (rtTotalBtn) rtTotalBtn.addEventListener('click', () => {
                const imp = Number(document.getElementById('riesgo_trab_importe').value);
                const radios = document.querySelectorAll('input[name="riesgo_trab_rango"]');
                let sel = null; radios.forEach(r => { if (r.checked) sel = r.value; });
                const fecha = document.getElementById('riesgo_trab_total_fecha').value;
                if (!imp) { Swal.fire('Falta el importe', 'Captura el importe mensual.', 'warning'); return; }
                if (sel && rangoMin[sel] && imp < rangoMin[sel]) { Swal.fire('Importe inválido', 'El importe no puede ser menor al rango seleccionado.', 'warning'); return; }
                if (!fecha) { Swal.fire('Falta la fecha', 'Indica la fecha de inicio de la pensión.', 'warning'); return; }
                addToResumenKv('riesgo_trab_rango', sel || '');
                addToResumenKv('riesgo_trab_importe', imp);
                addToResumenKv('riesgo_trab_total_fecha', fecha);
                const end = evaluarImporte(imp);
                if (end) showStep(end); else Swal.fire('Ups', 'No se pudo evaluar.', 'info');
            });

            // RT Parcial
            const rtParcialBtn = document.getElementById('btnRtParcialContinuar');
            if (rtParcialBtn) rtParcialBtn.addEventListener('click', () => {
                const fecha = document.getElementById('riesgo_trab_parcial_fecha').value;
                const pctRaw = document.getElementById('riesgo_trab_parcial_porcentaje').value.trim();
                if (!fecha) { Swal.fire('Falta la fecha', 'Indica la fecha de inicio de la pensión.', 'warning'); return; }
                if (!validarPorcentaje(pctRaw)) { Swal.fire('Porcentaje inválido', 'Indica un porcentaje válido (1–100%). Ej: 40%', 'warning'); return; }
                addToResumenKv('riesgo_trab_parcial_fecha', fecha);
                addToResumenKv('riesgo_trab_parcial_porcentaje', pctRaw);
                showStep('end_revision_manual');
            });

            // Envío de datos (NO guarda todavía — solo abre modal de cita o preferencia)
            const enviar = document.getElementById('btnEnviarDatos');
            if (enviar) enviar.addEventListener('click', () => {
                const nombre = document.getElementById('c_nombre').value.trim();
                const apPat = document.getElementById('c_ap_pat').value.trim();
                const apMat = document.getElementById('c_ap_mat').value.trim();
                const tel = document.getElementById('c_tel').value.trim();
                const mail = document.getElementById('c_mail').value.trim();
                const com = document.getElementById('c_com').value.trim();
                const inst = document.getElementById('inst_laboraba').value.trim();

                if (!nombre || !apPat || !apMat || !tel || !mail) {
                    Swal.fire('Campos incompletos', 'Completa nombre, apellidos, teléfono y correo.', 'warning'); return;
                }
                const phoneRegex = /^[0-9()\s\-]{7,20}$/;
                if (!phoneRegex.test(tel)) { Swal.fire('Teléfono inválido', 'Usa solo números, espacios y paréntesis. Ej: (844) 123 4667', 'warning'); return; }
                if (!validateEmail(mail)) { Swal.fire('Correo inválido', 'Revisa el formato de tu correo.', 'warning'); return; }

                addToResumenKv('c_nombre', nombre);
                addToResumenKv('c_ap_pat', apPat);
                addToResumenKv('c_ap_mat', apMat);
                addToResumenKv('c_tel', tel);
                addToResumenKv('c_mail', mail);
                addToResumenKv('c_com', com);
                addToResumenKv('inst_laboraba', inst);

                // Preguntar si quiere agendar
                $('#modalConfirmCita').modal('show');
            });
        });

        // Modales cita / preferencia
        document.getElementById('siCitaBtn').addEventListener('click', () => {
            $('#modalConfirmCita').modal('hide'); $('#modalAgenda').modal('show');
        });

        document.getElementById('noCitaBtn').addEventListener('click', () => {
            $('#modalConfirmCita').modal('hide');
            const mail = document.getElementById('c_mail').value.trim();
            document.getElementById('prefMailSpan').textContent = mail;
            $('#modalContactoPref').modal('show');
        });

        // Confirmar cita => AQUÍ SÍ SE GUARDA TODO (finalizar_registro.php)
        document.getElementById('confirmAppt').addEventListener('click', async () => {
            const tipo = document.getElementById('tipo_cita').value;
            const date = document.getElementById('appt_date').value;
            const time = document.getElementById('appt_time').value;

            if (!date || !time) { Swal.fire('Datos incompletos', 'Selecciona fecha y hora para la cita.', 'warning'); return; }
            if (!isBusinessDay(date)) { Swal.fire('Día no hábil', 'Selecciona un día entre lunes y viernes.', 'warning'); return; }
            if (!withinHours(time)) { Swal.fire('Hora fuera de horario', `Selecciona una hora entre ${BUSINESS.start} y ${BUSINESS.end}.`, 'warning'); return; }
            if (!isFuture(date, time)) { Swal.fire('Fecha/hora inválida', 'La cita debe ser en el futuro.', 'warning'); return; }

            // Guarda en respuestas
            addToResumenKv('tipo_de_cita', tipo);
            addToResumenKv('appt_date', date);
            addToResumenKv('appt_time', time);

            const payload = {
                // Datos de contacto
                nombre: answers.get('c_nombre') || '',
                ap_pat: answers.get('c_ap_pat') || '',
                ap_mat: answers.get('c_ap_mat') || '',
                telefono: answers.get('c_tel') || '',
                correo: answers.get('c_mail') || '',
                comentarios: answers.get('c_com') || '',
                dependencia_laboraba: answers.get('inst_laboraba') || '',
                // Respuestas del diagrama (mapa completo)
                respuestas: Object.fromEntries(answers),
                // Cita
                tipo_de_cita: tipo,
                appt_date: date,
                appt_time: time
            };

            const csrf = readCsrf();
            const btn = document.getElementById('confirmAppt');

            try {
                // Bloquear botón mientras se envía
                btn.disabled = true; btn.textContent = 'Guardando...';

                const res = await fetch('../api/finalizar_registro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrf      // PHP lo lee como HTTP_X_CSRF_TOKEN (ok)
                    },
                    body: JSON.stringify(payload)
                });

                // Intenta parsear JSON incluso si !res.ok (para mostrar message del backend)
                let data;
                try { data = await res.json(); } catch { data = {}; }

                btn.disabled = false; btn.textContent = 'Confirmar cita';

                if (res.ok && data.success) {
                    // Cierra tu modal de agenda si existe
                    if (window.$ && $('#modalAgenda').modal) $('#modalAgenda').modal('hide');

                    // Credenciales del backend
                    const userStr = (data.usuario || payload.correo || '');
                    const passStr = (data.password ? `<br><b>Contraseña:</b> ${data.password}` : '');

                    // Texto base del modal
                    const baseHtml = `Tu cita <b>${tipo}</b> fue agendada para <b>${date}</b> a las <b>${time}</b>.<br><br>
                        <b>Usuario:</b> ${userStr}${passStr}<br><br>`;

                    // Si hay link de WhatsApp, agrégalo; si no, solo muestra el mensaje base
                    const extraHtml = data.wa_link
                        ? `<a href="${data.wa_link}" target="_blank" class="swal2-confirm swal2-styled" style="background-color:#25D366;">
             Enviar por WhatsApp
           </a><br><br>
           También te enviaremos la información por correo.`
                        : `También te enviaremos la información por WhatsApp/correo.`;

                    // Espera a que el usuario cierre el modal antes de redirigir
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Registro completado!',
                        html: baseHtml + extraHtml,
                        confirmButtonText: 'Finalizar',
                        allowOutsideClick: false,
                        allowEnterKey: true
                    });

                    // Limpia y redirige
                    sessionStorage.removeItem(STORAGE_KEY);
                    window.location.href = '../';
                } else {
                    Swal.fire('Error', data.message || 'No se pudo registrar. Intenta nuevamente.', 'error');
                }
            } catch (err) {
                console.error(err);
                btn.disabled = false; btn.textContent = 'Confirmar cita';
                Swal.fire('Error de red', 'No fue posible conectar con el servidor.', 'error');
            }
        });

        // Preferencia de contacto sin agendar (opcional)
        document.getElementById('confirmPref').addEventListener('click', async () => {
            const pref = document.querySelector('input[name="pref_contact"]:checked').value;
            const mail = document.getElementById('c_mail').value.trim();
            const tel = document.getElementById('c_tel').value.trim();
            $('#modalContactoPref').modal('hide');

            try {
                const payload = { contact_preference: pref, correo: mail, telefono: tel, respuestas: Object.fromEntries(answers) };
                const csrf = readCsrf();

                const res = await fetch('../api/finalizar_registro.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    if (pref === 'none') Swal.fire('Listo', 'Hemos registrado tu preferencia. No serás contactado.', 'success');
                    else if (pref === 'call') Swal.fire('Listo', 'Se registró tu preferencia. Te llamaremos en horario de oficina.', 'success');
                    else Swal.fire('Listo', 'Te enviaremos la información por correo.', 'success');

                    sessionStorage.removeItem(STORAGE_KEY);
                    window.location.href = '../';
                } else {
                    Swal.fire('Error', data.message || 'No se pudo registrar tu preferencia.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error de red', 'No fue posible conectar con el servidor.', 'error');
            }
        });

        function validarPorcentaje(v) {
            if (!v) return false;
            const s = v.toString().trim().replace('%', '');
            if (!/^\d{1,3}$/.test(s)) return false;
            const n = Number(s); return n >= 1 && n <= 100;
        }
        function validateEmail(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); }

        // Evitar dobles clics
        document.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-prevent-double]'); if (!btn) return;
            if (btn.dataset.clicked) { ev.preventDefault(); ev.stopImmediatePropagation(); return false; }
            btn.dataset.clicked = '1';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>