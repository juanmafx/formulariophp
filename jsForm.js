// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'
  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {

        let nombre = document.getElementById('input_nombre');
        let email = document.getElementById('input_email').value;
        let telefono = document.getElementById('input_telefono').value;
        let asunto = document.getElementById('input_asunto').value;
        let mensaje = document.getElementById('input_mensaje').value;
        console.log("nombre".nombre.value);
        console.log("email".email);
        console.log("asunto".asunto);
        console.log("mensaje ".mensaje);
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})();

function sendForm(event) {





}

