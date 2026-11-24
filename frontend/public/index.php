<?php

declare(strict_types=1);

use ContactForm\ApplicationFactory;
use ContactForm\Http\Request;
use ContactForm\Http\Response;
use ContactForm\Http\SecurityHeaders;
use ContactForm\Persistence\DatabaseFactory;
use ContactForm\Security\NativeSession;
use ContactForm\Security\SystemClock;

ini_set('display_errors', '0');
header_remove('X-Powered-By');

try {
    $config = require dirname(__DIR__, 2) . '/backend/bootstrap.php';
    $response = ApplicationFactory::create(
        $config,
        DatabaseFactory::connect($config->databasePath),
        new NativeSession($config->secureCookies),
        new SystemClock(),
    )->handle(Request::fromGlobals());
} catch (Throwable $exception) {
    error_log('Unhandled application error: ' . $exception::class);
    $response = SecurityHeaders::apply(
        new Response(500, 'The service is temporarily unavailable.', ['Content-Type' => 'text/plain; charset=utf-8']),
        false,
    );
}

$response->send();
