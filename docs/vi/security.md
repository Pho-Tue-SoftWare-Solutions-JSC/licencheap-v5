# Bảo mật (VI)

## Mục tiêu

Tài liệu này mô tả các điểm cần lưu ý để vận hành module an toàn, tránh rò rỉ token và giảm rủi ro khi chạy installer.

## Token API

- Token được nhập ở App/Server field **Username**.
- Không commit token vào git / không lưu token trong file.

## Debug logs có thể lộ token

Trong `class.api.php`, module ghi log request/response qua `HBDebug::debug(...)`.

- `HBDebug::debug("HB >> LicenseCheapV5", ["url" => ..., "payload" => ...])`

Payload gửi đi có `token`.

✅ Module đã được cập nhật để **mask token** trong debug log (hiển thị `***`).

Khuyến nghị:

- Chỉ bật debug khi cần điều tra lỗi
- Trước khi share log, phải mask token
- Nếu môi trường production yêu cầu nghiêm ngặt, nên patch module để ẩn token trong debug output

## Quyền truy cập file

- Đảm bảo thư mục `includes/modules/Hosting/licensecheapv5/` không public qua web.
- File `products.json` không chứa secret, nhưng vẫn nên hạn chế truy cập trực tiếp.

## An toàn khi chạy lệnh installer

Các lệnh install/update/remove có thể:

- tải script từ URL (ví dụ `pre.sh`)
- chạy với quyền `root`

Khuyến nghị vận hành:

- Chỉ chạy lệnh từ nguồn tin cậy, kiểm tra domain/URL trước khi chạy.
- Nếu có thể, lưu script về máy rồi review (hoặc checksum) trước khi execute.
- Thực hiện trên môi trường test/staging trước khi áp dụng production.
- Ghi lại thay đổi (change log nội bộ) khi thay đổi `Input1` (installer URL) hoặc `Input2` (prefix).
