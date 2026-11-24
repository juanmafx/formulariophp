<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Contact\ContactValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContactValidatorTest extends TestCase
{
    private static function validFields(): array
    {
        return [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '+1 (555) 010-0000',
            'subject' => 'Project inquiry',
            'message' => "First line\r\nSecond line",
        ];
    }

    public function testAcceptsValidInputAndNormalizesLineEndings(): void
    {
        $result = (new ContactValidator())->validate(self::validFields());

        self::assertTrue($result->isValid());
        self::assertSame("First line\nSecond line", $result->input?->message);
    }

    public function testAcceptsNonAsciiNames(): void
    {
        $result = (new ContactValidator())->validate(['name' => 'Zoë Müller'] + self::validFields());

        self::assertTrue($result->isValid());
    }

    #[DataProvider('invalidFields')]
    public function testRejectsInvalidField(string $field, mixed $value): void
    {
        $result = (new ContactValidator())->validate([$field => $value] + self::validFields());

        self::assertFalse($result->isValid());
        self::assertArrayHasKey($field, $result->errors);
    }

    public static function invalidFields(): array
    {
        return [
            'empty name' => ['name', '   '],
            'long name' => ['name', str_repeat('a', 101)],
            'name array' => ['name', ['x']],
            'invalid utf8' => ['name', "\xC3\x28"],
            'email header injection lf' => ['email', "a@example.com\nBcc: b@example.com"],
            'email header injection crlf' => ['email', "a@example.com\r\nBcc: b@example.com"],
            'malformed email' => ['email', 'not-an-email'],
            'long email' => ['email', str_repeat('a', 250) . '@example.com'],
            'phone letters' => ['phone', 'call-me'],
            'short phone' => ['phone', '123'],
            'subject header injection' => ['subject', "Hello\r\nBcc: b@example.com"],
            'subject unicode line separator' => ['subject', "Hello\u{2028}Bcc: b@example.com"],
            'subject null byte' => ['subject', "Hello\0"],
            'long subject' => ['subject', str_repeat('a', 151)],
            'empty message' => ['message', ''],
            'long message' => ['message', str_repeat('a', 5001)],
            'message control character' => ['message', "Hello\x07"],
        ];
    }
}
