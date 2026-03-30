# Troubleshooting (EN)

## 1) Test Connection fails

Connection test order:

1. Call `bundle.php` (structured JSON)
2. On failure, fallback to `action=ShowHelp` with `serviceid=10`

### Common causes

- Wrong/empty token
- Wrong base URL (missing `/rc/` or wrong domain)
- Firewall blocking outbound traffic
- Missing PHP cURL extension
- API returning HTML / upstream errors

### Suggestions

- Re-check App/Server `Hostname` and `Username`
- Check network latency/outbound HTTPS

## 2) cURL errors / timeouts

If you see:

- `Unknown cURL error`
- timeouts

Check:

- DNS resolution
- outbound HTTPS
- `open_basedir` / `disable_functions` restrictions

## 3) Docs/commands not shown

Commands are generated from `products.json`. Verify:

- The product ID is selected correctly
- The product has `command_alias`
- File permissions allow reading `products.json`

## 4) Change IP invalid

The module validates IPs using `FILTER_VALIDATE_IP`.

- Invalid new IP → `New IP address is invalid`
- Empty/invalid current IP → `Current licensed IP is empty or invalid`

## 5) Change IP blocked by limit

When `max_ip_changes > 0` and the counter has reached the limit, the module blocks the request before calling the API.

You can reset the counter from the admin service tab (reset IP counter).

## 6) Debug log note (important)

In `class.api.php` the module logs request/response via `HBDebug::debug(...)`, and the payload **includes the token**.

- Enable debug only when needed
- Do not share logs externally without masking the token

> If you want production-hardening, you can patch the code to mask tokens in debug output.
