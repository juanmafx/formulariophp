<?php

declare(strict_types=1);

namespace ContactForm\Contact;

use ContactForm\Security\FieldCipher;

final readonly class SubmissionVault
{
    public function __construct(private FieldCipher $cipher)
    {
    }

    public function seal(string $id, ContactInput $input, ?string $attachmentName, int $createdAt): StoredSubmission
    {
        return new StoredSubmission(
            $id,
            $this->cipher->encrypt($input->name, $this->context($id, 'name')),
            $this->cipher->encrypt($input->email, $this->context($id, 'email')),
            $this->cipher->encrypt($input->phone, $this->context($id, 'phone')),
            $this->cipher->encrypt($input->subject, $this->context($id, 'subject')),
            $this->cipher->encrypt($input->message, $this->context($id, 'message')),
            $attachmentName === null ? null : $this->cipher->encrypt($attachmentName, $this->context($id, 'attachment_name')),
            'pending',
            $createdAt,
        );
    }

    public function open(StoredSubmission $stored): DecryptedSubmission
    {
        $id = $stored->id;

        return new DecryptedSubmission(
            $id,
            new ContactInput(
                $this->cipher->decrypt($stored->nameEncrypted, $this->context($id, 'name')),
                $this->cipher->decrypt($stored->emailEncrypted, $this->context($id, 'email')),
                $this->cipher->decrypt($stored->phoneEncrypted, $this->context($id, 'phone')),
                $this->cipher->decrypt($stored->subjectEncrypted, $this->context($id, 'subject')),
                $this->cipher->decrypt($stored->messageEncrypted, $this->context($id, 'message')),
            ),
            $stored->attachmentNameEncrypted === null
                ? null
                : $this->cipher->decrypt($stored->attachmentNameEncrypted, $this->context($id, 'attachment_name')),
            $stored->deliveryStatus,
            $stored->createdAt,
        );
    }

    private function context(string $id, string $field): string
    {
        return 'contact_submissions:' . $id . ':' . $field;
    }
}
