# Troubleshooting (VI)

## 1) Test Connection fail

Module test connection theo thứ tự:

1. Gọi `bundle.php` (structured JSON)
2. Nếu lỗi, fallback gọi `action=ShowHelp` với `serviceid=10`

### Nguyên nhân thường gặp

- Sai token / token rỗng
- Base URL sai (thiếu `/rc/` hoặc sai domain)
- Firewall chặn outbound
- PHP thiếu cURL
- API trả HTML / lỗi upstream

### Gợi ý xử lý

- Kiểm tra HostBill server field `Hostname` và `Username`
- Thử giảm timeout (hiện set 20s) hoặc kiểm tra latency mạng

## 2) Lỗi cURL / timeout

Nếu thấy lỗi kiểu:

- `Unknown cURL error`
- timeout

Hãy kiểm tra:

- DNS resolve
- outbound HTTPS
- giới hạn `open_basedir` / `disable_functions`

## 3) Không hiện docs/commands

Lệnh docs được build từ `products.json`. Kiểm tra:

- Product đã chọn đúng ID
- Product có `command_alias`
- Quyền đọc file `products.json`

## 4) Change IP báo invalid

Module validate IP bằng `FILTER_VALIDATE_IP`.

- IP mới không hợp lệ → `New IP address is invalid`
- IP hiện tại rỗng/không hợp lệ → `Current licensed IP is empty or invalid`

## 5) Change IP bị chặn do giới hạn

Nếu `max_ip_changes > 0` và đã đạt giới hạn, module sẽ chặn trước khi gọi API.

Có thể reset counter từ admin service tab (action reset IP counter).

## 6) Lưu ý debug logs (quan trọng)

Trong `class.api.php`, module ghi debug request/response qua `HBDebug::debug(...)` và payload **có token**.

- Chỉ bật debug khi cần
- Không chia sẻ log ra ngoài nếu chưa ẩn token

> Nếu bạn muốn an toàn hơn ở production, có thể chỉnh code để mask token khi debug (nếu cần mình sẽ hỗ trợ).
