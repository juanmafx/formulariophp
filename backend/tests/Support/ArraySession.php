<?php

declare(strict_types=1);

namespace ContactForm\Tests\Support;

use ContactForm\Security\SessionStore;

final class ArraySession implements SessionStore
{
    public array $data = [];
    public int $regenerations = 0;

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function regenerate(): void
    {
        $this->regenerations++;
    }

    public function clear(): void
    {
        $this->data = [];
        $this->regenerations++;
    }
}
