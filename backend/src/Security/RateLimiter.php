<?php

declare(strict_types=1);

namespace ContactForm\Security;

final readonly class RateLimiter
{
    public function __construct(
        private RateLimitStore $store,
        private Clock $clock,
        private string $hashKey,
    ) {
    }

    public function allow(string $bucket, string $identifier, int $limit, int $windowSeconds): bool
    {
        $bucketKey = hash_hmac('sha256', $bucket . "\0" . $identifier, $this->hashKey);

        return $this->store->hit($bucketKey, $windowSeconds, $this->clock->now()) <= $limit;
    }
}
