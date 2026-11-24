<?php

declare(strict_types=1);

namespace ContactForm\Security;

final class PasswordHasher
{
    public function __construct(
        private readonly int $memoryCostKib = 65536,
        private readonly int $timeCost = 3,
        private readonly int $threads = 1,
    ) {
    }

    public function hash(#[\SensitiveParameter] string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, $this->options());
    }

    public function verify(#[\SensitiveParameter] string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, $this->options());
    }

    public function burnVerification(#[\SensitiveParameter] string $password): void
    {
        $this->hash($password);
    }

    private function options(): array
    {
        return [
            'memory_cost' => $this->memoryCostKib,
            'time_cost' => $this->timeCost,
            'threads' => $this->threads,
        ];
    }
}
