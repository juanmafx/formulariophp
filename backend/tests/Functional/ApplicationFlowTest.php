<?php

declare(strict_types=1);

namespace ContactForm\Tests\Functional;

use ContactForm\Admin\AdminAuthenticator;
use ContactForm\Application;
use ContactForm\ApplicationFactory;
use ContactForm\Config\AppConfig;
use ContactForm\Http\Request;
use ContactForm\Http\Response;
use ContactForm\Persistence\DatabaseFactory;
use ContactForm\Persistence\PdoAdminRepository;
use ContactForm\Security\FieldCipher;
use ContactForm\Security\KeyRing;
use ContactForm\Security\PasswordHasher;
use ContactForm\Security\TotpVerifier;
use ContactForm\Tests\Support\ArraySession;
use ContactForm\Tests\Support\FixedClock;
use ContactForm\Tests\Support\RecordingContactMailer;
use PDO;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('pdo_sqlite')]
final class ApplicationFlowTest extends TestCase
{
    private const string PASSWORD = 'correct horse battery staple';
    private const string TOTP_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    private PDO $pdo;
    private ArraySession $session;
    private FixedClock $clock;
    private RecordingContactMailer $mailer;
    private Application $app;

    protected function setUp(): void
    {
        $masterKey = str_repeat('m', 32);
        $this->pdo = DatabaseFactory::connect(':memory:');
        DatabaseFactory::migrate($this->pdo, dirname(__DIR__, 3) . '/db/schema.sql');
        $this->session = new ArraySession();
        $this->clock = new FixedClock();
        $this->mailer = new RecordingContactMailer();
        $hasher = new PasswordHasher(1024, 1, 1);
        $config = new AppConfig($masterKey, ':memory:', 'null://null', 'from@example.com', 'to@example.com', true);
        $this->app = ApplicationFactory::create($config, $this->pdo, $this->session, $this->clock, $this->mailer, $hasher);

        $cipher = new FieldCipher((new KeyRing($masterKey))->totpKey());
        (new PdoAdminRepository($this->pdo))->create(
            'operator',
            $hasher->hash(self::PASSWORD),
            $cipher->encrypt(self::TOTP_SECRET, AdminAuthenticator::totpContext('operator')),
            $this->clock->now(),
        );
    }

    private function get(string $path, array $query = []): Response
    {
        return $this->app->handle(new Request('GET', $path, [], null, '192.0.2.10', $query));
    }

    private function post(string $path, array $fields, string $ip = '192.0.2.10'): Response
    {
        $this->get('/');
        $token = $this->session->data['csrf_token'];

        return $this->app->handle(new Request('POST', $path, ['csrf_token' => $token] + $fields, null, $ip));
    }

    private function contactFields(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '+1 555 010 0000',
            'subject' => 'Project inquiry',
            'message' => 'Hello there',
        ];
    }

    private function code(): string
    {
        return (new TotpVerifier())->codeAt(self::TOTP_SECRET, $this->clock->now());
    }

    private function signIn(): void
    {
        self::assertSame('/admin/verify', $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD])->headers['Location']);
        self::assertSame('/admin', $this->post('/admin/verify', ['code' => $this->code()])->headers['Location']);
    }

    public function testContactPageIsEnglishAndCarriesCsrfAndSecurityHeaders(): void
    {
        $response = $this->get('/');

        self::assertSame(200, $response->status);
        self::assertStringContainsString('<html lang="en">', $response->body);
        self::assertStringContainsString('name="csrf_token"', $response->body);
        self::assertArrayHasKey('Content-Security-Policy', $response->headers);
    }

    public function testRejectsPostWithoutCsrfToken(): void
    {
        $response = $this->app->handle(new Request('POST', '/contact', $this->contactFields()));

        self::assertSame(403, $response->status);
        self::assertSame([], $this->mailer->sent);
    }

    public function testValidSubmissionIsEncryptedMailedAndConfirmedOnce(): void
    {
        $response = $this->post('/contact', $this->contactFields());

        self::assertSame(303, $response->status);
        self::assertSame('/confirmation', $response->headers['Location']);
        self::assertCount(1, $this->mailer->sent);

        $raw = json_encode($this->pdo->query('SELECT * FROM contact_submissions')?->fetchAll(), JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('ada@example.com', $raw);
        self::assertStringContainsString('"delivery_status":"sent"', $raw);

        self::assertSame(200, $this->get('/confirmation')->status);
        self::assertSame(303, $this->get('/confirmation')->status);
    }

    public function testMailFailureKeepsTheSubmissionAndMarksItFailed(): void
    {
        $this->mailer->shouldFail = true;
        $logFile = (string) tempnam(sys_get_temp_dir(), 'contact-log');
        $previousLog = ini_set('error_log', $logFile);

        try {
            $response = $this->post('/contact', $this->contactFields());
        } finally {
            ini_set('error_log', (string) $previousLog);
        }

        $log = (string) file_get_contents($logFile);
        unlink($logFile);

        self::assertSame(303, $response->status);
        self::assertSame('failed', $this->pdo->query('SELECT delivery_status FROM contact_submissions')?->fetchColumn());
        self::assertStringNotContainsString('ada@example.com', $log);
    }

    public function testInvalidSubmissionIsRejectedAndEscaped(): void
    {
        $response = $this->post('/contact', $this->contactFields([
            'email' => "x@example.com\r\nBcc: victim@example.com",
            'name' => '"><script>alert(1)</script>',
        ]));

        self::assertSame(422, $response->status);
        self::assertStringNotContainsString('<script>', $response->body);
        self::assertStringContainsString('&lt;script&gt;', $response->body);
        self::assertSame([], $this->mailer->sent);
    }

    public function testContactEndpointIsRateLimited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            self::assertSame(303, $this->post('/contact', $this->contactFields())->status);
        }

        self::assertSame(429, $this->post('/contact', $this->contactFields())->status);
    }

    public function testAdminRequiresAuthentication(): void
    {
        self::assertSame('/admin/login', $this->get('/admin')->headers['Location']);
    }

    public function testPasswordAloneDoesNotGrantAccess(): void
    {
        $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD]);

        self::assertSame('/admin/login', $this->get('/admin')->headers['Location']);
    }

    public function testWrongPasswordAndUnknownUserShareTheSameResponse(): void
    {
        $wrong = $this->post('/admin/login', ['username' => 'operator', 'password' => 'wrong password value']);
        $unknown = $this->post('/admin/login', ['username' => 'nobody', 'password' => 'wrong password value']);

        self::assertSame(401, $wrong->status);
        self::assertSame(401, $unknown->status);
        self::assertStringContainsString('Invalid credentials.', $wrong->body);
        self::assertStringContainsString('Invalid credentials.', $unknown->body);
    }

    public function testFullTwoFactorSignInRevealsDecryptedAndEscapedSubmissions(): void
    {
        $this->post('/contact', $this->contactFields(['subject' => '<img src=x onerror=alert(1)>']));
        $regenerationsBefore = $this->session->regenerations;
        $this->signIn();

        self::assertGreaterThan($regenerationsBefore, $this->session->regenerations);
        $page = $this->get('/admin');
        self::assertSame(200, $page->status);
        self::assertStringContainsString('ada@example.com', $page->body);
        self::assertStringNotContainsString('<img src=x', $page->body);
    }

    public function testWrongCodeIsRejected(): void
    {
        $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD]);
        $wrongCode = $this->code() === '000000' ? '111111' : '000000';

        self::assertSame(401, $this->post('/admin/verify', ['code' => $wrongCode])->status);
        self::assertSame('/admin/login', $this->get('/admin')->headers['Location']);
    }

    public function testTotpCodeCannotBeReplayed(): void
    {
        $this->signIn();
        $this->post('/admin/logout', []);
        $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD]);

        self::assertSame(401, $this->post('/admin/verify', ['code' => $this->code()])->status);
    }

    public function testSecondFactorAttemptsAreRateLimited(): void
    {
        $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD]);
        $wrongCode = $this->code() === '000000' ? '111111' : '000000';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            self::assertSame(401, $this->post('/admin/verify', ['code' => $wrongCode])->status);
        }

        self::assertSame(429, $this->post('/admin/verify', ['code' => $this->code()])->status);
    }

    public function testLoginAttemptsAreRateLimited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            self::assertSame(401, $this->post('/admin/login', ['username' => 'operator', 'password' => 'wrong password value'])->status);
        }

        self::assertSame(429, $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD])->status);
    }

    public function testPendingSecondFactorExpires(): void
    {
        $this->post('/admin/login', ['username' => 'operator', 'password' => self::PASSWORD]);
        $this->clock->advance(301);

        self::assertSame('/admin/login', $this->post('/admin/verify', ['code' => $this->code()])->headers['Location']);
    }

    public function testIdleSessionExpires(): void
    {
        $this->signIn();
        $this->clock->advance(901);

        self::assertSame('/admin/login', $this->get('/admin')->headers['Location']);
    }

    public function testLogoutEndsTheSession(): void
    {
        $this->signIn();
        $this->post('/admin/logout', []);

        self::assertSame('/admin/login', $this->get('/admin')->headers['Location']);
    }

    public function testUnknownRouteReturns404(): void
    {
        self::assertSame(404, $this->get('/missing-page')->status);
    }
}
