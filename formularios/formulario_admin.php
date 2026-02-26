<?php
// admin_registro.php
declare(strict_types=1);
session_start();

// Opcional: restringir sólo a superadmin
// if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'superadmin') {
//     header('Location: login.php');
//     exit;
// }

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Registro de administrador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .card {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, .1);
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .col-6 {
            flex: 1 1 calc(50% - 15px);
            min-width: 250px;
        }

        .col-12 {
            flex: 1 1 100%;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 60px;
            resize: vertical;
        }

        .btn-primary {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary[disabled] {
            opacity: .7;
            cursor: wait;
        }

        .btn-primary:hover:not([disabled]) {
            background: #0056b3;
        }

        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert-error {
            background: #ffe5e5;
            color: #b30000;
            border: 1px solid #ffb3b3;
        }

        .alert-success {
            background: #e5ffe7;
            color: #006b1f;
            border: 1px solid #b3ffbf;
        }

        h1 {
            margin-top: 0;
        }

        ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Registrar administrador</h1>

        <div id="alertas"></div>

        <form id="form-admin">
            <h2>Datos personales</h2>
            <div class="row">
                <div class="col-6">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre">
                </div>
                <div class="col-6">
                    <label for="apellido_paterno">Apellido paterno</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno">
                </div>
                <div class="col-6">
                    <label for="apellido_materno">Apellido materno</label>
                    <input type="text" name="apellido_materno" id="apellido_materno">
                </div>
                <div class="col-6">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono">
                </div>
            </div>

            <h2>Contactos</h2>
            <div class="row">
                <div class="col-6">
                    <label for="email">Email principal</label>
                    <input type="email" name="email" id="email">
                </div>
                <div class="col-6">
                    <label for="email_secundario">Email secundario</label>
                    <input type="email" name="email_secundario" id="email_secundario">
                </div>
                <div class="col-6">
                    <label for="num_tel_secundario">Teléfono secundario</label>
                    <input type="text" name="num_tel_secundario" id="num_tel_secundario">
                </div>
            </div>

            <h2>Datos profesionales</h2>
            <div class="row">
                <div class="col-6">
                    <label for="especialidad">Especialidad</label>
                    <input type="text" name="especialidad" id="especialidad">
                </div>
                <div class="col-6">
                    <label for="estudios">Estudios</label>
                    <input type="text" name="estudios" id="estudios">
                </div>
                <div class="col-6">
                    <label for="ciudad_ejerce">Ciudad donde ejerce</label>
                    <input type="text" name="ciudad_ejerce" id="ciudad_ejerce">
                </div>
                <div class="col-6">
                    <label for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion">
                </div>
                <div class="col-6">
                    <label for="departamento">Departamento</label>
                    <input type="text" name="departamento" id="departamento">
                </div>
                <div class="col-6">
                    <label for="puesto">Puesto</label>
                    <input type="text" name="puesto" id="puesto">
                </div>
                <div class="col-12">
                    <label for="descripcion">Descripción / Bio</label>
                    <textarea name="descripcion" id="descripcion"></textarea>
                </div>
            </div>

            <h2>Cuenta y seguridad</h2>
            <div class="row">
                <div class="col-6">
                    <label for="username">Username *</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="col-6">
                    <label for="password">Contraseña *</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="col-6">
                    <label for="password2">Repite la contraseña *</label>
                    <input type="password" name="password2" id="password2" required>
                </div>
                <div class="col-3">
                    <label for="rol">Rol</label>
                    <select name="rol" id="rol">
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="editor">Editor</option>
                    </select>
                </div>
                <div class="col-3">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="suspendido">Suspendido</option>
                    </select>
                </div>
            </div>

            <br>
            <button type="submit" class="btn-primary" id="btn-guardar">Guardar administrador</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-admin');
            const alertasDiv = document.getElementById('alertas');
            const btnGuardar = document.getElementById('btn-guardar');

            function mostrarAlertas(tipo, mensaje, errores = []) {
                let html = '<div class="alert ' + (tipo === 'error' ? 'alert-error' : 'alert-success') + '">';
                html += '<strong>' + mensaje + '</strong>';
                if (errores.length) {
                    html += '<ul>';
                    errores.forEach(e => {
                        html += '<li>' + e + '</li>';
                    });
                    html += '</ul>';
                }
                html += '</div>';
                alertasDiv.innerHTML = html;
            }

            function limpiarFormulario() {
                form.reset();
                // valores por defecto
                document.getElementById('rol').value = 'admin';
                document.getElementById('estado').value = 'activo';
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                alertasDiv.innerHTML = '';
                btnGuardar.disabled = true;

                const formData = new FormData(form);

                fetch('../php/registro_admins.php', {
                    method: 'POST',
                    body: formData,
                })
                    .then(async (response) => {
                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            throw new Error('Respuesta inválida del servidor');
                        }
                        if (!response.ok || !data.success) {
                            const errores = data.errors || [];
                            throw new Error(data.message || 'Error al procesar la solicitud.');
                        }
                        // éxito
                        mostrarAlertas('success', data.message || 'Administrador registrado correctamente.');
                        limpiarFormulario();
                    })
                    .catch((error) => {
                        // si el servidor envió JSON con errores, intenta parsear otra vez
                        if (error && error.message) {
                            mostrarAlertas('error', 'No se pudo registrar el administrador.', [error.message]);
                        } else {
                            mostrarAlertas('error', 'Ocurrió un error inesperado.', []);
                        }
                    })
                    .finally(() => {
                        btnGuardar.disabled = false;
                    });
            });
        });
    </script>
</body>

</html>