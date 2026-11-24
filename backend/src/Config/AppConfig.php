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
            self::boolean($environment, 'SESSION_COOKIE_SECURE', true),
        );
    }

    private static function boolean(array $environment, string $name, bool $default): bool
    {
        $raw = $environment[$name] ?? null;
        if ($raw === null) {
            return $default;
        }

        $value = is_string($raw) && trim($raw) !== ''
            ? filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        if ($value === null) {
            throw new InvalidArgumentException($name . ' must be true or false.');
        }

        return $value;
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
