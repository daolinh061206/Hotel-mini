<?php
namespace App\Services;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Core booking logic only: controller validation, payment gateway and approval are separate. */
class BookingService {
    public function createPending(int $userId, array $roomIds, string $start, string $end, int $guestCount, string $name, string $phone): Booking {
        $startDate = CarbonImmutable::parse($start)->startOfDay();
        $endDate = CarbonImmutable::parse($end)->startOfDay();
        $nights = $startDate->diffInDays($endDate, false);
        $roomIds = collect($roomIds)->map(fn($id) => (int)$id)->unique()->sort()->values()->all();
        if ($nights <= 0 || !$roomIds || $guestCount < 1) {
            throw ValidationException::withMessages(['rooms' => 'Ngày hoặc số phòng/khách không hợp lệ.']);
        }
        return DB::transaction(function () use ($userId,$roomIds,$startDate,$endDate,$guestCount,$name,$phone,$nights) {
            // Lock room rows IN ORDER; all booking paths must follow this pattern.
            $rooms = Room::query()->whereIn('id',$roomIds)->orderBy('id')->lockForUpdate()->with('roomType')->get();
            if ($rooms->count() !== count($roomIds) || $rooms->contains(fn($r)=>!$r->is_active)) {
                throw ValidationException::withMessages(['rooms'=>'Có phòng không tồn tại hoặc ngừng hoạt động.']);
            }
            if ($rooms->sum(fn($r)=>$r->roomType->capacity) < $guestCount) {
                throw ValidationException::withMessages(['guest_count'=>'Số người vượt sức chứa phòng.']);
            }
            $overlap = BookingDetail::query()->whereIn('room_id',$roomIds)->whereHas('booking', function ($q) use ($startDate,$endDate) {
                $q->whereDate('check_in_date','<',$endDate->toDateString())
                  ->whereDate('check_out_date','>',$startDate->toDateString())
                  ->where(function ($q) {
                      $q->whereIn('status',['confirmed','checked_in'])
                        ->orWhere(function ($q) {
                            $q->whereIn('status',['pending_approval','pending_payment'])
                              ->where('expires_at','>',now());
                        });
                  });
            })->exists();
            if ($overlap) throw ValidationException::withMessages(['rooms'=>'Phòng đã được giữ trong khoảng thời gian này.']);
            $amount = $rooms->sum(fn($r)=>(float)$r->roomType->base_price*$nights);
            $booking = Booking::create([
                'booking_code'=>'BK'.strtoupper(Str::random(16)),
                'user_id'=>$userId, 'contact_name'=>$name, 'contact_phone'=>$phone,
                'guest_count'=>$guestCount, 'check_in_date'=>$startDate->toDateString(),
                'check_out_date'=>$endDate->toDateString(), 'status'=>'pending_approval',
                'total_amount'=>$amount, 'expires_at'=>now()->addDay(),
            ]);
            foreach ($rooms as $room) {
                $price=(float)$room->roomType->base_price;
                BookingDetail::create([
                    'booking_id'=>$booking->id,'room_id'=>$room->id,
                    'price_per_night'=>$price,'line_total'=>$price*$nights,
                ]);
            }
            return $booking->load('details');
        }, 3);
    }
}
