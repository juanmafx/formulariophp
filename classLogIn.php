classLogIn{
    private $usser;
    private $password;

private static $Key = "CLAVESUPERSECRETA";

public static function encrypt ($string) {
   return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(Enigma::$Key),
   $string, MCRYPT_MODE_CBC, md5(md5(Enigma::$Key))));
}

public static function decrypt ($string) {
   return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(Enigma::$Key), 
   base64_decode($string), MCRYPT_MODE_CBC, md5(md5(Enigma::$Key))), "\0");
}

}