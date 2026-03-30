# Security (EN)

## Goal

This document covers practical steps to operate the module safely, avoid token leakage, and reduce risk when running installer scripts.

## API token

- The token is configured in the App/Server **Username** field.
- Do not commit tokens to git and do not store them in files.

## Debug logs may expose the token

In `class.api.php`, the module logs request/response data via `HBDebug::debug(...)`.

- `HBDebug::debug("HB >> LicenseCheapV5", ["url" => ..., "payload" => ...])`

The payload includes `token`.

✅ The module has been updated to **redact the token** in debug logs (shown as `***`).

Recommendations:

- Enable debug only when troubleshooting
- Mask the token before sharing logs
- For strict production environments, patch the module to redact tokens in debug output

## File access

- Ensure `includes/modules/Hosting/licensecheapv5/` is not publicly accessible.
- `products.json` should not contain secrets, but direct access should still be restricted.

## Safe use of installer commands

Install/update/remove commands may:

- download scripts from a URL (e.g. `pre.sh`)
- run with `root` privileges

Operational recommendations:

- Only run commands from trusted sources and double-check the domain/URL.
- If possible, download and review (or verify checksums) before executing.
- Test in a staging environment before production.
- Track internal changes when modifying `Input1` (installer URL) or `Input2` (command prefix).
