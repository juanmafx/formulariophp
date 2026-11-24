<?php

declare(strict_types=1);

namespace ContactForm\Persistence;

use ContactForm\Admin\AdminRepository;
use ContactForm\Admin\AdminUser;
use PDO;

final readonly class PdoAdminRepository implements AdminRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?AdminUser
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $this->hydrate($statement->fetch(PDO::FETCH_ASSOC));
    }

    public function findByUsername(string $username): ?AdminUser
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_users WHERE username = :username');
        $statement->execute(['username' => $username]);

        return $this->hydrate($statement->fetch(PDO::FETCH_ASSOC));
    }

    public function create(string $username, string $passwordHash, string $totpSecretEncrypted, int $now): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_users (username, password_hash, totp_secret_encrypted, created_at, updated_at)
             VALUES (:username, :password_hash, :totp_secret, :created_at, :updated_at)',
        );
        $statement->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'totp_secret' => $totpSecretEncrypted,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function replaceCredentials(int $id, string $passwordHash, string $totpSecretEncrypted, int $now): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE admin_users SET password_hash = :password_hash, totp_secret_encrypted = :totp_secret,
                last_totp_step = NULL, updated_at = :updated_at WHERE id = :id',
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'totp_secret' => $totpSecretEncrypted,
            'updated_at' => $now,
            'id' => $id,
        ]);
    }

    public function updatePasswordHash(int $id, string $passwordHash, int $now): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE admin_users SET password_hash = :password_hash, updated_at = :updated_at WHERE id = :id',
        );
        $statement->execute(['password_hash' => $passwordHash, 'updated_at' => $now, 'id' => $id]);
    }

    public function advanceTotpStep(int $id, int $step, int $now): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE admin_users SET last_totp_step = :step, updated_at = :updated_at
             WHERE id = :id AND (last_totp_step IS NULL OR last_totp_step < :minimum_step)',
        );
        $statement->execute(['step' => $step, 'updated_at' => $now, 'id' => $id, 'minimum_step' => $step]);

        return $statement->rowCount() === 1;
    }

    private function hydrate(mixed $row): ?AdminUser
    {
        if (!is_array($row)) {
            return null;
        }

        return new AdminUser(
            (int) $row['id'],
            (string) $row['username'],
            (string) $row['password_hash'],
            (string) $row['totp_secret_encrypted'],
            $row['last_totp_step'] === null ? null : (int) $row['last_totp_step'],
        );
    }
}
