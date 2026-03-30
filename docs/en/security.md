# Security (EN)

## API token

- The token is configured in the App/Server **Username** field.
- Do not commit tokens to git and do not store them in files.

## Debug logs may expose the token

In `class.api.php`, the module logs:

- `HBDebug::debug("HB >> LicenseCheapV5", ["url" => ..., "payload" => ...])`

The payload includes `token`.

Recommendations:

- Enable debug only when troubleshooting
- Mask the token before sharing logs
- For strict production environments, patch the module to redact tokens in debug output

## File access

- Ensure `includes/modules/Hosting/licensecheapv5/` is not publicly accessible.
- `products.json` should not contain secrets, but direct access should still be restricted.
