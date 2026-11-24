<?php

declare(strict_types=1);

namespace ContactForm\Contact;

final readonly class ContactInput
{
    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $subject,
        public string $message,
    ) {
    }
}
