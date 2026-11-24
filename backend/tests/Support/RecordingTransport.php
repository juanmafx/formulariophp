<?php

declare(strict_types=1);

namespace ContactForm\Tests\Support;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;

final class RecordingTransport implements TransportInterface
{
    public ?RawMessage $message = null;
    public bool $shouldFail = false;

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        if ($this->shouldFail) {
            throw new TransportException('Simulated transport failure.');
        }
        $this->message = $message;

        return new SentMessage($message, $envelope ?? new Envelope(new Address('from@example.com'), [new Address('to@example.com')]));
    }

    public function __toString(): string
    {
        return 'recording://';
    }
}
