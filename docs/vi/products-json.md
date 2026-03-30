# products.json (VI)

`products.json` định nghĩa danh sách sản phẩm và metadata để module:

- Hiển thị tên sản phẩm
- Gửi đúng `api_type` khi gọi API
- Sinh **install/update/verify** commands
- Bổ sung notes / extra commands
- Bật/tắt Change IP cho từng sản phẩm

## Cấu trúc file

File là một JSON object, key là product id (string hoặc number):

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

## Các field hỗ trợ

- `name` (string): tên gốc / identifier
- `display_name` (string): tên hiển thị trên UI
- `api_type` (string): giá trị gửi sang API (mặc định dùng `name`)
- `installer_arg` (string): tham số cho script `pre.sh`
- `command_alias` (string): hậu tố lệnh update, sẽ ghép với prefix (ví dụ `RcLicenseDA`)

### Commands (override)

Bạn có thể override command template:

- `install_command` (string): template cho lệnh install
- `update_command` (string): template cho lệnh update
- `verify_command` (string): template cho lệnh verify

Token hỗ trợ trong template:

- `{{installer_url}}` → lấy từ App/Server Input1
- `{{installer_arg}}` → từ `installer_arg`
- `{{prefix}}` → từ App/Server Input2
- `{{update_command}}` → lệnh update sau khi replace prefix
- `{{display_name}}` → tên hiển thị

Ví dụ `verify_command` trong `products.json` hiện tại:

```text
touch /etc/.verifylicense ; {{update_command}}
```

### Notes

- `notes` (array<string>): ghi chú hiển thị trong widget/admin.

### Extra commands

- `extra_commands` (array<object>): thêm các command card.

Format mỗi object:

- `title` (string)
- `command` (string, có thể dùng token như trên)

Ví dụ:

```json
{
  "title": "Activate FleetSSL",
  "command": "{{update_command}} -fleetssl"
}
```

### Change IP

- `supports_change_ip` (bool): mặc định `true`. Nếu `false` thì module sẽ ẩn/không cho thao tác Change IP.

## Lưu ý

- Nếu thiếu field, module sẽ tự set default (ví dụ `display_name`, `api_type`, `supports_change_ip`).
- Khi chỉnh `products.json`, chỉ cần reload module/product UI là danh sách sẽ cập nhật.
