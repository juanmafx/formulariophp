<?php

declare(strict_types=1);

namespace ContactForm\Http;

final readonly class Request
{
    public function __construct(
        public string $method,
        public string $path,
        public array $post = [],
        public ?UploadedFile $attachment = null,
        public string $clientIp = '0.0.0.0',
        public array $query = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = is_string($_SERVER['REQUEST_URI'] ?? null) ? $_SERVER['REQUEST_URI'] : '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return new self(
            is_string($_SERVER['REQUEST_METHOD'] ?? null) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET',
            is_string($path) && $path !== '' ? $path : '/',
            $_POST,
            UploadedFile::fromGlobal($_FILES['attachment'] ?? null),
            is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0',
            $_GET,
        );
    }

    public function input(string $name): string
    {
        $value = $this->post[$name] ?? '';

        return is_string($value) ? $value : '';
    }

    public function queryInt(string $name, int $default): int
    {
        $value = filter_var($this->query[$name] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return is_int($value) ? $value : $default;
    }
}
