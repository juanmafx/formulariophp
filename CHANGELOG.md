# Changelog

This project follows [Semantic Versioning](https://semver.org/).

## [1.0.0] - Unreleased

### Added

- Layered `backend/` and `frontend/` structure with Composer autoloading.
- Encrypted SQLite submission store and `db/schema.sql`.
- Admin area with Argon2id passwords, TOTP two-factor authentication, and replay protection.
- CSRF protection, rate limiting, server-side validation, and security headers.
- Unit, integration, and functional tests, PHPStan, a committed test report, and GitHub Actions CI.
- Documentation, a security policy, and a self-review security audit.
- Session revocation on credential reset, IPv6 /64 rate limiting, and strict validation of the cookie security setting.

### Changed

- **Breaking:** the entry point moved to `frontend/public`, and the legacy PHP page URLs no longer exist.
- **Breaking:** configuration now comes from environment variables.
- **Breaking:** form field names, UI text, and code are now entirely in English.
- Mail delivery now uses Symfony Mailer.

### Removed

- Hand-written MIME mail function.
- Bundled Bootstrap, jQuery, and the mirrored third-party documentation site with its cache files.
- Client-side autofill and debug logging.

## [0.0.2] - 2021-11-27

- Last release before the modernization.

## [0.0.0.1] - 2021-11-04

- First tagged version.
