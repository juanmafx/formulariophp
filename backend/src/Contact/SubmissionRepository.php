<?php

declare(strict_types=1);

namespace ContactForm\Contact;

interface SubmissionRepository
{
    public function save(StoredSubmission $submission): void;

    public function updateDeliveryStatus(string $id, string $status): void;

    public function findPage(int $limit, int $offset): array;

    public function count(): int;
}
