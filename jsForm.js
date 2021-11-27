// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'
  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        let nombre = document.getElementById("input_nombre").value;
        let email = document.getElementById("input_email").value;
        let telefono = document.getElementById("input_telefono").value;
        let asunto = document.getElementById("input_asunto").value;
        let mensaje = document.getElementById("input_mensaje").value;

        let vector = [nombre, email, telefono, asunto, mensaje];
        console.log("Vector: " + vector);

        form.classList.add('was-validated')
      }, false)
    })
})();

function sendForm() {
  console.log("Se disponibiliza la informacion del formulario");
}

// For testing propurse
function autoCompletar() {
  document.getElementById("input_nombre").value ="input_nombre"
  document.getElementById("input_email").value ="input_email@mail.com"
  document.getElementById("input_telefono").value ="input_telefono"
  document.getElementById("input_asunto").value ="input_asunto"
  document.getElementById("input_mensaje").value ="input_mensaje"
}
