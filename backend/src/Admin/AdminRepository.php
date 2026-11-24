<?php

declare(strict_types=1);

namespace ContactForm\Admin;

interface AdminRepository
{
    public function findById(int $id): ?AdminUser;

    public function findByUsername(string $username): ?AdminUser;

    public function create(string $username, string $passwordHash, string $totpSecretEncrypted, int $now): int;

    public function replaceCredentials(int $id, string $passwordHash, string $totpSecretEncrypted, int $now): void;

    public function updatePasswordHash(int $id, string $passwordHash, int $now): void;

    public function advanceTotpStep(int $id, int $step, int $now): bool;
}
