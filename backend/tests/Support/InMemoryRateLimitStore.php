<?php

declare(strict_types=1);

namespace ContactForm\Tests\Support;

use ContactForm\Security\RateLimitStore;

final class InMemoryRateLimitStore implements RateLimitStore
{
    public array $buckets = [];

    public function hit(string $bucketKey, int $windowSeconds, int $now): int
    {
        $bucket = $this->buckets[$bucketKey] ?? null;
        if ($bucket === null || $bucket['expires_at'] <= $now) {
            $bucket = ['hits' => 0, 'expires_at' => $now + $windowSeconds];
        }
        $bucket['hits']++;
        $this->buckets[$bucketKey] = $bucket;

        return $bucket['hits'];
    }
}
