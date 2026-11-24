<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Http\Response;
use ContactForm\Http\SecurityHeaders;
use PHPUnit\Framework\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function testAppliesRestrictivePolicy(): void
    {
        $headers = SecurityHeaders::apply(new Response(200), true)->headers;

        self::assertStringContainsString("default-src 'none'", $headers['Content-Security-Policy']);
        self::assertStringContainsString("frame-ancestors 'none'", $headers['Content-Security-Policy']);
        self::assertStringNotContainsString('unsafe', $headers['Content-Security-Policy']);
        self::assertSame('nosniff', $headers['X-Content-Type-Options']);
        self::assertSame('no-store', $headers['Cache-Control']);
        self::assertSame('max-age=31536000', $headers['Strict-Transport-Security']);
    }

    public function testOmitsHstsOutsideHttps(): void
    {
        self::assertArrayNotHasKey('Strict-Transport-Security', SecurityHeaders::apply(new Response(200), false)->headers);
    }
}
