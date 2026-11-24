<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Config\AppConfig;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AppConfigTest extends TestCase
{
    private function environment(): array
    {
        return [
            'APP_MASTER_KEY' => base64_encode(str_repeat('k', 32)),
            'DATABASE_PATH' => '/tmp/contact.sqlite',
            'MAILER_DSN' => 'null://null',
            'MAIL_FROM' => 'from@example.com',
            'MAIL_TO' => 'to@example.com',
        ];
    }

    public function testLoadsValidConfigurationWithSecureCookiesByDefault(): void
    {
        $config = AppConfig::fromEnvironment($this->environment());

        self::assertSame(str_repeat('k', 32), $config->masterKey);
        self::assertTrue($config->secureCookies);
    }

    public function testAllowsDisablingSecureCookiesExplicitly(): void
    {
        $config = AppConfig::fromEnvironment(['SESSION_COOKIE_SECURE' => 'false'] + $this->environment());

        self::assertFalse($config->secureCookies);
    }

    #[DataProvider('invalidEnvironments')]
    public function testRejectsInvalidConfiguration(string $name, string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        AppConfig::fromEnvironment([$name => $value] + $this->environment());
    }

    public static function invalidEnvironments(): array
    {
        return [
            'empty key' => ['APP_MASTER_KEY', ''],
            'short key' => ['APP_MASTER_KEY', base64_encode('short')],
            'non base64 key' => ['APP_MASTER_KEY', '***not-base64***'],
            'missing database' => ['DATABASE_PATH', ''],
            'missing dsn' => ['MAILER_DSN', ''],
            'invalid sender' => ['MAIL_FROM', 'invalid'],
            'invalid recipient' => ['MAIL_TO', 'invalid'],
        ];
    }
}
