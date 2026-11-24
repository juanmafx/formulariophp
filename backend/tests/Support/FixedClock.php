<?php

declare(strict_types=1);

namespace ContactForm\Tests\Support;

use ContactForm\Security\Clock;

final class FixedClock implements Clock
{
    public function __construct(public int $timestamp = 1_800_000_000)
    {
    }

    public function now(): int
    {
        return $this->timestamp;
    }

    public function advance(int $seconds): void
    {
        $this->timestamp += $seconds;
    }
}
