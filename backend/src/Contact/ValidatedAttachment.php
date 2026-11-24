<?php

declare(strict_types=1);

namespace ContactForm\Contact;

final readonly class ValidatedAttachment
{
    public function __construct(
        public string $path,
        public string $safeName,
        public string $mimeType,
    ) {
    }
}
