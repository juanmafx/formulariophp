<?php

declare(strict_types=1);

namespace ContactForm\Security;

interface Clock
{
    public function now(): int;
}
