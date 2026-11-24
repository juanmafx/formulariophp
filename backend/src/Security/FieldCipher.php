<?php

declare(strict_types=1);

namespace ContactForm\Security;

final readonly class FieldCipher
{
    private const string PREFIX = 'v1.';

    public function __construct(private string $key)
    {
    }

    public function encrypt(string $plaintext, string $context): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, $context, $nonce, $this->key);

        return self::PREFIX . sodium_bin2base64($nonce . $ciphertext, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    public function decrypt(string $encoded, string $context): string
    {
        if (!str_starts_with($encoded, self::PREFIX)) {
            throw new DecryptionFailed('Unsupported ciphertext version.');
        }

        try {
            $payload = sodium_base642bin(substr($encoded, strlen(self::PREFIX)), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        } catch (\SodiumException) {
            throw new DecryptionFailed('Malformed ciphertext.');
        }

        $nonceLength = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        if (strlen($payload) <= $nonceLength) {
            throw new DecryptionFailed('Malformed ciphertext.');
        }

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            substr($payload, $nonceLength),
            $context,
            substr($payload, 0, $nonceLength),
            $this->key,
        );
        if ($plaintext === false) {
            throw new DecryptionFailed('Ciphertext authentication failed.');
        }

        return $plaintext;
    }
}
