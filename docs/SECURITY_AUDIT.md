# Security Audit

Scope: the `master` code as of the 2026 modernization, the rewritten application, and a name-level scan of repository history and remote branches. This is a self-review by the maintainer's tooling and is not an independent penetration test.

## Fixed in 1.0.0

| Area | Previous behavior | Resolution |
| --- | --- | --- |
| Mail header injection | Visitor input reached mail headers | Fixed sender, validated `Reply-To`, control characters rejected, Symfony Mailer |
| Attachment handling | Client-supplied type and name were trusted | Content-based allowlist, size limit, generated safe names |
| Data over-collection | Every posted field was forwarded | Explicit field allowlist |
| CSRF | No protection | Session-bound tokens on all POST routes |
| Abuse | No rate limiting | Limits on the form, sign-in, and TOTP |
| Output encoding | No escaping layer or Content Security Policy existed | Central contextual escaping and strict CSP |
| Debug helpers | Client-side autofill and console logging shipped | Removed, and the application ships no JavaScript |
| Legacy frontend | Bundled Bootstrap, jQuery 1.10.2, and a mirrored documentation site | Removed from the tree |
| Mirror metadata | Website-copier cache and logs were tracked | Removed from the tree |
| Data at rest | No storage or access control model | Authenticated encryption and a two-factor admin area |

## Verification performed

- PHPUnit suite on PHP 8.4 and 8.5, with results in `reports/tests/`.
- PHPStan at level 8 on `backend/src`.
- `composer audit` with no known advisories at the time of the run.
- An end-to-end HTTP run against the PHP built-in server covering upload, encryption at rest, two-factor sign-in, and code replay.

## Open items

| Item | Status |
| --- | --- |
| Git history | Earlier commits still contain the legacy code and the third-party mirror. Rewriting history is an owner decision. |
| Legacy remote branches | Unmerged branches contain unmaintained code, including a hardcoded database connection with a blank password. They should be reviewed and deleted by the owner. |
| Published legacy version | The default branch keeps the legacy code until this work is merged. |
| Plain-text notifications | Email content is outside the application's encryption boundary. |
| Key rotation | No automated rotation command exists yet. |
| TOTP recovery | No recovery codes. Recovery requires server access. |
| Account throttling | Per-account sign-in limits can temporarily delay a legitimate administrator during an attack. |
| Independent review | No third-party audit or production load test has been performed. |
