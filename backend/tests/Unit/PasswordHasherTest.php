<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;

final class PasswordHasherTest extends TestCase
{
    public function testProducesSaltedArgon2idHashes(): void
    {
        $hasher = new PasswordHasher(1024, 1, 1);
        $first = $hasher->hash('correct horse battery');
        $second = $hasher->hash('correct horse battery');

        self::assertStringStartsWith('$argon2id$', $first);
        self::assertNotSame($first, $second);
        self::assertTrue($hasher->verify('correct horse battery', $first));
        self::assertFalse($hasher->verify('wrong password', $first));
    }

    public function testDefaultParametersMeetTheDocumentedCost(): void
    {
        $info = password_get_info((new PasswordHasher())->hash('correct horse battery'));

        self::assertSame('argon2id', $info['algoName']);
        self::assertSame(65536, $info['options']['memory_cost']);
        self::assertSame(3, $info['options']['time_cost']);
    }

    public function testDetectsHashesThatNeedRehashing(): void
    {
        $weak = (new PasswordHasher(1024, 1, 1))->hash('correct horse battery');

        self::assertTrue((new PasswordHasher(2048, 2, 1))->needsRehash($weak));
        self::assertFalse((new PasswordHasher(1024, 1, 1))->needsRehash($weak));
    }
}
