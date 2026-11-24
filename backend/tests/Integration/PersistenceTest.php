<?php

declare(strict_types=1);

namespace ContactForm\Tests\Integration;

use ContactForm\Contact\ContactInput;
use ContactForm\Contact\SubmissionVault;
use ContactForm\Persistence\DatabaseFactory;
use ContactForm\Persistence\PdoAdminRepository;
use ContactForm\Persistence\PdoRateLimitStore;
use ContactForm\Persistence\PdoSubmissionRepository;
use ContactForm\Security\FieldCipher;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
final class PersistenceTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = DatabaseFactory::connect(':memory:');
        DatabaseFactory::migrate($this->pdo, dirname(__DIR__, 3) . '/db/schema.sql');
    }

    public function testSchemaIsIdempotent(): void
    {
        DatabaseFactory::migrate($this->pdo, dirname(__DIR__, 3) . '/db/schema.sql');

        $tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
            ?->fetchAll(PDO::FETCH_COLUMN);

        self::assertSame(['admin_users', 'contact_submissions', 'rate_limits'], $tables);
    }

    public function testStoresOnlyCiphertextAndRoundTrips(): void
    {
        $repository = new PdoSubmissionRepository($this->pdo);
        $vault = new SubmissionVault(new FieldCipher(str_repeat('k', 32)));
        $id = str_repeat('a', 32);
        $repository->save($vault->seal($id, new ContactInput('Ada', 'ada@example.com', '+15550100000', 'Inquiry', 'Hello'), 'notes.txt', 100));
        $repository->updateDeliveryStatus($id, 'sent');

        $raw = json_encode($this->pdo->query('SELECT * FROM contact_submissions')?->fetchAll(), JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('ada@example.com', $raw);
        self::assertStringNotContainsString('Inquiry', $raw);
        self::assertStringNotContainsString('notes.txt', $raw);

        $opened = $vault->open($repository->findPage(10, 0)[0]);
        self::assertSame('ada@example.com', $opened->contact->email);
        self::assertSame('notes.txt', $opened->attachmentName);
        self::assertSame('sent', $opened->deliveryStatus);
        self::assertSame(1, $repository->count());
    }

    public function testPaginatesNewestFirst(): void
    {
        $repository = new PdoSubmissionRepository($this->pdo);
        $vault = new SubmissionVault(new FieldCipher(str_repeat('k', 32)));
        foreach ([1, 2, 3] as $number) {
            $repository->save($vault->seal(str_repeat((string) $number, 32), new ContactInput('A', 'a@example.com', '+15550100000', 'S', 'M'), null, $number));
        }

        self::assertSame(3, $repository->findPage(1, 0)[0]->createdAt);
        self::assertSame(2, $repository->findPage(1, 1)[0]->createdAt);
    }

    public function testSchemaRejectsPlaintextAndNonArgonHashes(): void
    {
        $this->expectException(PDOException::class);

        (new PdoAdminRepository($this->pdo))->create('operator', '$2y$10$notargon', 'v1.secret', 1);
    }

    public function testSchemaRejectsPlaintextSubmissionFields(): void
    {
        $this->expectException(PDOException::class);

        $this->pdo->exec(
            "INSERT INTO contact_submissions VALUES ('" . str_repeat('a', 32) . "', 'plain', 'v1.a', 'v1.a', 'v1.a', 'v1.a', NULL, 'sent', 1)",
        );
    }

    public function testAdminLookupIsCaseInsensitiveAndTotpStepOnlyAdvances(): void
    {
        $repository = new PdoAdminRepository($this->pdo);
        $id = $repository->create('operator', '$argon2id$v=19$stub', 'v1.secret', 1);

        self::assertSame($id, $repository->findByUsername('OPERATOR')?->id);
        self::assertTrue($repository->advanceTotpStep($id, 10, 2));
        self::assertFalse($repository->advanceTotpStep($id, 10, 3));
        self::assertFalse($repository->advanceTotpStep($id, 9, 3));
        self::assertTrue($repository->advanceTotpStep($id, 11, 3));

        $repository->replaceCredentials($id, '$argon2id$v=19$new', 'v1.rotated', 4);
        self::assertNull($repository->findById($id)?->lastTotpStep);
    }

    public function testRateLimitStoreCountsResetsAndPrunes(): void
    {
        $store = new PdoRateLimitStore($this->pdo);
        $first = str_repeat('a', 64);
        $second = str_repeat('b', 64);

        self::assertSame(1, $store->hit($first, 60, 100));
        self::assertSame(2, $store->hit($first, 60, 110));
        self::assertSame(1, $store->hit($first, 60, 160));
        self::assertSame(1, $store->hit($second, 10, 300));
        self::assertSame(1, (int) $this->pdo->query('SELECT COUNT(*) FROM rate_limits')?->fetchColumn());
    }
}
