<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    public function index() { return view('panel.types',['types'=>RoomType::withCount('rooms')->orderBy('name')->get()]); }
    public function store(Request $request) {
        $data=$request->validate(['name'=>'required|string|max:100|unique:room_types,name','capacity'=>'required|integer|min:1|max:20','base_price'=>'required|numeric|min:1','description'=>'nullable|string|max:3000']);
        RoomType::create($data);
        return back()->with('success','Đã thêm loại phòng.');
    }
    public function update(Request $request,RoomType $roomType) {
        $data=$request->validate(['name'=>['required','string','max:100',Rule::unique('room_types','name')->ignore($roomType->id)],'capacity'=>'required|integer|min:1|max:20','base_price'=>'required|numeric|min:1','description'=>'nullable|string|max:3000']);
        $roomType->update($data);
        return back()->with('success','Đã cập nhật loại phòng (không đổi giá các đơn đã đặt).');
    }
}
