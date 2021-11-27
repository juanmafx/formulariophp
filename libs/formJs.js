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
            form.classList.add('was-validated')
        }, false)
      })
  })();
  
  function sendForm() {
    console.log("Se disponibiliza la informacion del formulario");
    console.log("form sendd");
    let nombre = document.getElementById("input_nombre").value;
    console.log(nombre);
  }


  // For testing propurse
  function autoCompletar() {
    console.log("SETED");
    document.getElementById("nombre").value ="input_nombre";
    document.getElementById("email").value ="input_email@mail.com";
    document.getElementById("telefono").value ="input_telefono";
    document.getElementById("asunto").value ="input_asunto";
    document.getElementById("mensaje").value ="input_mensaje";
    
  }