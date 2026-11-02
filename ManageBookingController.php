<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageBookingController extends Controller
{
    public function index()
    {
        return view('panel.bookings',[ 'bookings'=>Booking::with('user','details.room')->latest()->paginate(15) ]);
    }
    public function approve(Request $request,Booking $booking)
    {
        DB::transaction(function() use ($booking,$request) {
            $b=Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($b->status!=='pending_approval' || !$b->expires_at?->isFuture())
                throw ValidationException::withMessages(['booking'=>'Đơn không còn chờ duyệt hợp lệ.']);
            if ($b->check_in_date->isBefore(today()))
                throw ValidationException::withMessages(['booking'=>'Ngày nhận phòng đã qua.']);
            $b->update(['status'=>'pending_payment','reviewed_by_user_id'=>$request->user()->id,'reviewed_at'=>now(), 'expires_at'=>now()->addMinutes(30)]);
        });
        return back()->with('success','Đã duyệt đơn. Khách có 30 phút để thanh toán thử nghiệm.');
    }
    public function reject(Request $request,Booking $booking)
    {
        $updated=Booking::whereKey($booking->id)->where('status','pending_approval')->update([
            'status'=>'rejected','reviewed_by_user_id'=>$request->user()->id,'reviewed_at'=>now()
        ]);
        if (!$updated) return back()->with('error','Đơn đã được xử lý trước đó.');
        return back()->with('success','Đã từ chối đơn.');
    }
    public function checkIn(Booking $booking)
    {
        DB::transaction(function() use ($booking) {
            $b=Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($b->status!=='confirmed' || $b->check_in_date->isAfter(today()))
                throw ValidationException::withMessages(['booking'=>'Đơn chưa được xác nhận hoặc chưa đến ngày nhận phòng.']);
            $rooms=$b->details()->with('room')->get()->pluck('room');
            foreach ($rooms as $room) {
                if (!$room->is_active || $room->housekeeping_status!=='clean')
                    throw ValidationException::withMessages(['booking'=>'Phòng '.$room->room_number.' chưa sẵn sàng.']);
            }
            $b->update(['status'=>'checked_in','actual_check_in_at'=>now()]);
        });
        return back()->with('success','Đã nhận phòng.');
    }
    public function checkOut(Booking $booking)
    {
        DB::transaction(function() use ($booking) {
            $b=Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            if ($b->status!=='checked_in') throw ValidationException::withMessages(['booking'=>'Đơn chưa ở trạng thái đang lưu trú.']);
            $ids=$b->details()->pluck('room_id');
            Room::whereIn('id',$ids)->update(['housekeeping_status'=>'dirty']);
            $b->update(['status'=>'completed','actual_check_out_at'=>now()]);
        });
        return back()->with('success','Đã trả phòng; các phòng được đánh dấu cần dọn.');
    }
}
