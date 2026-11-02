<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\BookingController;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\PublicRoomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ManageBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicRoomController::class, 'index'])->name('home');
Route::get('/rooms/{room}', [PublicRoomController::class, 'show'])->name('rooms.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'can:bookings.create'])->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.mine');
    Route::get('/my-bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/my-bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/my-bookings/{booking}/pay', [PaymentController::class, 'form'])->name('payments.form');
    Route::post('/my-bookings/{booking}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
});

Route::middleware(['auth', 'can:bookings.view_all'])->prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [ManageBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/approve', [ManageBookingController::class, 'approve'])->middleware('can:bookings.review')->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [ManageBookingController::class, 'reject'])->middleware('can:bookings.review')->name('bookings.reject');
    Route::post('/bookings/{booking}/check-in', [ManageBookingController::class, 'checkIn'])->middleware('can:bookings.checkin')->name('bookings.checkin');
    Route::post('/bookings/{booking}/check-out', [ManageBookingController::class, 'checkOut'])->middleware('can:bookings.checkout')->name('bookings.checkout');
    Route::middleware('can:rooms.manage')->group(function () {
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    });
    Route::middleware('can:room_types.manage')->group(function () {
        Route::get('/room-types', [RoomTypeController::class, 'index'])->name('types.index');
        Route::post('/room-types', [RoomTypeController::class, 'store'])->name('types.store');
        Route::put('/room-types/{roomType}', [RoomTypeController::class, 'update'])->name('types.update');
    });
    Route::middleware('can:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
