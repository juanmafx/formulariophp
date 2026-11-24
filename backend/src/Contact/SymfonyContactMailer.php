<?php

declare(strict_types=1);

namespace ContactForm\Contact;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\ExceptionInterface as MimeException;

final readonly class SymfonyContactMailer implements ContactMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress,
        private string $toAddress,
    ) {
    }

    public function send(ContactInput $input, ?ValidatedAttachment $attachment): void
    {
        try {
            $email = (new Email())
                ->from(new Address($this->fromAddress))
                ->to(new Address($this->toAddress))
                ->replyTo(new Address($input->email))
                ->subject('Contact form: ' . $input->subject)
                ->text(implode("\n", [
                    'Name: ' . $input->name,
                    'Email: ' . $input->email,
                    'Phone: ' . $input->phone,
                    'Subject: ' . $input->subject,
                    '',
                    $input->message,
                ]));

            if ($attachment !== null) {
                $email->attachFromPath($attachment->path, $attachment->safeName, $attachment->mimeType);
            }

            $this->mailer->send($email);
        } catch (TransportExceptionInterface | MimeException $exception) {
            throw new MailDeliveryFailed('Mail transport failed.', 0, $exception);
        }
    }
}
