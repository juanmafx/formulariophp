<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

echo base64_encode(random_bytes(SODIUM_CRYPTO_KDF_KEYBYTES)), PHP_EOL;
