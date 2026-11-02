# Phân công code cho 4 thành viên

- **TV1 (Auth và CSDL):** `app/Models/User.php`, `Role.php`, `Permission.php`, `database/migrations/`, `database/seeders/`, `AuthController.php`, `app/Providers/AppServiceProvider.php`. Test tạo tài khoản và kiểm tra 403 của Staff/Client.
- **TV2 (Client và đặt phòng):** `PublicRoomController.php`, `Client/BookingController.php`, `BookingService.php`, views `client/home.blade.php`, `client/room.blade.php`, `client/bookings.blade.php`. Test đặt phòng không trùng lịch.
- **TV3 (Duyệt và thanh toán demo):** `Admin/ManageBookingController.php`, `Client/PaymentController.php`, `SimulatedCardPaymentService.php`, views `client/pay.blade.php`, `panel/bookings.blade.php`. Test thành công, thất bại, hết hạn, không đủ quyền.
- **TV4 (Quản trị và giao diện):** `Admin/DashboardController.php`, `Admin/RoomController.php`, `Admin/RoomTypeController.php`, `Admin/UserController.php`, views `panel/*`, stylesheet `public/css/hotel.css`. Test CRUD phòng và phân quyền Admin.

Nhánh Git đề xuất: `feature/auth-db`, `feature/client-booking`, `feature/payment-workflow`, `feature/admin-ui`. Mọi thay đổi bảng chung phải được review trong Pull Request.
