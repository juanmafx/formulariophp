<?php

declare(strict_types=1);

namespace ContactForm\Config;

use InvalidArgumentException;

final readonly class AppConfig
{
    public function __construct(
        public string $masterKey,
        public string $databasePath,
        public string $mailerDsn,
        public string $mailFrom,
        public string $mailTo,
        public bool $secureCookies,
    ) {
    }

    public static function fromEnvironment(array $environment): self
    {
        $masterKey = base64_decode(self::required($environment, 'APP_MASTER_KEY'), true);
        if ($masterKey === false || strlen($masterKey) !== SODIUM_CRYPTO_KDF_KEYBYTES) {
            throw new InvalidArgumentException('APP_MASTER_KEY must be base64 encoding of exactly 32 bytes.');
        }

        return new self(
            $masterKey,
            self::required($environment, 'DATABASE_PATH'),
            self::required($environment, 'MAILER_DSN'),
            self::email($environment, 'MAIL_FROM'),
            self::email($environment, 'MAIL_TO'),
            filter_var($environment['SESSION_COOKIE_SECURE'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        );
    }

    private static function required(array $environment, string $name): string
    {
        $value = $environment[$name] ?? '';
        if (!is_string($value) || $value === '') {
            throw new InvalidArgumentException($name . ' is required.');
        }

        return $value;
    }

    private static function email(array $environment, string $name): string
    {
        $value = self::required($environment, $name);
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException($name . ' must be a valid email address.');
        }

        return $value;
    }
}
