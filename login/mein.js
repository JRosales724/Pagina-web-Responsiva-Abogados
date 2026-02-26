$(document).ready(function () {
  // Captura el evento submit del formulario de inicio de sesión
  $("#login-form").submit(function (event) {
    // Evita el envío normal del formulario
    event.preventDefault();

    // Obtiene los datos del formulario
    var formData = $(this).serialize();

    // Realiza la solicitud AJAX
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: formData,
      success: function (response) {
        // Maneja la respuesta del servidor
        console.log(response);

        // Maneja la respuesta exitosa
        if (response == 0) {
          // Si el usuario o contraseña son incorrectos, muestra una alerta
          alertify.error("Usuario y/o Contraseña incorrectos");
        } else if (response == 1) {
          // Redirige a la página raíz después de un inicio de sesión exitoso
          window.location.href = "../";
        }

        // Verifica si el tipo de usuario se almacenó en la sesión correctamente
      },
      error: function (xhr, status, error) {
        // Maneja los errores de la solicitud AJAX
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  });
});
