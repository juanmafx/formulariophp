<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Formulario de contacto </title>
<meta name="keywords" content="">
<meta name="Description" content="">
<link rel="stylesheet" href="css.css">
</head>
<body>
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
<div class=sub>Si deseas un presupuesto adjunta el archivo.</div>
<textarea name="mensaje" cols="35" rows="10" id="mensaje"></textarea>
<br>
Adjuntar archivo:<input  type='file' name='archivo1' id='archivo1' >
 <br>
<input  id=enviar type='submit' value='Enviar'> 


</form>

<br>
<br>
<br>
<a  target="_blank" href="http://validator.w3.org/check?uri=referer">
<img id=iso src="http://validator.w3.org/images/v15445" alt="valid+ISO+15445" height="31" width="88">
</a>

</body>
</html>

