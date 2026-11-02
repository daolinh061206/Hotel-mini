<?php
namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Dữ liệu thử dành cho ứng dụng Laravel mới, KHÔNG chạy trên dữ liệu thật. */
class DemoHotelSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local','testing'])) {
            throw new \RuntimeException('Không seed dữ liệu demo ngoài local/testing.');
        }
        $staff = User::updateOrCreate(['email'=>'staff@hotel-demo.local'], [
            'name'=>'Lễ tân demo','phone'=>'0901000002','role_id'=>Role::where('code','STAFF')->firstOrFail()->id,
            'password'=>'Demo@12345','is_active'=>true,
        ]);
        $client = User::updateOrCreate(['email'=>'client@hotel-demo.local'], [
            'name'=>'Khách hàng demo','phone'=>'0901000003','role_id'=>Role::where('code','CLIENT')->firstOrFail()->id,
            'password'=>'Demo@12345','is_active'=>true,
        ]);
        foreach([
            ['name'=>'Standard','capacity'=>2,'base_price'=>550000,'description'=>'Phòng tiêu chuẩn ấm cúng cho 1–2 khách.'],
            ['name'=>'Deluxe','capacity'=>3,'base_price'=>850000,'description'=>'Không gian rộng rãi, tiện nghi nâng cao.'],
            ['name'=>'Family','capacity'=>4,'base_price'=>1200000,'description'=>'Phòng gia đình rộng rãi cho 4 khách.'],
        ] as $type){ RoomType::updateOrCreate(['name'=>$type['name']],$type); }
        foreach (['101','102','103','104','105','106','201','202','203','204','301','302'] as $number) {
            $type = (int)$number < 200 ? 'Standard' : ((int)$number < 300 ? 'Deluxe' : 'Family');
            Room::updateOrCreate(['room_number'=>$number],[
                'room_type_id'=>RoomType::where('name',$type)->firstOrFail()->id,
                'floor'=>(int)substr($number,0,1), 'is_active'=>true, 'housekeeping_status'=>'clean',
            ]);
        }
        $room=Room::where('room_number','102')->firstOrFail();
        $start=today()->addDays(3);
        $end=today()->addDays(5);
        $booking=Booking::updateOrCreate(['booking_code'=>'BKG-DEMO-PENDING'],[
            'user_id'=>$client->id,'contact_name'=>$client->name,'contact_phone'=>$client->phone,
            'guest_count'=>2,'check_in_date'=>$start,'check_out_date'=>$end,
            'status'=>'pending_approval','total_amount'=>1100000,
            'expires_at'=>now()->addHours(6),
        ]);
        BookingDetail::updateOrCreate(['booking_id'=>$booking->id,'room_id'=>$room->id],[
            'price_per_night'=>550000,'line_total'=>1100000,
        ]);
        $this->command?->info('Demo: staff@hotel-demo.local / client@hotel-demo.local : Demo@12345');
    }
}
