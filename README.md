formulario de contacto php
=============

Formulario de contacto  en php el mas simple.
Este formulario de contacto funciona con dos archivos:

1)El archivo index.php donde tus usuarios completaran los campos.
2)El archivo confirmacion.php donde el usuario lee una confirmacion de envio.


Hacia donde se envian los campos com pletados en el formulario de contacto?
Para que el admisnitrador del sitio reciba esa informacion es nesesario lo siguiente:

SUPER IMPORTANTE:
En el archivo confirmacion.php en el renglon 55 del codigo casi al final encontraras esto
//cambiar aqui el email 
if (form_mail("TUEMAIL@LOQUESEA", $_POST[asunto], 
Doonde deberas poner el email donde deseas recibir el formulario de contacto.
Una vez hecho eso, puedes completar el formulario  y reciniras un correo con los datos cargados por el usuario de ese sitio.



Con este formulario puedes crear un  formulario de contacto y recibir los datos cargados por el usuario  en una casilla de mail.

En un archivo se cargan los datos  (formulario) y cuando el usuario hace clik en enviar si completo con un mail 
que posiblemente sea real.
El formulario te envia a un nuevo archivo de confirmacion.

Puedes configurar una respuesta automatica desde ese mail que recibiste el correo , algo como:

Gracias por contacatarse a la brevedad recibira una respuesta.

Esta pensado para funcionar en HTML5.

Ademas el formulario incluye el hecho de poder adjuntar un mail.


