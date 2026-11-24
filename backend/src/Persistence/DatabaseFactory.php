<?php

declare(strict_types=1);

namespace ContactForm\Persistence;

use PDO;
use RuntimeException;

final class DatabaseFactory
{
    public static function connect(string $path): PDO
    {
        if ($path !== ':memory:' && !file_exists($path)) {
            $previousMask = umask(0077);
            touch($path);
            umask($previousMask);
        }

        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        if ($path !== ':memory:') {
            $pdo->exec('PRAGMA journal_mode = WAL');
        }

        return $pdo;
    }

    public static function migrate(PDO $pdo, string $schemaPath): void
    {
        $schema = file_get_contents($schemaPath);
        if ($schema === false) {
            throw new RuntimeException('Unable to read the database schema.');
        }
        $pdo->exec($schema);
    }
}
