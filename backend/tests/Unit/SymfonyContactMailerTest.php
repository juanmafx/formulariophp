<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Contact\ContactInput;
use ContactForm\Contact\MailDeliveryFailed;
use ContactForm\Contact\SymfonyContactMailer;
use ContactForm\Contact\ValidatedAttachment;
use ContactForm\Tests\Support\RecordingTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

final class SymfonyContactMailerTest extends TestCase
{
    private function input(): ContactInput
    {
        return new ContactInput('Ada', 'ada@example.com', '+1 555 010 0000', 'Inquiry', 'Hello');
    }

    public function testUsesFixedSenderAndVisitorAsReplyTo(): void
    {
        $transport = new RecordingTransport();
        (new SymfonyContactMailer(new Mailer($transport), 'from@example.com', 'to@example.com'))->send($this->input(), null);

        $message = $transport->message;
        self::assertInstanceOf(Email::class, $message);
        self::assertSame('from@example.com', $message->getFrom()[0]->getAddress());
        self::assertSame('to@example.com', $message->getTo()[0]->getAddress());
        self::assertSame('ada@example.com', $message->getReplyTo()[0]->getAddress());
        self::assertSame('Contact form: Inquiry', $message->getSubject());
        self::assertSame([], $message->getBcc());
    }

    public function testAttachesTheValidatedFileUnderItsSafeName(): void
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'mail-test');
        file_put_contents($path, 'attachment body');
        $transport = new RecordingTransport();

        try {
            (new SymfonyContactMailer(new Mailer($transport), 'from@example.com', 'to@example.com'))
                ->send($this->input(), new ValidatedAttachment($path, 'notes.txt', 'text/plain'));
            $message = $transport->message;
            self::assertInstanceOf(Email::class, $message);
            self::assertCount(1, $message->getAttachments());
            self::assertSame('notes.txt', $message->getAttachments()[0]->getFilename());
        } finally {
            unlink($path);
        }
    }

    public function testWrapsTransportFailures(): void
    {
        $transport = new RecordingTransport();
        $transport->shouldFail = true;

        $this->expectException(MailDeliveryFailed::class);
        (new SymfonyContactMailer(new Mailer($transport), 'from@example.com', 'to@example.com'))->send($this->input(), null);
    }

    public function testWrapsAddressesTheMimeLayerRejects(): void
    {
        $input = new ContactInput('Ada', "bad\r\nBcc: x@example.com", '+1 555 010 0000', 'Inquiry', 'Hello');

        $this->expectException(MailDeliveryFailed::class);
        (new SymfonyContactMailer(new Mailer(new RecordingTransport()), 'from@example.com', 'to@example.com'))->send($input, null);
    }
}
