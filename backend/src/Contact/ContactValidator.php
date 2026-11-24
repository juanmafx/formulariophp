<?php

declare(strict_types=1);

namespace ContactForm\Contact;

final class ContactValidator
{
    private const int MAX_NAME = 100;
    private const int MAX_EMAIL = 254;
    private const int MAX_SUBJECT = 150;
    private const int MAX_MESSAGE = 5000;
    private const string PHONE_PATTERN = '/\A\+?[0-9][0-9 ().-]{5,31}\z/';

    public function validate(array $fields): ValidationResult
    {
        $name = $this->singleLine($fields['name'] ?? '');
        $email = $this->singleLine($fields['email'] ?? '');
        $phone = $this->singleLine($fields['phone'] ?? '');
        $subject = $this->singleLine($fields['subject'] ?? '');
        $message = $this->multiLine($fields['message'] ?? '');

        $errors = [];
        if ($name === null || $name === '' || mb_strlen($name) > self::MAX_NAME) {
            $errors['name'] = 'Enter your name (up to 100 characters).';
        }
        if (
            $email === null
            || strlen($email) > self::MAX_EMAIL
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if ($phone === null || preg_match(self::PHONE_PATTERN, $phone) !== 1) {
            $errors['phone'] = 'Enter a valid phone number.';
        }
        if ($subject === null || $subject === '' || mb_strlen($subject) > self::MAX_SUBJECT) {
            $errors['subject'] = 'Enter a subject (up to 150 characters).';
        }
        if ($message === null || $message === '' || mb_strlen($message) > self::MAX_MESSAGE) {
            $errors['message'] = 'Enter a message (up to 5000 characters).';
        }

        if ($errors !== [] || $name === null || $email === null || $phone === null || $subject === null || $message === null) {
            return new ValidationResult(null, $errors);
        }

        return new ValidationResult(new ContactInput($name, $email, $phone, $subject, $message), []);
    }

    private function singleLine(mixed $value): ?string
    {
        if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || preg_match('/[\p{Cc}\x{2028}\x{2029}]/u', $value) === 1) {
            return null;
        }

        return trim($value);
    }

    private function multiLine(mixed $value): ?string
    {
        if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
            return null;
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        if (preg_match('/[^\P{Cc}\n\t]/u', $normalized) === 1) {
            return null;
        }

        return trim($normalized);
    }
}
