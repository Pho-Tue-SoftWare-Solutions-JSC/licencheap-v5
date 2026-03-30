# LicenseCheap v5 for HostBill — Documentation (EN)

## Table of contents

- [Installation](installation.md)
- [Configuration](configuration.md)
- [`products.json`](products-json.md)
- [Troubleshooting](troubleshooting.md)
- [Security](security.md)
- [Changelog](changelog.md)

The `licensecheapv5` module is a provisioning module for HostBill, originally structured from:

- `directadminlicense` (HostBill module structure, admin controller, client-area widgets)
- `class.module.php`, `class.license.php`, `class.ssl.php` (module interfaces / lifecycle)
- `rclicensing` (License.Cheap / ResellerCenter legacy logic)
- Extra documentation for install/update/verify commands generated from `products.json` metadata

## Features

- Provision a new license via action `license`
- Renew a license via action `ReNew`
- Suspend / Unsuspend via `Ban` / `UnBan` or `Ban2` / `UnBan2`
- Change IP via action `ChangeIp`
- Load product list from `products.json` using `id => name` mapping (compatible with `rclicensing`)
- Generate install/update/verify commands from JSON metadata (override without touching PHP)
- Optionally fetch extra `ShowHelp` content from the API and display it in admin/client area
- Three client-area widgets: details, change IP, docs
- Admin service tab: quick license info, commands, and renew/change IP actions

## Folder structure

```text
licensecheapv5/
  class.api.php
  class.licensecheapv5.php
  products.json
  admin/
    class.licensecheapv5_controller.php
  templates/
    license.tpl
    ajax.license.tpl
    license.js
  widgets/
    class.licensecheapv5_widget.php
    lcv5_licensedetails/
      widget.lcv5_licensedetails.php
      default.tpl
    lcv5_changeip/
      widget.lcv5_changeip.php
      default.tpl
    lcv5_licensedocs/
      widget.lcv5_licensedocs.php
      default.tpl
  docs/
    README.md
    vi/README.md
    en/README.md
```

## HostBill App/Server configuration

Create an App/Server for module **`LicenseCheap v5`**:

- **Hostname**: API base URL (default `https://api.resellercenter.ir/rc/`)
- **Username**: API token
- **Input1**: installer script URL (default `https://mirror.resellercenter.ir/pre.sh`)
- **Input2**: command prefix (default `RcLicense`)
- **Checkbox**: enable extended suspend endpoints `Ban2/UnBan2`

## HostBill Product configuration

- **Product**: choose product ID from `products.json`
- **Licensed IP**: the primary IP resource of the service
- **Max IP changes**: HostBill-side limit for IP changes (set `0` for unlimited)
- **Reference key prefix**: prefix for internal reference key (default `LC-`)
- **Install docs source**:
  - `hybrid`: local commands + append `ShowHelp` output when available
  - `local`: local commands generated from JSON only
  - `remote`: prefer `ShowHelp` output, fallback to local metadata if needed

## `products.json`

The module reads `products.json` as an object, where keys are the legacy `rclicensing` product IDs.

Example:

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

### Supported fields

- `name`: legacy name / identifier (from `rclicensing`)
- `display_name`: UI display name
- `api_type`: value sent to the API
- `installer_arg`: argument passed to `pre.sh`
- `command_alias`: suffix for the update command (combined with prefix)
- `verify_command`: optional verify command
- `extra_commands`: extra commands (e.g. FleetSSL)
- `notes`: notes displayed in widget/admin
- `supports_change_ip`: enable/disable Change IP button

## API mapping

- `Create()` -> `action=license`
- `Renewal()` / `RenewNow()` -> `action=ReNew`
- `Suspend()` -> `Ban` or `Ban2`
- `Unsuspend()` -> `UnBan` or `UnBan2`
- `Terminate()` -> calls `Ban`/`Ban2` then marks local status as `Expired`
- `LicenseChangeIp()` -> `action=ChangeIp`
- `testConnection()` -> prefers `bundle.php`, falls back to `ShowHelp`

## Extra details stored

The module stores these fields in `details`:

- `reference_key`
- `license_ip`
- `product_id`
- `product_name`
- `status`
- `message`
- `help_raw`
- `change_ip_count`
- `billing_cycle_months`
- `last_action`
- `last_remote_action`
- `command_source`

## Change IP behavior

- Validates IP using `FILTER_VALIDATE_IP`
- Tracks usage in `change_ip_count`
- Enforces **Max IP changes** in HostBill before calling the API
- On success, updates both `details` and the account config field `ip`

## Client-area widgets

### `lcv5_licensedetails`

- License overview
- Shows status, IP, billing cycle, last API message
- Shows used IP changes
- Renew button

### `lcv5_changeip`

- Dedicated Change IP form for client area
- Validates new IP via `LicenseChangeIp()`
- Displays the configured IP-change limit

### `lcv5_licensedocs`

- Shows install/update/remove/verify commands
- Shows per-product notes
- Shows raw `ShowHelp` output (when available)

## Admin service tab

Controller `admin/class.licensecheapv5_controller.php` attaches a custom template to the service details page:

- Reads `LicenseDetails()`
- Shows overview/debug info, commands/notes/help
- Allows refreshing data/help directly from the tab
- Uses a bootbox form for renew, change IP, and resetting the local IP counter

## Customization

1. Edit `products.json` to add/update products
2. Override `installer_arg`, `command_alias`, `verify_command`, `extra_commands`
3. Replace **Input1** if you use a private/whitelabel installer URL
4. Replace **Input2** if you want a different command prefix than `RcLicense`

## Current limitations

- The upstream API does not provide a full license listing like the DirectAdmin module; this module focuses on provisioning and managing services created in HostBill
- `Reference Key` is an internal HostBill reference, not a remote license ID
- `Terminate()` keeps the legacy `rclicensing` behavior: sends ban action and marks the local service as `Expired`

## Testing checklist

1. Test connection with a valid token
2. Provision one service for each sample product
3. Test renew, suspend, unsuspend
4. Test Change IP within and above the configured limit
5. Verify client-area widgets and the admin service tab
6. Edit `products.json`, reload the module, confirm UI updates correctly
