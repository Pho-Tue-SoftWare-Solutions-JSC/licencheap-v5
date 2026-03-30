# Configuration (EN)

## 1) App/Server fields

The module maps HostBill App/Server fields as follows:

- **Hostname** → API Base URL
- **Username** → API Token
- **Input1** → Installer Script URL
- **Input2** → Command Prefix
- **Checkbox** → Use extended suspend endpoints `Ban2/UnBan2`

Defaults (in code):

- Base URL: `https://api.resellercenter.ir/rc/`
- Installer URL: `https://mirror.resellercenter.ir/pre.sh`
- Command prefix: `RcLicense`

## 2) Product options

Main product/module options:

- **Product**: loaded from `products.json`
- **Licensed IP**: licensed IP
- **Max IP changes**: HostBill-side IP change limit
- **Reference key prefix**: prefix for `reference_key`
- **Install docs source**:
  - `hybrid`: local commands + append remote `ShowHelp` when available
  - `local`: local commands only
  - `remote`: prefer remote `ShowHelp`, fallback to local metadata on errors

## 3) Billing cycle → months

The module normalizes the billing cycle into months for API calls:

- Monthly → 1
- Quarterly → 3
- Semi-Annually/Semi-Annual/Semiannually → 6
- Annually/Yearly → 12
- Biennially → 24
- Triennially → 36

If HostBill provides a numeric cycle, it is used directly.

## 4) Current IP resolution

Priority order:

1. `account_config[ip]`
2. `details[license_ip]`
3. `details[ip]`

With `strict=true`, an empty/invalid IP triggers an error.

## 5) Change IP limit

- Counter: `details[change_ip_count]`
- Limit: `max_ip_changes`

If `max_ip_changes > 0` and `change_ip_count >= max_ip_changes`, the module blocks the change **before** calling the remote API.
