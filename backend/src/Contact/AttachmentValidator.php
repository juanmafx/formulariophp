<?php

declare(strict_types=1);

namespace ContactForm\Contact;

use ContactForm\Http\UploadedFile;
use finfo;

final class AttachmentValidator
{
    public const int MAX_BYTES = 5_242_880;

    private const array ALLOWED_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'text/plain' => 'txt',
    ];

    public function validate(UploadedFile $file): ValidatedAttachment|string
    {
        if ($file->error === UPLOAD_ERR_INI_SIZE || $file->error === UPLOAD_ERR_FORM_SIZE) {
            return 'The attachment must be 5 MB or smaller.';
        }
        if ($file->error !== UPLOAD_ERR_OK || !is_file($file->path)) {
            return 'The attachment could not be uploaded.';
        }

        $size = filesize($file->path);
        if ($size === false || $size === 0 || $size > self::MAX_BYTES) {
            return 'The attachment must be between 1 byte and 5 MB.';
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file->path);
        if (!is_string($mimeType) || !isset(self::ALLOWED_TYPES[$mimeType])) {
            return 'Attachments must be PDF, JPEG, PNG, or plain text.';
        }

        return new ValidatedAttachment(
            $file->path,
            $this->safeName($file->clientName, self::ALLOWED_TYPES[$mimeType]),
            $mimeType,
        );
    }

    private function safeName(string $clientName, string $extension): string
    {
        $baseName = pathinfo(str_replace('\\', '/', $clientName), PATHINFO_FILENAME);
        $sanitized = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName) ?? '';
        $trimmed = substr(trim($sanitized, '-'), 0, 64);

        return ($trimmed === '' ? 'attachment' : $trimmed) . '.' . $extension;
    }
}
