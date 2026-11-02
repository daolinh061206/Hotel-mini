@extends('layouts.panel')
@section('title',$room->exists?'Sửa phòng':'Thêm phòng')
@section('panel-content')
<div class="section-head"><div><span class="eyebrow dark">QUẢN LÝ PHÒNG</span><h1>{{ $room->exists?'Chỉnh sửa phòng':'Thêm phòng mới' }}</h1></div></div>
<div class="surface-card form-narrow"><form method="post" action="{{ $room->exists?route('panel.rooms.update',$room):route('panel.rooms.store') }}">@csrf @if($room->exists)@method('PUT')@endif
<div class="field"><label>Số phòng</label><input name="room_number" required value="{{ old('room_number',$room->room_number) }}"></div>
<div class="field"><label>Loại phòng</label><select name="room_type_id" required>@foreach($types as $type)<option value="{{ $type->id }}" @selected(old('room_type_id',$room->room_type_id)==$type->id)>{{ $type->name }} – {{ number_format($type->base_price,0,',','.') }}đ/đêm</option>@endforeach</select></div>
<div class="field"><label>Tầng</label><input type="number" min="0" max="99" name="floor" value="{{ old('floor',$room->floor) }}"></div>
<div class="field"><label>Trạng thái hoạt động</label><select name="is_active"><option value="1" @selected(old('is_active',$room->exists?$room->is_active:1)==1)>Đang hoạt động</option><option value="0" @selected(old('is_active',$room->is_active)==0)>Ngừng hoạt động</option></select></div>
<div class="field"><label>Vệ sinh phòng</label><select name="housekeeping_status"><option value="clean" @selected(old('housekeeping_status',$room->housekeeping_status)==='clean')>Sạch – sẵn sàng</option><option value="dirty" @selected(old('housekeeping_status',$room->housekeeping_status)==='dirty')>Cần dọn</option></select></div>
<div class="actions"><button class="button button-dark">Lưu phòng</button><a class="button button-outline" href="{{ route('panel.rooms.index') }}">Quay lại</a></div></form></div>
@endsection
