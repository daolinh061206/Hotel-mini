<?php
namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void {
        $codes = [
            'rooms.view','rooms.manage','room_types.manage',
            'bookings.create','bookings.view_own','bookings.cancel_own',
            'payments.create','bookings.view_all','bookings.review',
            'bookings.checkin','bookings.checkout','users.manage',
            'roles.assign','revenue.view',
        ];
        foreach ($codes as $code) {
            Gate::define($code, fn (User $user) => $user->hasPermission($code));
        }
    }
}
