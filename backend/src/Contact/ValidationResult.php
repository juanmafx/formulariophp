<?php

declare(strict_types=1);

namespace ContactForm\Contact;

final readonly class ValidationResult
{
    public function __construct(
        public ?ContactInput $input,
        public array $errors,
    ) {
    }

    public function isValid(): bool
    {
        return $this->input !== null && $this->errors === [];
    }
}
