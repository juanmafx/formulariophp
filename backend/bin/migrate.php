<?php

declare(strict_types=1);

use ContactForm\Persistence\DatabaseFactory;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$config = require dirname(__DIR__) . '/bootstrap.php';
$directory = dirname($config->databasePath);
if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
    fwrite(STDERR, "Unable to create the database directory.\n");
    exit(1);
}

$previousMask = umask(0077);
$pdo = DatabaseFactory::connect($config->databasePath);
DatabaseFactory::migrate($pdo, dirname(__DIR__, 2) . '/db/schema.sql');
umask($previousMask);
echo "Schema applied.\n";
