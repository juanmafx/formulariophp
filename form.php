<?
session_start();
$_SESSION['vectorData']=[];

if (isset($_POST['input_submited'])) {
    $url = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $_SESSION['vectorData'] = [
        'nombre' => htmlentities($_POST['input_nombre']),
        'email' => htmlentities($_POST['input_email']),
        'telefono' => htmlentities($_POST['input_telefono']),
        'asunto' => htmlentities($_POST['input_asunto']),
        'mensaje' => htmlentities($_POST['input_mensaje']),
        'url' => htmlentities($url)
    ];

    header('location: confirmacion.php');
    return;
}

?>
<form class="row needs-validation" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" novalidate>
    <div class="col-md-6">
        <label class="form-label">Nombre:</label>
        <input type="text" class="form-control" id="input_nombre" name="input_nombre" required>
        <div class="invalid-feedback">
            Ingrese nombre.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Email:</label>
        <input type="email" class="form-control" id="input_email" name="input_email" required>
        <div class="invalid-feedback">
            Ingrese email.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Teléfono:</label>
        <input type="text" class="form-control" id="input_telefono" name="input_telefono" required>
        <div class="invalid-feedback">
            Ingrese teléfono.
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Asunto:</label>
        <input type="text" class="form-control" id="input_asunto" name="input_asunto" required>
        <div class="invalid-feedback">
            Ingrese asunto.
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label">Mensaje:</label>
        <textarea class="form-control" id="input_mensaje" name="input_mensaje" required></textarea>
        <div class="invalid-feedback">
            Ingrese menasaje.
        </div>
    </div>

<<<<<<< HEAD
    <div class="col-12 m-1">
        <button class="btn btn-primary" type="submit" id="input_submited" name="input_submited">Enviar Mensaje</button>
=======

    <div class="col-12">
        <button class="btn btn-primary" type="submit" onclick="sendForm()">Submit form</button>
>>>>>>> 8bd879773889b410b5e6b4ab0dcae91fe5a8dca0
    </div>

</form>
<div class="col-12 m-1">
    <button class="btn btn-primary float-right" onclick="autoCompletar()">AutoCompletar</button>
</div>