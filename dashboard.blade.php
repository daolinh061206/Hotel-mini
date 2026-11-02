@extends('layouts.panel')
@section('title','Tổng quan quản trị')
@section('panel-content')
<div class="section-head"><div><span class="eyebrow dark">TỔNG QUAN</span><h1>Xin chào, {{ auth()->user()->name }}!</h1><p>Chào mừng đến hệ thống quản lý Lotus Stay.</p></div><span class="status status-confirmed">{{ auth()->user()->role?->name }}</span></div>
<div class="stat-grid"><div class="stat-card"><span>PHÒNG ĐANG HOẠT ĐỘNG</span><strong>{{ $roomCount }}</strong><small>Tổng phòng có thể kinh doanh</small></div><div class="stat-card"><span>ĐƠN CHỜ DUYỆT</span><strong>{{ $pendingCount }}</strong><small>Cần nhân viên xem xét</small></div><div class="stat-card"><span>ĐANG LƯU TRÚ</span><strong>{{ $stayingCount }}</strong><small>Đơn đã check-in</small></div>@if($revenue !== null)<div class="stat-card"><span>DOANH THU ĐÃ THU</span><strong>{{ number_format($revenue,0,',','.') }}đ</strong><small>Các thanh toán success (demo)</small></div>@endif</div>
<div class="panel-block"><div class="block-heading"><h2>Đặt phòng gần đây</h2><a href="{{ route('panel.bookings.index') }}">Xem tất cả →</a></div><div class="table-wrap"><table><thead><tr><th>Mã đơn</th><th>Khách</th><th>Phòng</th><th>Ngày nhận</th><th>Trạng thái</th></tr></thead><tbody>
@foreach($recentBookings as $b)<tr><td><strong>{{ $b->booking_code }}</strong></td><td>{{ $b->user->name }}</td><td>{{ $b->details->pluck('room.room_number')->join(', ') }}</td><td>{{ $b->check_in_date->format('d/m/Y') }}</td><td><span class="status status-{{ config('hotel.booking_status.'.$b->status, $b->status) }}">{{ config('hotel.booking_status.'.$b->status, $b->status) }}</span></td></tr>@endforeach
</tbody></table></div></div>
@endsection
