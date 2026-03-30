# Cài đặt (VI)

Tài liệu này hướng dẫn cài đặt module `licensecheapv5` vào HostBill.

## Yêu cầu

- HostBill (đã cài sẵn)
- PHP có extension: **cURL** và **JSON**
- Server chạy lệnh install/update cần quyền **root** (theo hướng dẫn installer)

## Cấu trúc đặt module

Đặt thư mục module vào HostBill theo đúng chuẩn module Hosting, ví dụ:

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

> Lưu ý: Tên thư mục phải khớp `licensecheapv5` để HostBill load đúng template/controller/widgets.

## Kích hoạt module

1. Vào **Admin → Settings → Apps / Modules** (tuỳ phiên bản HostBill)
2. Tìm module **LicenseCheap v5**
3. Chọn **Install/Activate**

Sau khi upgrade lên các phiên bản mới, HostBill thường sẽ gọi `upgrade()` của module.

## Tạo App/Server

Tạo App/Server cho module **LicenseCheap v5** và cấu hình:

- **Hostname**: API base URL (mặc định `https://api.resellercenter.ir/rc/`)
- **Username**: API token
- **Input1**: installer script URL (mặc định `https://mirror.resellercenter.ir/pre.sh`)
- **Input2**: command prefix (mặc định `RcLicense`)
- **Checkbox**: bật endpoint suspend mở rộng `Ban2/UnBan2`

## Tạo / gán Product

Trong Product cấu hình module:

- **Product**: chọn ID từ `products.json`
- **Licensed IP**: IP cần licensing
- **Max IP changes**: giới hạn đổi IP do HostBill áp (0 = không giới hạn)
- **Reference key prefix**: prefix mã tham chiếu (mặc định `LC-`)
- **Install docs source**: `hybrid` / `local` / `remote`

## Kiểm tra nhanh (smoke test)

- Test Connection ở phần App/Server (token đúng)
- Tạo 1 service test và chạy Provision/Create
- Kiểm tra client-area widgets (nếu widget đã được gán vào product)
- Vào service admin tab để thử Renew / Change IP

## Gỡ cài đặt

- Nếu chỉ muốn gỡ license trên máy khách, dùng lệnh **Remove** hiển thị trong widget/docs.
- Nếu muốn xoá module khỏi HostBill: gỡ theo quy trình module của HostBill (tuỳ phiên bản).
