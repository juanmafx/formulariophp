Formulario de contacto php SUPER EFICIENTE 
=============

Con estos dos archivos  vas a crear  un  formulario de contacto y recibir los datos cargados por el usuario  en una casilla de mail (osea un e-mail).

En un archivo se cargan los datos  (formulario) y cuando el usuario hace clik en enviar si completo con un mail 
que posiblemente sea real.
El formulario te envía a un nuevo archivo de confirmacion.

Puedes configurar una respuesta automática desde ese mail que recibiste el correo , algo como:

Gracias por contactarse a la brevedad recibirá una respuesta.


Formulario de contacto  en php el mas simple.
Este formulator de contacto funciona con dos archivos:

1)El archivo index.php donde tus usuarios completan los campos que solicites 

2)El archivo confirmacion.php donde el usuario lee una confirmación de envio.


¿Hacia donde se envían los campos completados por el usuario  en el formulario de contacto?
Para que el administrador del sitio reciba esa información en alguna casilla de correo electrónico es  necesario lo siguiente:

SUPER IMPORTANTE:

En el archivo confirmacion.php en el renglón 55 del código casi al final encontrarás esto:

//cambiar aqui el email 

if (form_mail("TUEMAIL@LOQUESEA", $_POST[asunto], 

Donde deberas colocar la dirección de e-mail  donde deseas recibir los envíos del formulario de contacto.

Una vez hecho eso, puedes completar el formulario  y recibirás un correo con los datos cargados por los usuarios del sitio.
Por cada clic en enviar en el formulario de contacto recibirás la información completada por el usuario.



Este formulario de contacto en php simple busca ser una solución simple rapida y eficiente.
Es una base fuerte y segura para que los usuarios del sitio web se contacten con usted.
Usted SEÑOR WEB MASTER tiene que ser inteligente y astuto, es muy importante que en el archivo confirmacion.php donde el usuario va para confirmar el envio del mail en forma automatica despues de hacer CLIK en enviar ,usted ponga mas datos de contacto como su correo electrónico su teléfono su dirección y todo lo demas.

Mucha suerte espero serle útil.
Saludos.
