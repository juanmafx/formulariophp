<?php

declare(strict_types=1);

use ContactForm\Config\AppConfig;
use Symfony\Component\Dotenv\Dotenv;

$projectRoot = dirname(__DIR__);
require $projectRoot . '/vendor/autoload.php';

if (is_file($projectRoot . '/.env')) {
    (new Dotenv())->usePutenv(false)->load($projectRoot . '/.env');
}

return AppConfig::fromEnvironment($_ENV + getenv());
