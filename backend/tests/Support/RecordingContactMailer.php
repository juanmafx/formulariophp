<?php

declare(strict_types=1);

namespace ContactForm\Tests\Support;

use ContactForm\Contact\ContactInput;
use ContactForm\Contact\ContactMailer;
use ContactForm\Contact\MailDeliveryFailed;
use ContactForm\Contact\ValidatedAttachment;

final class RecordingContactMailer implements ContactMailer
{
    public array $sent = [];
    public bool $shouldFail = false;

    public function send(ContactInput $input, ?ValidatedAttachment $attachment): void
    {
        if ($this->shouldFail) {
            throw new MailDeliveryFailed('Simulated failure.');
        }
        $this->sent[] = [$input, $attachment];
    }
}
