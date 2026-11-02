# 16 kịch bản kiểm thử thủ công (sau khi chạy Laravel + MySQL)

| ID | Tài khoản | Thao tác | Kết quả kỳ vọng |
|---|---|---|---|
| T01 | Khách chưa đăng nhập | Xem trang chủ, phòng | Được xem |
| T02 | Khách chưa đăng nhập | Gửi POST `/bookings` | Bị yêu cầu đăng nhập |
| T03 | Chưa đăng nhập | Đăng ký hợp lệ | Tạo tài khoản CLIENT, không thể tự gán ADMIN |
| T04 | Client | Mở `/panel` | Bị từ chối 403 |
| T05 | Staff | Mở `/panel/users` | Bị từ chối 403 |
| T06 | Admin | Vào `/panel/users` | Xem/tạo/đổi vai trò |
| T07 | Client | Tìm phòng trống ngày hợp lệ | Chỉ hiện phòng không trùng lịch |
| T08 | Client | Đặt phòng với ngày trả <= ngày nhận | Báo validation |
| T09 | Client | Đặt phòng vượt sức chứa | Không tạo đơn |
| T10 | Client | Đặt lại phòng trùng ngày với đơn chờ hợp lệ | Không tạo đơn |
| T11 | Staff | Duyệt đơn pending_approval | Chuyển pending_payment, thời hạn 30 phút |
| T12 | Client khác | Xem đơn không phải của mình qua URL ID | 403 |
| T13 | Client | Thanh toán mô phỏng `declined` | Tạo payment `failed`, đơn vẫn pending_payment |
| T14 | Client | Thanh toán mô phỏng `approved` | Tạo payment `success`, đơn confirmed |
| T15 | Client | Thanh toán lại đơn confirmed | Từ chối |
| T16 | Staff | Check-in đơn confirmed rồi check-out | Trạng thái completed, phòng cần dọn |

**Lưu ý khi kiểm thử check-in:** Ngày nhận dự kiến phải đến (không phải một ngày trong tương lai). Không thử số thẻ thật, CVV hay PIN.
