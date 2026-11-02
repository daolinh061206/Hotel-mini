<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(){ return view('panel.rooms',['rooms'=>Room::with('roomType')->orderBy('room_number')->paginate(20)]); }
    public function create(){ return view('panel.room_form',['room'=>new Room(),'types'=>RoomType::orderBy('name')->get()]); }
    public function edit(Room $room){ return view('panel.room_form',['room'=>$room,'types'=>RoomType::orderBy('name')->get()]); }
    private function validated(Request $request, ?Room $room=null): array
    {
        return $request->validate([
            'room_number'=>['required','string','max:20',Rule::unique('rooms','room_number')->ignore($room?->id)],
            'room_type_id'=>'required|integer|exists:room_types,id', 'floor'=>'nullable|integer|min:0|max:99',
            'is_active'=>'required|boolean', 'housekeeping_status'=>'required|in:clean,dirty'
        ]);
    }
    public function store(Request $request)
    {
        Room::create($this->validated($request));
        return redirect()->route('panel.rooms.index')->with('success','Đã tạo phòng mới.');
    }
    public function update(Request $request, Room $room)
    {
        $data=$this->validated($request,$room);
        if (!$data['is_active'] && $room->bookingDetails()->whereHas('booking', fn($q)=>$q
                ->whereIn('status',['confirmed','checked_in','pending_approval','pending_payment'])
                ->where('check_out_date','>',today()->toDateString()))->exists()) {
            return back()->withErrors(['is_active'=>'Không thể ngừng hoạt động phòng đang có đặt phòng hiệu lực.']);
        }
        $room->update($data);
        return redirect()->route('panel.rooms.index')->with('success','Đã cập nhật phòng.');
    }
}
