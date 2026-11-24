<?php

declare(strict_types=1);

namespace ContactForm\Admin;

use ContactForm\Security\Clock;
use ContactForm\Security\CsrfTokenManager;
use ContactForm\Security\FieldCipher;
use ContactForm\Security\PasswordHasher;
use ContactForm\Security\SessionStore;
use ContactForm\Security\TotpVerifier;

final readonly class AdminAuthenticator
{
    private const string PENDING_ID = 'admin_pending_id';
    private const string PENDING_AT = 'admin_pending_at';
    private const string ADMIN_ID = 'admin_id';
    private const string AUTHENTICATED_AT = 'admin_authenticated_at';
    private const string LAST_SEEN_AT = 'admin_last_seen_at';
    private const string CREDENTIAL_TAG = 'admin_credential_tag';
    private const int PENDING_TTL = 300;
    private const int IDLE_TTL = 900;
    private const int ABSOLUTE_TTL = 28800;
    public const int MAX_PASSWORD_BYTES = 1024;

    public function __construct(
        private AdminRepository $admins,
        private PasswordHasher $hasher,
        private TotpVerifier $totp,
        private FieldCipher $totpCipher,
        private SessionStore $session,
        private CsrfTokenManager $csrf,
        private Clock $clock,
    ) {
    }

    public static function totpContext(string $username): string
    {
        return 'admin_users:' . mb_strtolower($username) . ':totp_secret';
    }

    public function attemptPassword(string $username, #[\SensitiveParameter] string $password): bool
    {
        if (strlen($password) > self::MAX_PASSWORD_BYTES) {
            return false;
        }

        $admin = $this->admins->findByUsername($username);
        if ($admin === null) {
            $this->hasher->burnVerification($password);

            return false;
        }
        if (!$this->hasher->verify($password, $admin->passwordHash)) {
            return false;
        }
        if ($this->hasher->needsRehash($admin->passwordHash)) {
            $this->admins->updatePasswordHash($admin->id, $this->hasher->hash($password), $this->clock->now());
        }

        $this->session->regenerate();
        $this->session->remove(self::ADMIN_ID);
        $this->session->remove(self::AUTHENTICATED_AT);
        $this->session->remove(self::LAST_SEEN_AT);
        $this->session->set(self::PENDING_ID, $admin->id);
        $this->session->set(self::PENDING_AT, $this->clock->now());
        $this->session->set(self::CREDENTIAL_TAG, self::credentialTag($admin));
        $this->csrf->rotate();

        return true;
    }

    public function pendingAdminId(): ?int
    {
        $id = $this->session->get(self::PENDING_ID);
        $startedAt = $this->session->get(self::PENDING_AT);
        if (!is_int($id) || !is_int($startedAt)) {
            return null;
        }
        if ($this->clock->now() - $startedAt > self::PENDING_TTL) {
            $this->session->remove(self::PENDING_ID);
            $this->session->remove(self::PENDING_AT);

            return null;
        }

        return $id;
    }

    public function verifySecondFactor(#[\SensitiveParameter] string $code): bool
    {
        $id = $this->pendingAdminId();
        $admin = $id === null ? null : $this->admins->findById($id);
        if ($admin === null || !$this->holdsCurrentCredentials($admin)) {
            $this->session->remove(self::PENDING_ID);
            $this->session->remove(self::PENDING_AT);

            return false;
        }

        $secret = $this->totpCipher->decrypt($admin->totpSecretEncrypted, self::totpContext($admin->username));
        $now = $this->clock->now();
        $step = $this->totp->matchingStep($secret, $code, $admin->lastTotpStep, $now);
        if ($step === null || !$this->admins->advanceTotpStep($admin->id, $step, $now)) {
            return false;
        }

        $this->session->regenerate();
        $this->session->remove(self::PENDING_ID);
        $this->session->remove(self::PENDING_AT);
        $this->session->set(self::ADMIN_ID, $admin->id);
        $this->session->set(self::AUTHENTICATED_AT, $now);
        $this->session->set(self::LAST_SEEN_AT, $now);
        $this->csrf->rotate();

        return true;
    }

    public function isAuthenticated(): bool
    {
        $id = $this->session->get(self::ADMIN_ID);
        $authenticatedAt = $this->session->get(self::AUTHENTICATED_AT);
        $lastSeenAt = $this->session->get(self::LAST_SEEN_AT);
        if (!is_int($id) || !is_int($authenticatedAt) || !is_int($lastSeenAt)) {
            return false;
        }

        $now = $this->clock->now();
        $admin = $this->admins->findById($id);
        if (
            $now - $lastSeenAt > self::IDLE_TTL
            || $now - $authenticatedAt > self::ABSOLUTE_TTL
            || $admin === null
            || !$this->holdsCurrentCredentials($admin)
        ) {
            $this->logout();

            return false;
        }

        $this->session->set(self::LAST_SEEN_AT, $now);

        return true;
    }

    public function logout(): void
    {
        $this->session->clear();
        $this->csrf->rotate();
    }

    private function holdsCurrentCredentials(AdminUser $admin): bool
    {
        $tag = $this->session->get(self::CREDENTIAL_TAG);

        return is_string($tag) && hash_equals(self::credentialTag($admin), $tag);
    }

    private static function credentialTag(AdminUser $admin): string
    {
        return hash('sha256', $admin->totpSecretEncrypted);
    }
}
