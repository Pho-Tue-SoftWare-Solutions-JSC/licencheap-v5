# Bảo mật (VI)

## Token API

- Token được nhập ở App/Server field **Username**.
- Không commit token vào git / không lưu token trong file.

## Debug logs có thể lộ token

Trong `class.api.php`, module gọi:

- `HBDebug::debug("HB >> LicenseCheapV5", ["url" => ..., "payload" => ...])`

Payload gửi đi có `token`.

Khuyến nghị:

- Chỉ bật debug khi cần điều tra lỗi
- Trước khi share log, phải mask token
- Nếu môi trường production yêu cầu nghiêm ngặt, nên patch module để ẩn token trong debug output

## Quyền truy cập file

- Đảm bảo thư mục `includes/modules/Hosting/licensecheapv5/` không public qua web.
- File `products.json` không chứa secret, nhưng vẫn nên hạn chế truy cập trực tiếp.
