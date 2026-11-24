<?php

declare(strict_types=1);

namespace ContactForm\Security;

interface SessionStore
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function regenerate(): void;

    public function clear(): void;
}
