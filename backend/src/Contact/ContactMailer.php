<?php

declare(strict_types=1);

namespace ContactForm\Contact;

interface ContactMailer
{
    public function send(ContactInput $input, ?ValidatedAttachment $attachment): void;
}
