<?php

declare(strict_types=1);

namespace ContactForm;

use ContactForm\Admin\AdminAuthenticator;
use ContactForm\Config\AppConfig;
use ContactForm\Contact\AttachmentValidator;
use ContactForm\Contact\ContactMailer;
use ContactForm\Contact\ContactService;
use ContactForm\Contact\ContactValidator;
use ContactForm\Contact\SubmissionVault;
use ContactForm\Contact\SymfonyContactMailer;
use ContactForm\Persistence\PdoAdminRepository;
use ContactForm\Persistence\PdoRateLimitStore;
use ContactForm\Persistence\PdoSubmissionRepository;
use ContactForm\Security\Clock;
use ContactForm\Security\CsrfTokenManager;
use ContactForm\Security\FieldCipher;
use ContactForm\Security\KeyRing;
use ContactForm\Security\PasswordHasher;
use ContactForm\Security\RateLimiter;
use ContactForm\Security\SessionStore;
use ContactForm\Security\TotpVerifier;
use ContactForm\View\TemplateRenderer;
use PDO;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

final class ApplicationFactory
{
    public static function create(
        AppConfig $config,
        PDO $pdo,
        SessionStore $session,
        Clock $clock,
        ?ContactMailer $mailer = null,
        ?PasswordHasher $hasher = null,
    ): Application {
        $keys = new KeyRing($config->masterKey);
        $submissions = new PdoSubmissionRepository($pdo);
        $vault = new SubmissionVault(new FieldCipher($keys->submissionKey()));
        $csrf = new CsrfTokenManager($session);
        $mailer ??= new SymfonyContactMailer(
            new Mailer(Transport::fromDsn($config->mailerDsn)),
            $config->mailFrom,
            $config->mailTo,
        );

        return new Application(
            new ContactValidator(),
            new AttachmentValidator(),
            new ContactService($submissions, $vault, $mailer, $clock),
            $submissions,
            $vault,
            new AdminAuthenticator(
                new PdoAdminRepository($pdo),
                $hasher ?? new PasswordHasher(),
                new TotpVerifier(),
                new FieldCipher($keys->totpKey()),
                $session,
                $csrf,
                $clock,
            ),
            $csrf,
            new RateLimiter(new PdoRateLimitStore($pdo), $clock, $keys->rateLimitKey()),
            $session,
            new TemplateRenderer(dirname(__DIR__, 2) . '/frontend/templates'),
            $config->secureCookies,
        );
    }
}
