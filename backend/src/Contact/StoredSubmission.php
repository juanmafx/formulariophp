<?php

declare(strict_types=1);

namespace ContactForm\Contact;

final readonly class StoredSubmission
{
    public function __construct(
        public string $id,
        public string $nameEncrypted,
        public string $emailEncrypted,
        public string $phoneEncrypted,
        public string $subjectEncrypted,
        public string $messageEncrypted,
        public ?string $attachmentNameEncrypted,
        public string $deliveryStatus,
        public int $createdAt,
    ) {
    }
}
