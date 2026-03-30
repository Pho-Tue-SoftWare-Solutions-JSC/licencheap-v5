# LicenseCheap v5 for HostBill

## Tiếng Việt

Module `licensecheapv5` là module provisioning License.Cheap / ResellerCenter cho HostBill.

**Tài liệu chi tiết**: xem `docs/vi/README.md`.

### Tính năng chính

- Provision license mới (`action=license`)
- Renew (`action=ReNew`)
- Suspend/Unsuspend (`Ban/UnBan` hoặc `Ban2/UnBan2`)
- Change IP (`action=ChangeIp`)
- Load sản phẩm từ `products.json`, sinh install/update/verify commands theo metadata
- Client-area widgets: details / change IP / docs
- Admin service tab: xem nhanh license + thao tác renew/change IP

### Cấu hình nhanh

**App/Server** (HostBill):

- `Hostname`: API base URL (mặc định `https://api.resellercenter.ir/rc/`)
- `Username`: API token
- `Input1`: installer script URL (mặc định `https://mirror.resellercenter.ir/pre.sh`)
- `Input2`: command prefix (mặc định `RcLicense`)
- `Checkbox`: bật endpoint suspend mở rộng (`Ban2/UnBan2`)

**Product** (HostBill):

- Chọn `Product` theo ID trong `products.json`
- `Licensed IP`, `Max IP changes`, `Reference key prefix`, `Install docs source` (hybrid/local/remote)

---

## English

`licensecheapv5` is a HostBill provisioning module for License.Cheap / ResellerCenter.

**Full documentation**: see `docs/en/README.md`.

### Key features

- Provision new licenses (`action=license`)
- Renew (`action=ReNew`)
- Suspend/Unsuspend (`Ban/UnBan` or `Ban2/UnBan2`)
- Change IP (`action=ChangeIp`)
- Load products from `products.json`, generate install/update/verify commands from metadata
- Client-area widgets: details / change IP / docs
- Admin service tab: quick license info + renew/change IP actions

### Quick configuration

**App/Server** (HostBill):

- `Hostname`: API base URL (default `https://api.resellercenter.ir/rc/`)
- `Username`: API token
- `Input1`: installer script URL (default `https://mirror.resellercenter.ir/pre.sh`)
- `Input2`: command prefix (default `RcLicense`)
- `Checkbox`: enable extended suspend endpoints (`Ban2/UnBan2`)

**Product** (HostBill):

- Pick `Product` by ID from `products.json`
- Configure `Licensed IP`, `Max IP changes`, `Reference key prefix`, `Install docs source` (hybrid/local/remote)
