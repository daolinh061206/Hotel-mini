<?php
namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class PublicRoomController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'check_in'=>'nullable|required_with:check_out|date|after_or_equal:today',
            'check_out'=>'nullable|required_with:check_in|date|after:check_in',
            'guests'=>'nullable|integer|min:1|max:20',
            'type'=>'nullable|integer|exists:room_types,id',
        ]);
        $rooms = Room::query()->with(['roomType','images'])->where('is_active', true);
        if (!empty($data['type'])) $rooms->where('room_type_id', $data['type']);
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $start=$data['check_in']; $end=$data['check_out'];
            $rooms->whereDoesntHave('bookingDetails', function ($q) use ($start,$end) {
                $q->whereHas('booking', fn($b)=>$b
                    ->where('check_in_date', '<', $end)->where('check_out_date', '>', $start)
                    ->where(fn($s)=>$s->whereIn('status',['confirmed','checked_in'])
                        ->orWhere(fn($pending)=>$pending->whereIn('status',['pending_approval','pending_payment'])
                            ->where('expires_at','>',now()))));
            });
        }
        return view('client.home', [
            'rooms'=>$rooms->orderBy('room_number')->get(),
            'types'=>\App\Models\RoomType::orderBy('base_price')->get(),
            'search'=>$data,
        ]);
    }

    public function show(Room $room)
    {
        abort_unless($room->is_active, 404);
        $room->load(['roomType','images']);
        return view('client.room', compact('room'));
    }
}
