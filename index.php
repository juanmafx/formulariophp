<<<<<<< HEAD
=======
<?
require_once "pdo.php";
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=consultas','a');

$sql = "INSERT INTO consultas (nombre) 
				VALUES (:nombre)";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(
	':nombre' => htmlentities($_POST['nombre'])
));

?>
>>>>>>> 8bd879773889b410b5e6b4ab0dcae91fe5a8dca0

<!doctype html>
<html lang="es">

<head>
	<meta charset="utf-8">
<<<<<<< HEAD
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Formulario de Contacto</title>
	<link rel="stylesheet" href="css.css?id=<? print(date('H:i:s')); ?>">
=======
	<title>Formulario de Contacto</title>
	<link rel="stylesheet" href="css.css">
>>>>>>> 8bd879773889b410b5e6b4ab0dcae91fe5a8dca0
</head>

<body>
	<div class="container">
<<<<<<< HEAD
		<div class="py-5 text-center">
			<h2>Formulario de Contacto</h2>
		</div>
		<div class="row">
			<div class="col-md-7 col-lg-8">
				<?php include 'form.php'; ?>
			</div>
=======
		<main>
			<div class="py-5 text-center">
				<h2>Formulario de Contacto</h2>
			</div>

			<div class="row">
				<div class="col-md-7 col-lg-8">
					<h4 class="mb-3">Formulario</h4>
					<?php include 'form.php'; ?>
				</div>
>>>>>>> 8bd879773889b410b5e6b4ab0dcae91fe5a8dca0
</body>
<script src="jsForm.js?id=<? print(date('H:i:s')); ?>"></script>

</html>