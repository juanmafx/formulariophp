<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Formulario de contacto</title>
    <link rel="stylesheet" href="libs/bootstrap.css">
</head>

<body>
    <div class="container">
        <div class="py-5 text-center">
            <h2>Formulario de Contacto</h2>
        </div>

        <form class="row needs-validation" name='formulario' id='formulario' method='post' action='confirmacion.php' target='_self' enctype="multipart/form-data" novalidate>
            <div class="col-md-6">
                <label class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
                <div class="invalid-feedback">
                    Ingrese nombre.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">
                    Ingrese email.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Teléfono:</label>
                <input type="text" class="form-control" id="telefono" name="telefono" required>
                <div class="invalid-feedback">
                    Ingrese teléfono.
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Asunto:</label>
                <input type="text" class="form-control" id="asunto" name="asunto" required>
                <div class="invalid-feedback">
                    Ingrese asunto.
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label">Si desea puede adjuntar un archivo:</label>
                <br>
                <input type='file' name='archivo' id='archivo'>
                <br>
                <div class="invalid-feedback">
                    Ingrese menasaje.
                </div>
            </div>
            <div class="col-md-12">
                <label class="form-label">Mensaje:</label>
                <textarea class="form-control" id="mensaje" name="mensaje" required></textarea>
                <div class="invalid-feedback">
                    Ingrese menasaje.
                </div>
            </div>


            <div class="col-12 m-1">
                <button class="btn btn-primary" id="boton-enviar" type="submit" value="Enviar" name="input_submit" onclick="sendForm()">Enviar</button>
 
            </div>

        </form>
		
		               <button class="btn btn-primary float-right" onclick="autoCompletar()">AutoCompletar</button>





</body>
<script src="libs/formJs.js" ?id=<? print(date('H:i:s')); ?>></script>

</html>