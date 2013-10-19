<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Formulario de contacto</title>
<link rel="stylesheet" href="css.css">
</head>
<body>
<h1>Formulario de contacto</h1>
<form name='formulario' id='formulario' method='post' action='confirmacion.php' target='_self' enctype="multipart/form-data"> 

<br>NOMBRE: <input type='text' name='Nombre' id='Nombre'> 

<br>E-MAIL:<input type='text' name='email' id='email'pattern="^[a-zA-Z0-9.!#$%'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$" 
 required	value="ejemplo@hotmail.com" 
onclick="if(this.value=='ejemplo@hotmail.com') this.value=''" onblur="if(this.value=='') this.value='ejemplo@hotmail.com'">

<br>TELEFONO:<input type='text' name='telefono' id='telefono' value="Con Codigo de Area" 
onclick="if(this.value=='Con Codigo de Area') this.value=''" onblur="if(this.value=='') this.value='Con Codigo de Area'">
<br>ASUNTO: <input type='text' name='asunto' id='asunto'>
<br>
MENSAJE:<br>
<textarea name="mensaje" cols="35" rows="10" id="mensaje"></textarea>
<br>
Si desea puede adjuntar un archivo:<input  type='file' name='archivo1' id='archivo1' >
 <br>

<input  id=boton-enviar type='submit' value='Enviar'> 

</form>
</body>
</html>
