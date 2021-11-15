<?php
require_once "pdo.php";
  session_start();
// Demand a GET parameter
//if (  ! isset($_SESSION['pizza']) ) {
//    die('Not logged in');
//}



// If the user requested logout go back to index.php
//if ( isset($_POST['logout']) ) {
  //  header('Location: logout.php');
    //return;
//}

//$_SESSION['inserted']=false;
//
//$not_make=false;
//$not_num=false;

 if ( isset($_POST['nombre']) ) 
	{

		if ( strlen($_POST['make']) < 1)
		{
			$not_make=true;
		}
			
		else{
				$sql = "INSERT INTO consultas (nombre) 
				VALUES (:nombre)";
				$_SESSION['inserted']=true;
					
				$stmt = $pdo->prepare($sql);
				$stmt->execute(array(
					':nombre' => htmlentities($_POST['nombre'])
				));
					
				header("Location: index.php");
				return;

			}
	}
	
?>





<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<title>Juan Ramallo   fc981212</title>
</head>
<body>

<div class="container">

<h2>Automobiles</h2>

<?php
if ( isset ($_SESSION['email']) ) {
    echo "<h1>Tracking Autos for ";
    echo htmlentities($_SESSION['email']);
    echo "</h1>";
}
?>


<p style="color: red;">
<?php if ($not_make==true) echo("Make is required"); ?>
<?php if ($not_num==true) echo("Mileage and year must be numeric"); ?>

</p>

<form method="post">
<p>Make:
<input type="text" name="make" size="60"></p>
<p>Year:
<input type="text" name="year"></p>
<p>Mileage:
<input type="text" name="mileage"></p>
<input type="submit" value="Add"/>
<input type="submit" name="logout" value="Logout">
</form>

</ul>


<BR>


</div>
</body>
</html>