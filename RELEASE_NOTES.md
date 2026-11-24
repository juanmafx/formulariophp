# Release Notes: 1.0.0

Version 1.0.0 is a full rewrite of the 2013 contact form. It keeps the same purpose and adds secure storage, an admin area, and an automated test suite.

**Status:** prepared on branch `modernize/2026`. It has not been tagged, released, or deployed.

## Highlights

- Personal data is encrypted at rest with XChaCha20-Poly1305.
- Admin access requires an Argon2id-protected password and a TOTP code.
- CSRF protection, rate limiting, strict validation, and mail header injection defenses are built in.
- The legacy mail function and bundled frontend libraries are gone.

## Upgrade notes

This release is not a drop-in replacement:

1. Install PHP 8.4 or newer with `pdo_sqlite` and `sodium`.
2. Point the document root at `frontend/public`.
3. Create `.env` from `.env.example` and generate `APP_MASTER_KEY`.
4. Run `backend/bin/migrate.php` and `backend/bin/create-admin.php`.

The previous version stored nothing, so there is no data to migrate.

## Known limitations

See the limitations in [README.md](README.md) and the open items in [docs/SECURITY_AUDIT.md](docs/SECURITY_AUDIT.md).
