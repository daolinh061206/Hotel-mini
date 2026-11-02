@extends('layouts.panel')
@section('title','Quản lý phòng')
@section('panel-content')
<div class="section-head"><div><span class="eyebrow dark">CƠ SỞ LƯU TRÚ</span><h1>Quản lý phòng</h1><p>Danh sách phòng và trạng thái vệ sinh.</p></div><a class="button button-dark button-sm" href="{{ route('panel.rooms.create') }}">+ Thêm phòng</a></div>
<div class="panel-block table-wrap"><table><thead><tr><th>Phòng</th><th>Loại phòng</th><th>Tầng</th><th>Đơn giá</th><th>Hoạt động</th><th>Vệ sinh</th><th></th></tr></thead><tbody>
@foreach($rooms as $room)<tr><td><strong>{{ $room->room_number }}</strong></td><td>{{ $room->roomType->name }}</td><td>{{ $room->floor }}</td><td>{{ number_format($room->roomType->base_price,0,',','.') }}đ</td><td><span class="status status-{{ $room->is_active?'confirmed':'cancelled' }}">{{ $room->is_active?'Hoạt động':'Tạm ngừng' }}</span></td><td>{{ $room->housekeeping_status==='clean'?'Sạch':'Cần dọn' }}</td><td><a href="{{ route('panel.rooms.edit',$room) }}">Chỉnh sửa ↗</a></td></tr>@endforeach
</tbody></table></div><div class="pagination">{{ $rooms->links() }}</div>
@endsection
