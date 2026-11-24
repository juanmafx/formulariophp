<?php

declare(strict_types=1);

namespace ContactForm\Security;

final readonly class KeyRing
{
    private const string CONTEXT = 'contact_';

    public function __construct(private string $masterKey)
    {
    }

    public function submissionKey(): string
    {
        return $this->derive(1);
    }

    public function rateLimitKey(): string
    {
        return $this->derive(2);
    }

    public function totpKey(): string
    {
        return $this->derive(3);
    }

    private function derive(int $subkeyId): string
    {
        return sodium_crypto_kdf_derive_from_key(32, $subkeyId, self::CONTEXT, $this->masterKey);
    }
}
