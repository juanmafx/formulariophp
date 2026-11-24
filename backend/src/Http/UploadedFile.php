<?php

declare(strict_types=1);

namespace ContactForm\Http;

final readonly class UploadedFile
{
    public function __construct(
        public string $path,
        public string $clientName,
        public int $size,
        public int $error,
    ) {
    }

    public static function fromGlobal(mixed $file): ?self
    {
        if (!is_array($file) || !is_string($file['tmp_name'] ?? null) || !is_int($file['error'] ?? null)) {
            return null;
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] === UPLOAD_ERR_OK && !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        return new self(
            $file['tmp_name'],
            is_string($file['name'] ?? null) ? $file['name'] : '',
            is_int($file['size'] ?? null) ? $file['size'] : 0,
            $file['error'],
        );
    }
}
