<?
session_start();
$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$_SESSION['url'] = $url;

print($_SESSION['url']);

$salt = 'XyZzy12*_';
$stored_hash = 'a8609e8d62c043243c4e201cbb342862';  // Pw is meow123
$check = hash('md5', $salt.$_POST['pass']);

$failure = false;  // If we have no POST data

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['who']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['who']) < 1 || strlen($_POST['pass']) < 1 ) {
        $failure = "User name and password are required";
    } else {
        $check = hash('md5', $salt.$_POST['pass']);

if (isset($_POST['input_submited'])) {
    $vectorData = [
        'nombre' => htmlentities($_POST['input_nombre']),
        'email' => htmlentities($_POST['input_email']),
        'telefono' => htmlentities($_POST['input_telefono']),
        'asunto' => htmlentities($_POST['input_asunto']),
        'mensaje' => htmlentities($_POST['input_mensaje']),
        'url' => htmlentities($url)
    ];
    $_SESSION['vectorData'] = $vectorData;
    $_GET['vectorData'] = $vectorData;
    header('location: confirmacion.php?id=juan'.$vectorData);
    return;
}

?>