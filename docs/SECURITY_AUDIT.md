# Security Audit

Scope: the `master` code as of the 2026 modernization, the rewritten application, and a name-level scan of repository history and remote branches. This is a self-review by the maintainer's tooling and is not an independent penetration test.

## Fixed in 1.0.0

| Area | Previous behavior | Resolution |
| --- | --- | --- |
| Mail header injection | Visitor input reached mail headers | Fixed sender, validated `Reply-To`, control characters rejected, Symfony Mailer |
| Attachment handling | Client-supplied type and name were trusted | Content-based allowlist, size limit, sanitized file names |
| Data over-collection | Every posted field was forwarded | Explicit field allowlist |
| CSRF | No protection | Session-bound tokens on all POST routes |
| Abuse | No rate limiting | Limits on the form, sign-in, and TOTP |
| Output encoding | No escaping layer or Content Security Policy existed | Central HTML escaping and strict CSP |
| Debug helpers | Client-side autofill and console logging shipped | Removed, and the application ships no JavaScript |
| Legacy frontend | Bundled Bootstrap, jQuery 1.10.2, and a mirrored documentation site | Removed from the tree |
| Mirror metadata | Website-copier cache and logs were tracked | Removed from the tree |
| Data at rest | No storage or access control model | Authenticated encryption and a two-factor admin area |

## Fixed during the pre-release review

Each item was reproduced against the first 1.0.0 candidate and has a regression test, except the CLI and CI items.

| Area | Defect | Resolution |
| --- | --- | --- |
| Username enumeration | Unknown usernames took twice as long as known ones | One Argon2id computation on both paths |
| Session revocation | Sessions survived `create-admin --reset` | Sessions are bound to the current credentials |
| Cookie configuration | A mistyped `SESSION_COOKIE_SECURE` silently disabled `Secure` | Invalid values stop the application |
| Rate limit bypass | Rotating addresses inside one IPv6 /64 avoided every limit | IPv6 clients are limited per /64 |
| Rate limit storage | Blocked clients could keep adding rows | Account buckets are only touched after the network check passes |
| Database permissions | A database created by the web entry point followed the process umask | New database files are created with mode 0600 |
| HSTS scope | `includeSubDomains` could force HTTPS on unrelated subdomains | HSTS applies to the application host only |
| Admin password limit | The CLI accepted passwords that sign-in always rejects | The CLI enforces the same 1024-byte limit |
| CI toolchain | The pinned Composer version had published high-severity advisories | Updated to Composer 2.11.2 |

## Verification performed

- PHPUnit suite on PHP 8.4 and 8.5. The committed report in `reports/tests/` comes from the PHP 8.5 run.
- PHPStan at level 8 on `backend/src`.
- `composer audit` with no known advisories at the time of the run.
- gitleaks with redaction. Its only findings are the public RFC 6238 test secret used as a test fixture.
- An end-to-end run against the PHP built-in server with production cookie settings and a local SMTP sink, covering injection, upload rejection, encryption at rest, two-factor sign-in, code replay, and session revocation. It used plain HTTP on the loopback interface, so TLS was not exercised.

## Open items

| Item | Status |
| --- | --- |
| Git history | Earlier commits still contain the legacy code and the third-party mirror. Rewriting history is an owner decision. |
| Legacy archive tag | The `archive/actulizacion` tag keeps unmaintained code, including a hardcoded database connection with a blank password. Keeping it is an owner decision. |
| Published legacy version | The default branch keeps the legacy code until this work is merged. |
| Plain-text notifications | Email content is outside the application's encryption boundary. |
| Key rotation | No automated rotation command exists yet. |
| TOTP recovery | No recovery codes. Recovery requires server access. |
| Account throttling | Per-account sign-in limits can temporarily delay a legitimate administrator during an attack. |
| Anonymous sessions | Each new visitor creates a server-side session for its CSRF token, so request floods must be limited at the web server. |
| Independent review | No third-party audit or production load test has been performed. |
