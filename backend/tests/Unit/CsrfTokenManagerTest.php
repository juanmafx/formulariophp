<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Security\CsrfTokenManager;
use ContactForm\Tests\Support\ArraySession;
use PHPUnit\Framework\TestCase;

final class CsrfTokenManagerTest extends TestCase
{
    public function testIssuesAStableHighEntropyToken(): void
    {
        $csrf = new CsrfTokenManager(new ArraySession());
        $token = $csrf->token();

        self::assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $token);
        self::assertSame($token, $csrf->token());
        self::assertTrue($csrf->isValid($token));
    }

    public function testRejectsMissingAndWrongTokens(): void
    {
        $csrf = new CsrfTokenManager(new ArraySession());

        self::assertFalse($csrf->isValid(''));
        $csrf->token();
        self::assertFalse($csrf->isValid(''));
        self::assertFalse($csrf->isValid(str_repeat('0', 64)));
    }

    public function testRotationInvalidatesThePreviousToken(): void
    {
        $csrf = new CsrfTokenManager(new ArraySession());
        $previous = $csrf->token();
        $current = $csrf->rotate();

        self::assertFalse($csrf->isValid($previous));
        self::assertTrue($csrf->isValid($current));
    }
}
