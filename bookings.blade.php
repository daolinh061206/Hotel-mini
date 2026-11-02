@extends('layouts.panel')
@section('title','Quản lý đặt phòng')
@section('panel-content')
<div class="section-head"><div><span class="eyebrow dark">VẬN HÀNH</span><h1>Quản lý đơn đặt phòng</h1><p>Duyệt đơn, nhận phòng và trả phòng.</p></div></div>
<div class="panel-block table-wrap"><table><thead><tr><th>Mã đơn / Khách</th><th>Phòng</th><th>Lưu trú</th><th>Thành tiền</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
@forelse($bookings as $b)<tr><td><strong>{{ $b->booking_code }}</strong><br><small>{{ $b->user->name }}</small></td><td>{{ $b->details->pluck('room.room_number')->join(', ') }}</td><td>{{ $b->check_in_date->format('d/m') }} – {{ $b->check_out_date->format('d/m/Y') }}</td><td>{{ number_format($b->total_amount,0,',','.') }}đ</td><td><span class="status status-{{ config('hotel.booking_status.'.$b->status, $b->status) }}">{{ config('hotel.booking_status.'.$b->status, $b->status) }}</span></td>
<td><div class="actions">
@if($b->status==='pending_approval' && $b->expires_at?->isFuture())
<form method="post" action="{{ route('panel.bookings.approve',$b) }}">@csrf<button class="button button-dark button-xs">Duyệt</button></form>
<form method="post" action="{{ route('panel.bookings.reject',$b) }}">@csrf<button class="button button-outline button-xs">Từ chối</button></form>
@endif
@if($b->status==='confirmed')<form method="post" action="{{ route('panel.bookings.checkin',$b) }}">@csrf<button class="button button-outline button-xs">Check-in</button></form>@endif
@if($b->status==='checked_in')<form method="post" action="{{ route('panel.bookings.checkout',$b) }}">@csrf<button class="button button-outline button-xs">Check-out</button></form>@endif
</div></td></tr>@empty<tr><td colspan="6">Chưa có đơn đặt phòng.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $bookings->links() }}</div>
@endsection
