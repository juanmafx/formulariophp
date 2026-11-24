<?php

declare(strict_types=1);

namespace ContactForm\Contact;

use ContactForm\Security\Clock;

final readonly class ContactService
{
    public function __construct(
        private SubmissionRepository $submissions,
        private SubmissionVault $vault,
        private ContactMailer $mailer,
        private Clock $clock,
    ) {
    }

    public function submit(ContactInput $input, ?ValidatedAttachment $attachment): string
    {
        $id = bin2hex(random_bytes(16));
        $this->submissions->save($this->vault->seal($id, $input, $attachment?->safeName, $this->clock->now()));

        try {
            $this->mailer->send($input, $attachment);
            $this->submissions->updateDeliveryStatus($id, 'sent');
        } catch (MailDeliveryFailed) {
            $this->submissions->updateDeliveryStatus($id, 'failed');
            error_log('Contact notification delivery failed for submission ' . $id);
        }

        return $id;
    }
}
