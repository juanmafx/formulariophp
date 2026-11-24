<?php

declare(strict_types=1);

namespace ContactForm\Http;

final class SecurityHeaders
{
    private const string CONTENT_SECURITY_POLICY = "default-src 'none'; style-src 'self'; img-src 'self'; "
        . "form-action 'self'; base-uri 'none'; frame-ancestors 'none'";

    public static function apply(Response $response, bool $secure): Response
    {
        $headers = [
            'Content-Security-Policy' => self::CONTENT_SECURITY_POLICY,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'Permissions-Policy' => 'camera=(), geolocation=(), microphone=()',
            'Cache-Control' => 'no-store',
        ];
        if ($secure) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        return $response->withHeaders($headers);
    }
}
