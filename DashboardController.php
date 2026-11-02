<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        return view('panel.dashboard',[
            'roomCount'=>Room::where('is_active',1)->count(),
            'pendingCount'=>Booking::where('status','pending_approval')->where('expires_at','>',now())->count(),
            'stayingCount'=>Booking::where('status','checked_in')->count(),
            'revenue'=>Gate::allows('revenue.view') ? Payment::where('status','success')->sum('amount') : null,
            'recentBookings'=>Booking::with('user','details.room')->latest()->limit(7)->get(),
        ]);
    }
}
