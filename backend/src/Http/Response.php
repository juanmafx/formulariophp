<?php

declare(strict_types=1);

namespace ContactForm\Http;

final readonly class Response
{
    public function __construct(
        public int $status,
        public string $body = '',
        public array $headers = [],
    ) {
    }

    public static function redirect(string $location): self
    {
        return new self(303, '', ['Location' => $location]);
    }

    public function withHeaders(array $headers): self
    {
        return new self($this->status, $this->body, $headers + $this->headers);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
