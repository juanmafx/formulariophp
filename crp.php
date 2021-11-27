
    $simple_string = implode(',', $vectorData);
    $ciphering = "aes-128-cbc";
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options   = 0;
    $encryption_iv = '1234567';
    $encryption_key = "encryption_key";
    $encryption = openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv);

    // Displaying the encrypted string 
    echo "Encrypted String: " . $encryption . "\n";
    echo "<br>";

    $decryption_iv = '1234567';
    $decryption_key = "encryption_key";
    $decryption = openssl_decrypt($encryption, $ciphering, $decryption_key, $options, $decryption_iv);
    echo "Decrypted String: " . $decryption;