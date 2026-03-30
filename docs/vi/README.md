# LicenseCheap v5 for HostBill — Tài liệu (VI)

## Mục lục

- [Cài đặt](installation.md)
- [Cấu hình](configuration.md)
- [`products.json`](products-json.md)
- [Troubleshooting](troubleshooting.md)
- [Bảo mật](security.md)
- [Changelog](changelog.md)

Module `licensecheapv5` là module provisioning cho HostBill, được viết dựa trên:

- `directadminlicense` để lấy structure module HostBill, admin controller, widget client area
- `class.module.php`, `class.license.php`, `class.ssl.php` để bám interface/module lifecycle
- `rclicensing` để giữ logic gốc của License.Cheap / ResellerCenter
- Bổ sung tài liệu install/update/verify commands dựa trên metadata trong `products.json`

## Tính năng

- Provision license mới bằng action `license`
- Renew license bằng action `ReNew`
- Suspend / Unsuspend bằng `Ban` / `UnBan` hoặc `Ban2` / `UnBan2`
- Change IP bằng action `ChangeIp`
- Load danh sách sản phẩm từ `products.json` theo key `id => name` giống `rclicensing`
- Sinh install/update/verify commands từ JSON metadata, có thể override mà không cần sửa PHP
- Lấy thêm nội dung `ShowHelp` từ API khi cần và hiển thị trong admin/client area
- Bộ 3 widget client area: details, đổi IP và docs
- Admin account tab xem nhanh license, commands và thao tác renew/change IP

## Cấu trúc thư mục

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

## Cấu hình App/Server trong HostBill

Tạo App/Server cho module **`LicenseCheap v5`** và điền:

- **Hostname**: API base URL, mặc định `https://api.resellercenter.ir/rc/`
- **Username**: API token
- **Input1**: installer script URL, mặc định `https://mirror.resellercenter.ir/pre.sh`
- **Input2**: command prefix, mặc định `RcLicense`
- **Checkbox**: bật chế độ suspend mở rộng `Ban2/UnBan2`

## Cấu hình Product trong HostBill

- **Product**: chọn product ID từ `products.json`
- **Licensed IP**: resource IP chính của service
- **Max IP changes**: giới hạn đổi IP ở phía HostBill, đặt `0` nếu không giới hạn
- **Reference key prefix**: prefix cho mã tham chiếu nội bộ, mặc định `LC-`
- **Install docs source**:
  - `hybrid`: lệnh local + nội dung `ShowHelp` nếu có
  - `local`: chỉ dùng lệnh local sinh từ JSON
  - `remote`: ưu tiên `ShowHelp`, nếu lỗi thì fallback local

## `products.json`

Module đọc file `products.json` theo kiểu object, key là ID gốc của `rclicensing`.

Ví dụ:

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

### Field hỗ trợ

- `name`: tên hiển thị / tên gốc theo `rclicensing`
- `display_name`: tên để hiển thị trên UI
- `api_type`: giá trị gửi sang API
- `installer_arg`: đối số truyền vào `pre.sh`
- `command_alias`: phần hậu tố của lệnh update, được ghép với prefix
- `verify_command`: lệnh verify nếu sản phẩm cần
- `extra_commands`: lệnh bổ sung (ví dụ FleetSSL)
- `notes`: ghi chú hiển thị trong widget/admin
- `supports_change_ip`: bật/tắt nút đổi IP

## Mapping API

- `Create()` -> `action=license`
- `Renewal()` / `RenewNow()` -> `action=ReNew`
- `Suspend()` -> `Ban` hoặc `Ban2`
- `Unsuspend()` -> `UnBan` hoặc `UnBan2`
- `Terminate()` -> gọi `Ban`/`Ban2` và cập nhật status local thành `Expired`
- `LicenseChangeIp()` -> `action=ChangeIp`
- `testConnection()` -> ưu tiên `bundle.php`, fallback `ShowHelp`

## Dữ liệu lưu trong extra details

Module lưu các field sau trong `details`:

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

## Hành vi đổi IP

- Validate IP mới bằng `FILTER_VALIDATE_IP`
- Đếm số lần đổi IP tại `change_ip_count`
- Nếu vượt `Max IP changes`, chặn ngay từ HostBill trước khi gọi API
- Sau khi đổi IP thành công, cập nhật cả `details` và account config field `ip`

## Widget client area

### `lcv5_licensedetails`

- Tổng quan license
- Hiện trạng thái, IP, billing cycle, last API message
- Hiện số lần đổi IP đã dùng
- Nút renew ngay

### `lcv5_changeip`

- Form đổi IP riêng cho client area
- Validate IP mới qua method `LicenseChangeIp()`
- Hiện giới hạn số lần đổi IP nếu có

### `lcv5_licensedocs`

- Hiện install/update/remove/verify commands
- Hiện notes theo sản phẩm
- Hiện raw output của `ShowHelp` nếu có

## Admin account tab

Controller `admin/class.licensecheapv5_controller.php` gắn custom template vào trang chi tiết service trong admin:

- Đọc `LicenseDetails()`
- Hiện overview/debug info, commands/notes/help
- Refresh lại dữ liệu/help ngay trong tab admin
- Mở bootbox form để renew, change IP và reset IP counter local

## Cách tuỳ biến

1. Sửa `products.json` để thêm/sửa sản phẩm
2. Override `installer_arg`, `command_alias`, `verify_command`, `extra_commands`
3. Đổi `Input1` nếu cần trỏ qua whitelabel installer URL riêng
4. Đổi `Input2` nếu cần prefix khác `RcLicense`

## Giới hạn hiện tại

- API gốc không cung cấp danh sách license đầy đủ như module DirectAdmin, nên module tập trung vào provisioning và quản lý service đã tạo trong HostBill
- `Reference Key` là mã nội bộ của HostBill, không phải remote license id
- `Terminate()` giữ đúng hành vi gốc của `rclicensing`: gọi action ban và đánh dấu local là `Expired`

## Gợi ý kiểm thử

1. Test connection với token thật
2. Provision một service mới cho mỗi 1 product mẫu
3. Thử renew, suspend, unsuspend
4. Thử change IP trong và vượt giới hạn
5. Kiểm tra widget client area và admin account tab
6. Sửa `products.json` rồi reload module để xác nhận UI cập nhật đúng
