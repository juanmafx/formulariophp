<form class="row needs-validation" novalidate>

    <div class="col-md-6">
        <label class="form-label">Nombre:</label>
        <input type="text" class="form-control" id="input_nombre" required>
        <div class="invalid-feedback">
            Ingrese nombre.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Email:</label>
        <input type="email" class="form-control" id="input_email" required>
        <div class="invalid-feedback">
            Escriba email.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Teléfono:</label>
        <input type="text" class="form-control" id="input_telefono" required>
        <div class="invalid-feedback">
            Inserte Teléfono.
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Asunto:</label>
        <input type="text" class="form-control" id="input_asunto" required>
        <div class="invalid-feedback">
            Debe indicar asunto.
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label">Mensaje:</label>
        <textarea class="form-control" id="input_mensaje" required></textarea>
        <div class="invalid-feedback">
            Debe indicar menasaje.
        </div>
    </div>


    <div class="col-12">
        <button class="btn btn-primary" type="submit" onclick="sendForm(event);">Submit form</button>
    </div>
</form>