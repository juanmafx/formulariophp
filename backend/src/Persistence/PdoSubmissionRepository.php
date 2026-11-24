<?php

declare(strict_types=1);

namespace ContactForm\Persistence;

use ContactForm\Contact\StoredSubmission;
use ContactForm\Contact\SubmissionRepository;
use PDO;

final readonly class PdoSubmissionRepository implements SubmissionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(StoredSubmission $submission): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO contact_submissions (id, name_encrypted, email_encrypted, phone_encrypted, subject_encrypted,
                message_encrypted, attachment_name_encrypted, delivery_status, created_at)
             VALUES (:id, :name, :email, :phone, :subject, :message, :attachment, :status, :created_at)',
        );
        $statement->execute([
            'id' => $submission->id,
            'name' => $submission->nameEncrypted,
            'email' => $submission->emailEncrypted,
            'phone' => $submission->phoneEncrypted,
            'subject' => $submission->subjectEncrypted,
            'message' => $submission->messageEncrypted,
            'attachment' => $submission->attachmentNameEncrypted,
            'status' => $submission->deliveryStatus,
            'created_at' => $submission->createdAt,
        ]);
    }

    public function updateDeliveryStatus(string $id, string $status): void
    {
        $statement = $this->pdo->prepare('UPDATE contact_submissions SET delivery_status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function findPage(int $limit, int $offset): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM contact_submissions ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset',
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map(
            static fn (array $row): StoredSubmission => new StoredSubmission(
                (string) $row['id'],
                (string) $row['name_encrypted'],
                (string) $row['email_encrypted'],
                (string) $row['phone_encrypted'],
                (string) $row['subject_encrypted'],
                (string) $row['message_encrypted'],
                $row['attachment_name_encrypted'] === null ? null : (string) $row['attachment_name_encrypted'],
                (string) $row['delivery_status'],
                (int) $row['created_at'],
            ),
            $statement->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    public function count(): int
    {
        $statement = $this->pdo->query('SELECT COUNT(*) FROM contact_submissions');

        return $statement === false ? 0 : (int) $statement->fetchColumn();
    }
}
