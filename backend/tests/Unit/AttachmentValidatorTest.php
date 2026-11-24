<?php

declare(strict_types=1);

namespace ContactForm\Tests\Unit;

use ContactForm\Contact\AttachmentValidator;
use ContactForm\Contact\ValidatedAttachment;
use ContactForm\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

final class AttachmentValidatorTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'attachment-test');
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    private function upload(string $content, string $name, int $error = UPLOAD_ERR_OK): UploadedFile
    {
        file_put_contents($this->path, $content);

        return new UploadedFile($this->path, $name, strlen($content), $error);
    }

    public function testAcceptsAllowedTypeAndDerivesExtensionFromContent(): void
    {
        $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', true);
        $result = (new AttachmentValidator())->validate($this->upload($png, 'photo.php'));

        self::assertInstanceOf(ValidatedAttachment::class, $result);
        self::assertSame('photo.png', $result->safeName);
        self::assertSame('image/png', $result->mimeType);
    }

    public function testSanitizesPathTraversalAndHeaderCharactersInNames(): void
    {
        $result = (new AttachmentValidator())->validate($this->upload('plain text', "../../etc/pass\r\nwd.txt"));

        self::assertInstanceOf(ValidatedAttachment::class, $result);
        self::assertMatchesRegularExpression('/\A[A-Za-z0-9_-]+\.txt\z/', $result->safeName);
    }

    public function testRejectsExecutableContentRegardlessOfExtension(): void
    {
        $result = (new AttachmentValidator())->validate($this->upload("\x7FELF\x02\x01\x01" . str_repeat("\0", 64), 'report.pdf'));

        self::assertIsString($result);
    }

    public function testRejectsOversizedAndEmptyFiles(): void
    {
        $validator = new AttachmentValidator();

        self::assertIsString($validator->validate($this->upload(str_repeat('a', AttachmentValidator::MAX_BYTES + 1), 'big.txt')));
        self::assertIsString($validator->validate($this->upload('', 'empty.txt')));
    }

    public function testReportsUploadErrors(): void
    {
        $validator = new AttachmentValidator();

        self::assertIsString($validator->validate($this->upload('x', 'a.txt', UPLOAD_ERR_INI_SIZE)));
        self::assertIsString($validator->validate($this->upload('x', 'a.txt', UPLOAD_ERR_PARTIAL)));
    }
}
