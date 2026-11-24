# Security Policy

## Supported versions

| Version | Supported |
| --- | --- |
| 1.x | Yes |
| Earlier than 1.0.0 | No. These versions must not be deployed. |

## Reporting a vulnerability

Please use GitHub's private **Report a vulnerability** option on this repository's Security tab. If it is unavailable, open an issue that asks for a private contact channel and leave out all technical details.

Please do not publish details until a fix is available. This is a personal project, so responses are handled on a best-effort basis.

## Operator responsibilities

- Keep `APP_MASTER_KEY` and `.env` out of version control and back them up securely.
- Serve the application only over HTTPS.
- Expose only `frontend/public` through the web server.
- Keep PHP and Composer dependencies up to date.
