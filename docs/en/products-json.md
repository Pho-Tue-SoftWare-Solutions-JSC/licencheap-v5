# products.json (EN)

`products.json` defines product list and metadata used by the module to:

- Display product names
- Send the correct `api_type` to the remote API
- Generate **install/update/verify** commands
- Attach notes / extra commands
- Enable/disable Change IP per product

## File structure

The file is a JSON object where keys are product IDs (string or number):

```json
{
  "21": {
    "name": "directadmin",
    "display_name": "DirectAdmin",
    "api_type": "directadmin",
    "installer_arg": "DirectAdmin",
    "command_alias": "DA"
  }
}
```

## Supported fields

- `name` (string): legacy identifier
- `display_name` (string): UI display name
- `api_type` (string): value sent to the API (defaults to `name`)
- `installer_arg` (string): argument passed to `pre.sh`
- `command_alias` (string): update command suffix combined with prefix (e.g. `RcLicenseDA`)

### Command overrides

You can override command templates:

- `install_command` (string)
- `update_command` (string)
- `verify_command` (string)

Supported tokens:

- `{{installer_url}}` → from App/Server Input1
- `{{installer_arg}}` → from `installer_arg`
- `{{prefix}}` → from App/Server Input2
- `{{update_command}}` → resolved update command with prefix applied
- `{{display_name}}` → display name

Example `verify_command` currently in `products.json`:

```text
touch /etc/.verifylicense ; {{update_command}}
```

### Notes

- `notes` (array<string>): displayed in widgets/admin tab.

### Extra commands

- `extra_commands` (array<object>): adds extra command cards.

Object format:

- `title` (string)
- `command` (string, can use tokens above)

Example:

```json
{
  "title": "Activate FleetSSL",
  "command": "{{update_command}} -fleetssl"
}
```

### Change IP

- `supports_change_ip` (bool): defaults to `true`. If `false`, Change IP will be hidden/disabled.

## Notes

- Missing fields are auto-filled with defaults (e.g. `display_name`, `api_type`, `supports_change_ip`).
- After editing `products.json`, reloading the module/product UI is enough to reflect changes.
