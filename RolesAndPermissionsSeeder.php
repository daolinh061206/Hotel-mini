<?php
namespace Database\Seeders;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
class RolesAndPermissionsSeeder extends Seeder {
    public function run(): void {
        foreach (['CLIENT'=>'Khách hàng','STAFF'=>'Nhân viên lễ tân','ADMIN'=>'Quản trị viên'] as $code=>$name) {
            Role::updateOrCreate(['code'=>$code], ['name'=>$name]);
        }
        $permissions = [
            'rooms.view'=>'Xem phòng', 'rooms.manage'=>'Quản lý phòng',
            'room_types.manage'=>'Quản lý loại phòng và giá',
            'bookings.create'=>'Đặt phòng', 'bookings.view_own'=>'Xem đơn của mình',
            'bookings.cancel_own'=>'Hủy đơn chưa trả tiền của mình',
            'payments.create'=>'Thanh toán đơn của mình',
            'bookings.view_all'=>'Xem tất cả đơn', 'bookings.review'=>'Duyệt đơn',
            'bookings.checkin'=>'Nhận phòng', 'bookings.checkout'=>'Trả phòng',
            'users.manage'=>'Quản lý người dùng', 'roles.assign'=>'Gán vai trò',
            'revenue.view'=>'Xem thống kê doanh thu',
        ];
        foreach ($permissions as $code=>$name) Permission::updateOrCreate(['code'=>$code], ['name'=>$name]);
        $all = Permission::pluck('id')->all();
        Role::where('code','ADMIN')->firstOrFail()->permissions()->sync($all);
        $staff = ['rooms.view','bookings.view_all','bookings.review','bookings.checkin','bookings.checkout'];
        $client = ['rooms.view','bookings.create','bookings.view_own','bookings.cancel_own','payments.create'];
        Role::where('code','STAFF')->firstOrFail()->permissions()->sync(Permission::whereIn('code',$staff)->pluck('id'));
        Role::where('code','CLIENT')->firstOrFail()->permissions()->sync(Permission::whereIn('code',$client)->pluck('id'));
    }
}
