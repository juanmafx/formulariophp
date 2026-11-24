# formulariophp

A self-hosted PHP contact form that encrypts every submission at rest, emails a notification to the site owner, and exposes the stored messages only through an admin area protected by a password and TOTP two-factor authentication.

> **Project history:** originally created in 2013, modernized in 2026.

## Purpose

Small sites often need a contact form and nothing else. Hosted form services hand visitor data to a third party, and the classic single-file PHP mail script is a common source of header injection and spam relay abuse. This project keeps the original idea, a form you can host yourself with almost no infrastructure, and rebuilds it so that the personal data it collects is handled carefully.

## What it does

- Serves a server-rendered, JavaScript-free contact form (name, email, phone, subject, message, optional attachment).
- Validates all input on the server and rejects control characters that could inject mail headers.
- Encrypts each personal field with XChaCha20-Poly1305 before writing it to SQLite.
- Sends a notification through [Symfony Mailer](https://symfony.com/doc/current/mailer.html) using a fixed sender, with the visitor only in `Reply-To`.
- Records whether each notification was delivered, so a mail outage does not lose the message.
- Provides an admin area with Argon2id passwords, TOTP, replay protection, rate limiting, and session timeouts.

Attachments are emailed and never written to the database. Only their sanitized file name is stored, encrypted.

## Architecture

```text
Browser ──> frontend/public/index.php ──> backend Application
                                            ├── Contact    validation, encryption, mail
                                            ├── Admin      password + TOTP authentication
                                            ├── Security   CSRF, rate limits, ciphers, sessions
                                            └── Persistence ──> SQLite (db/schema.sql)
```

```text
backend/    Application code, CLI tools, and tests
frontend/   Web root (public/) and HTML templates
db/         SQL schema
docs/       Architecture and security audit
reports/    Committed test report from a real run
scripts/    Report generation
```

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for details.

## Quick start

Requirements: PHP 8.4 or newer with `pdo_sqlite`, `sodium`, `mbstring`, and `fileinfo`, plus Composer.

```bash
composer install --no-dev --no-scripts
cp .env.example .env
php backend/bin/generate-key.php
```

Put the generated value in `APP_MASTER_KEY`, set the other variables in `.env`, then:

```bash
php backend/bin/migrate.php
php backend/bin/create-admin.php your-admin-name
php -S 127.0.0.1:8000 -t frontend/public
```

For local HTTP development only, set `SESSION_COOKIE_SECURE=false`, otherwise the browser will not send the session cookie.

## Usage guide

**Visitors** open `/`, complete the form, and are redirected to a confirmation page.

**Administrators:**

1. Run `create-admin.php`, enter a password of at least 14 characters, and add the printed `otpauth://` URI to an authenticator app. The URI is shown only once.
2. Open `/admin/login`, sign in, and enter the six-digit code.
3. Read submissions at `/admin`, then sign out when finished.

If an authenticator device is lost, replace the credentials from the server:

```bash
php backend/bin/create-admin.php your-admin-name --reset
```

## Configuration

| Variable | Required | Description |
| --- | --- | --- |
| `APP_MASTER_KEY` | Yes | Base64 encoding of 32 random bytes. Subkeys are derived from it. |
| `DATABASE_PATH` | Yes | Absolute path to the SQLite file, outside the web root. |
| `MAILER_DSN` | Yes | Symfony Mailer DSN, for example `smtp://user:pass@host:587`. |
| `MAIL_FROM` | Yes | Fixed sender address on a domain you control. |
| `MAIL_TO` | Yes | Inbox that receives notifications. |
| `SESSION_COOKIE_SECURE` | No | Defaults to `true`. Disable only for local HTTP. |

Back up `APP_MASTER_KEY` separately from the database. **If the key is lost, stored submissions cannot be recovered.**

## Database

[db/schema.sql](db/schema.sql) defines `admin_users`, `contact_submissions`, and `rate_limits`. Check constraints reject non-Argon2id password hashes and unencrypted personal fields.

## Testing

```bash
composer install --no-scripts
composer test
composer analyse
```

The suite has unit, integration, and functional layers. Integration and functional tests need `pdo_sqlite`. The latest report from a real run is in [reports/tests/](reports/tests/), and `scripts/generate-test-report.sh` regenerates it.

## Security notes

- Argon2id password hashing with per-password salts.
- Authenticated field encryption bound to the record and column, so ciphertext cannot be moved between rows.
- CSRF tokens on every state-changing request.
- Rate limits on the form, sign-in, and TOTP verification, keyed by HMAC so raw IP addresses are not stored.
- Strict Content Security Policy with no script execution allowed.

Known limitations:

- Notification emails contain the submission in plain text once they leave the application.
- There is no built-in key rotation command and there are no TOTP recovery codes.
- Rate limiting uses the connecting IP address. Behind a reverse proxy, all visitors share one bucket unless the web server restores the client address.

See [SECURITY.md](SECURITY.md) and [docs/SECURITY_AUDIT.md](docs/SECURITY_AUDIT.md).

## Versioning

The current version is in [VERSION](VERSION). Changes are recorded in [CHANGELOG.md](CHANGELOG.md).

## License

This repository does not currently include a license file, so no reuse rights are granted by default. Third-party dependencies are installed through Composer and remain under their own licenses.

## Author

Juan Manuel Ramallo
