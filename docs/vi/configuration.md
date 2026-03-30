# Cấu hình (VI)

## 1) App/Server fields

Module map các field của App/Server như sau:

- **Hostname** → API Base URL
- **Username** → API Token
- **Input1** → Installer Script URL
- **Input2** → Command Prefix
- **Checkbox** → Dùng endpoint suspend mở rộng `Ban2/UnBan2`

Giá trị mặc định (trong code):

- Base URL: `https://api.resellercenter.ir/rc/`
- Installer URL: `https://mirror.resellercenter.ir/pre.sh`
- Command prefix: `RcLicense`

## 2) Product options

Các option chính trong product/module settings:

- **Product**: load từ `products.json`
- **Licensed IP**: IP gắn license
- **Max IP changes**: giới hạn đổi IP ở phía HostBill
- **Reference key prefix**: prefix của `reference_key`
- **Install docs source**:
  - `hybrid`: local commands + append remote `ShowHelp` nếu có
  - `local`: chỉ local commands
  - `remote`: ưu tiên remote `ShowHelp`, lỗi thì fallback local

## 3) Billing cycle → số tháng

Module chuẩn hoá billing cycle về số tháng để gửi API:

- Monthly → 1
- Quarterly → 3
- Semi-Annually/Semi-Annual/Semiannually → 6
- Annually/Yearly → 12
- Biennially → 24
- Triennially → 36

Nếu billing cycle là số (numeric) thì dùng trực tiếp.

## 4) IP đang dùng được lấy từ đâu?

Thứ tự ưu tiên lấy IP hiện tại:

1. `account_config[ip]`
2. `details[license_ip]`
3. `details[ip]`

Nếu `strict=true` và IP rỗng/không hợp lệ thì module sẽ báo lỗi.

## 5) Giới hạn đổi IP

- Counter: `details[change_ip_count]`
- Limit: option `max_ip_changes`

Nếu `max_ip_changes > 0` và `change_ip_count >= max_ip_changes` thì module chặn đổi IP **trước khi** gọi API.
