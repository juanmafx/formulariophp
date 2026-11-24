<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Security\TotpVerifier;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TotpVerifierTest extends TestCase
{
    private const string RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    private TotpVerifier $totp;

    protected function setUp(): void
    {
        $this->totp = new TotpVerifier();
    }

    public function testMatchesRfc6238ReferenceVectors(): void
    {
        self::assertSame('287082', $this->totp->codeAt(self::RFC_SECRET, 59));
        self::assertSame('081804', $this->totp->codeAt(self::RFC_SECRET, 1111111109));
        self::assertSame('005924', $this->totp->codeAt(self::RFC_SECRET, 1234567890));
    }

    public function testAcceptsCurrentAndAdjacentSteps(): void
    {
        $now = 1_800_000_000;
        $step = intdiv($now, 30);

        self::assertSame($step, $this->totp->matchingStep(self::RFC_SECRET, $this->totp->codeAt(self::RFC_SECRET, $now), null, $now));
        self::assertSame($step - 1, $this->totp->matchingStep(self::RFC_SECRET, $this->totp->codeAt(self::RFC_SECRET, $now - 30), null, $now));
        self::assertSame($step + 1, $this->totp->matchingStep(self::RFC_SECRET, $this->totp->codeAt(self::RFC_SECRET, $now + 30), null, $now));
    }

    public function testRejectsCodesOutsideTheDriftWindow(): void
    {
        $now = 1_800_000_000;

        self::assertNull($this->totp->matchingStep(self::RFC_SECRET, $this->totp->codeAt(self::RFC_SECRET, $now - 120), null, $now));
    }

    public function testRejectsReplayOfAnAcceptedStep(): void
    {
        $now = 1_800_000_000;
        $code = $this->totp->codeAt(self::RFC_SECRET, $now);
        $step = $this->totp->matchingStep(self::RFC_SECRET, $code, null, $now);

        self::assertNull($this->totp->matchingStep(self::RFC_SECRET, $code, $step, $now));
    }

    public function testRejectsMalformedCodes(): void
    {
        foreach (['', '12345', '1234567', 'abcdef', "123456\n"] as $code) {
            self::assertNull($this->totp->matchingStep(self::RFC_SECRET, $code, null, 1_800_000_000));
        }
    }

    public function testGeneratesAProvisioningUriForANewSecret(): void
    {
        $secret = $this->totp->generateSecret();
        $uri = $this->totp->provisioningUri($secret, 'operator', 'formulariophp');

        self::assertMatchesRegularExpression('/\A[A-Z2-7]{32}\z/', $secret);
        self::assertStringStartsWith('otpauth://totp/', $uri);
        self::assertStringContainsString('issuer=formulariophp', $uri);
    }

    public function testRejectsAnEmptySecret(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->totp->codeAt('', 0);
    }
}
