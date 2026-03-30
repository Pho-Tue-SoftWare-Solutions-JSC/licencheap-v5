# Installation (EN)

This document explains how to install the `licensecheapv5` module into HostBill.

## Requirements

- A working HostBill installation
- PHP extensions: **cURL** and **JSON**
- Target servers must run install/update commands as **root** (per installer instructions)

## Module placement

Place the module directory under HostBill Hosting modules, e.g.:

```text
includes/modules/Hosting/licensecheapv5/
  class.api.php
  class.licensecheapv5.php
  products.json
  admin/
  templates/
  widgets/
  docs/
```

> Note: The folder name must remain `licensecheapv5` so HostBill can resolve templates/controllers/widgets correctly.

## Enable the module

1. Go to **Admin → Settings → Apps / Modules** (menu may vary by HostBill version)
2. Locate **LicenseCheap v5**
3. Click **Install/Activate**

After upgrades, HostBill typically triggers the module `upgrade()` routine.

## Create an App/Server

Create an App/Server for **LicenseCheap v5** and configure:

- **Hostname**: API base URL (default `https://api.resellercenter.ir/rc/`)
- **Username**: API token
- **Input1**: installer script URL (default `https://mirror.resellercenter.ir/pre.sh`)
- **Input2**: command prefix (default `RcLicense`)
- **Checkbox**: enable extended suspend endpoints `Ban2/UnBan2`

## Configure a Product

In the product module settings:

- **Product**: select an ID from `products.json`
- **Licensed IP**: the IP to license
- **Max IP changes**: HostBill-side limit (0 = unlimited)
- **Reference key prefix**: internal reference key prefix (default `LC-`)
- **Install docs source**: `hybrid` / `local` / `remote`

## Quick smoke test

- Use **Test Connection** on the App/Server (valid token)
- Create a test service and run Provision/Create
- Check client-area widgets (if assigned to the product)
- Use the admin service tab to try Renew / Change IP

## Uninstall notes

- To remove a license from a target server, use the **Remove** command shown in widget/docs.
- To remove the module from HostBill, follow HostBill's module uninstall procedure (depends on version).
