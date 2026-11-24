<?php

declare(strict_types=1);

use ContactForm\Admin\AdminAuthenticator;
use ContactForm\Persistence\DatabaseFactory;
use ContactForm\Persistence\PdoAdminRepository;
use ContactForm\Security\FieldCipher;
use ContactForm\Security\KeyRing;
use ContactForm\Security\PasswordHasher;
use ContactForm\Security\TotpVerifier;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$arguments = array_slice($argv, 1);
$reset = in_array('--reset', $arguments, true);
$positional = array_values(array_filter($arguments, static fn (string $argument): bool => $argument !== '--reset'));
$username = mb_strtolower(trim($positional[0] ?? ''));
if (preg_match('/\A[a-z0-9._-]{3,64}\z/', $username) !== 1) {
    fwrite(STDERR, "Usage: php backend/bin/create-admin.php <username> [--reset]\n");
    exit(1);
}

$config = require dirname(__DIR__) . '/bootstrap.php';
$repository = new PdoAdminRepository(DatabaseFactory::connect($config->databasePath));
$existing = $repository->findByUsername($username);
if ($existing !== null && !$reset) {
    fwrite(STDERR, "That admin already exists. Pass --reset to replace its credentials.\n");
    exit(1);
}
if ($existing === null && $reset) {
    fwrite(STDERR, "That admin does not exist.\n");
    exit(1);
}

$isTerminal = stream_isatty(STDIN);
if ($isTerminal) {
    fwrite(STDERR, 'Password (at least 14 characters): ');
    shell_exec('stty -echo');
}
$password = rtrim((string) fgets(STDIN), "\r\n");
if ($isTerminal) {
    shell_exec('stty echo');
    fwrite(STDERR, PHP_EOL);
}
if (mb_strlen($password) < 14) {
    fwrite(STDERR, "The password must have at least 14 characters.\n");
    exit(1);
}

$totp = new TotpVerifier();
$secret = $totp->generateSecret();
$cipher = new FieldCipher((new KeyRing($config->masterKey))->totpKey());
$encryptedSecret = $cipher->encrypt($secret, AdminAuthenticator::totpContext($username));
$hash = (new PasswordHasher())->hash($password);

if ($existing === null) {
    $repository->create($username, $hash, $encryptedSecret, time());
} else {
    $repository->replaceCredentials($existing->id, $hash, $encryptedSecret, time());
}

echo "Add this URI to your authenticator app. It is shown only once:\n";
echo $totp->provisioningUri($secret, $username, 'formulariophp'), PHP_EOL;
