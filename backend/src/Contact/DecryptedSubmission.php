<?php

declare(strict_types=1);

namespace ContactForm\Contact;

final readonly class DecryptedSubmission
{
    public function __construct(
        public string $id,
        public ContactInput $contact,
        public ?string $attachmentName,
        public string $deliveryStatus,
        public int $createdAt,
    ) {
    }
}
