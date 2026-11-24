<?php

declare(strict_types=1);

namespace ContactForm\Security;

interface RateLimitStore
{
    public function hit(string $bucketKey, int $windowSeconds, int $now): int;
}
