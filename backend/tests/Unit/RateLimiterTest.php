<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Security\RateLimiter;
use ContactForm\Tests\Support\FixedClock;
use ContactForm\Tests\Support\InMemoryRateLimitStore;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    public function testBlocksAfterTheLimitAndRecoversAfterTheWindow(): void
    {
        $clock = new FixedClock();
        $limiter = new RateLimiter(new InMemoryRateLimitStore(), $clock, 'key');

        self::assertTrue($limiter->allow('login', '192.0.2.1', 2, 60));
        self::assertTrue($limiter->allow('login', '192.0.2.1', 2, 60));
        self::assertFalse($limiter->allow('login', '192.0.2.1', 2, 60));

        $clock->advance(60);

        self::assertTrue($limiter->allow('login', '192.0.2.1', 2, 60));
    }

    public function testIsolatesBucketsAndIdentifiers(): void
    {
        $limiter = new RateLimiter(new InMemoryRateLimitStore(), new FixedClock(), 'key');

        self::assertTrue($limiter->allow('login', '192.0.2.1', 1, 60));
        self::assertTrue($limiter->allow('login', '192.0.2.2', 1, 60));
        self::assertTrue($limiter->allow('contact', '192.0.2.1', 1, 60));
    }

    public function testStoresOnlyKeyedHashesOfIdentifiers(): void
    {
        $store = new InMemoryRateLimitStore();
        (new RateLimiter($store, new FixedClock(), 'key'))->allow('login', '192.0.2.1', 1, 60);

        $bucketKey = (string) array_key_first($store->buckets);

        self::assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $bucketKey);
        self::assertStringNotContainsString('192.0.2.1', $bucketKey);
    }
}
