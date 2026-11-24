# Architecture

## Goals

1. Keep deployment simple: one PHP application and one SQLite file.
2. Treat personal data as a liability: encrypt it before storage and decrypt it only for an authenticated administrator.
3. Keep the web root minimal so that source, configuration, and data are never directly reachable.

## Module structure

```text
backend/
├── bin/                     Operator commands (keys, migration, admin accounts)
├── bootstrap.php            Autoloading and environment loading
├── src/
│   ├── Application.php      Routing and request handling
│   ├── ApplicationFactory.php  Object wiring
│   ├── Admin/               Admin model and two-step authentication
│   ├── Config/              Validated environment configuration
│   ├── Contact/             Validation, attachment checks, sealing, mail
│   ├── Http/                Request, response, uploads, security headers
│   ├── Persistence/         PDO repositories and database setup
│   ├── Security/            Ciphers, hashing, TOTP, CSRF, rate limits, sessions
│   └── View/                Escaping template renderer
└── tests/
    ├── Unit/
    ├── Integration/
    ├── Functional/
    └── Support/
frontend/
├── public/                  The only web-accessible directory
│   ├── index.php
│   └── assets/css/app.css
└── templates/               HTML views
db/schema.sql                Database definition
```

Dependencies point inward. `Contact` and `Admin` define repository and mailer interfaces, and `Persistence` and `SymfonyContactMailer` implement them.

## Submission flow

1. The CSRF token and the per-client rate limit are checked.
2. `ContactValidator` and `AttachmentValidator` validate the request. Attachment types are detected from file content.
3. `SubmissionVault` encrypts each field and the record is stored as `pending`.
4. `SymfonyContactMailer` sends the notification.
5. The record becomes `sent` when the mail transport accepts the message, or `failed` otherwise. A failed email keeps the stored text, but the attachment is not retained.
6. The browser is redirected, so a refresh cannot resubmit the form.

## Admin authentication flow

1. The password is verified with Argon2id. Unknown usernames perform one Argon2id computation at the same cost to reduce timing differences.
2. The session ID is regenerated, any earlier sign-in in that session is dropped, and the session enters a pending state that lasts five minutes.
3. The TOTP code is checked with a tolerance of one 30-second step.
4. The accepted step is advanced with an atomic conditional `UPDATE`, so each code works once.
5. The session ID is regenerated again. Sessions expire after 15 idle minutes or 8 hours in total, and end immediately when the account's credentials are reset.

## Cryptography

| Purpose | Primitive |
| --- | --- |
| Password storage | Argon2id, 64 MiB, 3 iterations, 1 thread |
| Field encryption | XChaCha20-Poly1305 with a random 192-bit nonce |
| Key separation | libsodium KDF subkeys from `APP_MASTER_KEY` |
| Rate limit identifiers | HMAC-SHA-256 |
| Second factor | TOTP (RFC 6238), SHA-1, six digits, 30 seconds |

SHA-1 is the TOTP interoperability default across authenticator apps, and it is used there only inside HMAC.

## Recommended deployment

- Point the web server document root at `frontend/public` and route unknown paths to `index.php`.
- Serve only over HTTPS and keep `SESSION_COOKIE_SECURE=true`. The application sends HSTS for its own host only. Add `includeSubDomains` at the web server only if every subdomain supports HTTPS.
- Store the database outside the web root with permissions limited to the PHP user.
- Provide secrets through the environment or a secret manager.
- Set PHP `upload_max_filesize` to at least 5 MB and `post_max_size` slightly higher.
- If a reverse proxy is used, restore the client address at the web server so that rate limits apply per visitor. IPv4 clients are limited per address and IPv6 clients per /64 network.

SQLite suits the low write volume of a contact form on a single host. A multi-host deployment would need new implementations of the repository interfaces.
