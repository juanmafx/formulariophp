<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Security\DecryptionFailed;
use ContactForm\Security\FieldCipher;
use ContactForm\Security\KeyRing;
use PHPUnit\Framework\TestCase;

final class FieldCipherTest extends TestCase
{
    private FieldCipher $cipher;

    protected function setUp(): void
    {
        $this->cipher = new FieldCipher(str_repeat('a', 32));
    }

    public function testRoundTripsUnicodePlaintext(): void
    {
        $encrypted = $this->cipher->encrypt('Sensitive value é', 'context');

        self::assertStringNotContainsString('Sensitive', $encrypted);
        self::assertSame('Sensitive value é', $this->cipher->decrypt($encrypted, 'context'));
    }

    public function testUsesAFreshNonceForEveryEncryption(): void
    {
        self::assertNotSame($this->cipher->encrypt('same', 'context'), $this->cipher->encrypt('same', 'context'));
    }

    public function testRejectsCiphertextMovedToAnotherContext(): void
    {
        $encrypted = $this->cipher->encrypt('value', 'submission:1:email');

        $this->expectException(DecryptionFailed::class);
        $this->cipher->decrypt($encrypted, 'submission:2:email');
    }

    public function testRejectsTamperedCiphertext(): void
    {
        $encrypted = $this->cipher->encrypt('value', 'context');
        $tampered = substr($encrypted, 0, -2) . (str_ends_with($encrypted, 'AA') ? 'BB' : 'AA');

        $this->expectException(DecryptionFailed::class);
        $this->cipher->decrypt($tampered, 'context');
    }

    public function testRejectsAWrongKey(): void
    {
        $encrypted = $this->cipher->encrypt('value', 'context');

        $this->expectException(DecryptionFailed::class);
        (new FieldCipher(str_repeat('b', 32)))->decrypt($encrypted, 'context');
    }

    public function testRejectsUnknownVersionAndMalformedPayloads(): void
    {
        foreach (['v2.abc', 'v1.***', 'v1.AAAA'] as $payload) {
            try {
                $this->cipher->decrypt($payload, 'context');
                self::fail('Expected decryption to fail.');
            } catch (DecryptionFailed $exception) {
                self::assertNotSame('', $exception->getMessage());
            }
        }
    }

    public function testKeyRingDerivesDistinctStableSubkeys(): void
    {
        $keys = new KeyRing(str_repeat('m', 32));

        self::assertCount(3, array_unique([$keys->submissionKey(), $keys->rateLimitKey(), $keys->totpKey()]));
        self::assertSame($keys->submissionKey(), (new KeyRing(str_repeat('m', 32)))->submissionKey());
    }
}
