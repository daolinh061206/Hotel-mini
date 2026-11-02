<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CardPaymentDetail;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Chi su dung trong moi truong dev/test cho do an. KHONG xu ly the that. */
class SimulatedCardPaymentService
{
    /**
     * Moi lan goi tao 1 payment va 1 card_payment_details.
     * $scenario chi la che do DEMO, phai vo hieu hoa tren moi truong production.
     * Khong nhan so the day du, CVV, ma PIN.
     */
    public function pay(
        int $bookingId,
        int $customerUserId,
        string $brand,
        string $last4,
        string $cardholder,
        string $scenario
    ): Payment {
        $brand = strtoupper(trim($brand));
        $scenario = strtolower(trim($scenario));

        if (!app()->environment(['local', 'testing'])) {
            throw ValidationException::withMessages([
                'payment' => 'Thanh toan gia lap chi hoat dong o local/testing.',
            ]);
        }
        if (!in_array($brand, ['VISA', 'MASTERCARD', 'JCB'], true)
            || !preg_match('/^[0-9]{4}$/D', $last4)
            || trim($cardholder) === ''
            || !in_array($scenario, ['approved', 'declined', 'insufficient_funds'], true)) {
            throw ValidationException::withMessages(['card' => 'Thong tin the demo khong hop le.']);
        }

        return DB::transaction(function () use (
            $bookingId, $customerUserId, $brand, $last4, $cardholder, $scenario
        ) {
            $booking = Booking::query()->whereKey($bookingId)->lockForUpdate()->firstOrFail();
            if ((int) $booking->user_id !== $customerUserId) {
                throw ValidationException::withMessages(['booking' => 'Ban khong so huu don dat nay.']);
            }
            if ($booking->status !== 'pending_payment'
                || ($booking->expires_at && $booking->expires_at->lte(now()))
                || (float) $booking->total_amount <= 0) {
                throw ValidationException::withMessages(['booking' => 'Don khong con du dieu kien thanh toan.']);
            }
            if (Payment::query()->where('booking_id', $bookingId)->where('status', 'success')->exists()) {
                throw ValidationException::withMessages(['booking' => 'Don da thanh toan.']);
            }

            $approved = $scenario === 'approved';
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_code' => 'PAY-' . str_replace('-', '', (string) Str::uuid()),
                'provider' => 'MOCK_CARD_GATEWAY',
                'provider_transaction_id' => 'SIM-' . str_replace('-', '', (string) Str::uuid()),
                'method' => 'online',
                'amount' => $booking->total_amount,
                'status' => $approved ? 'success' : 'failed',
                'paid_at' => $approved ? now() : null,
            ]);

            CardPaymentDetail::create([
                'payment_id' => $payment->id,
                'card_brand' => $brand,
                'card_last4' => $last4,
                'cardholder_name' => trim($cardholder),
                'simulation_result' => $scenario,
                'authorization_code' => $approved ? 'AUTH-' . strtoupper(Str::random(10)) : null,
                'processed_at' => now(),
            ]);
            if ($approved) {
                $booking->update(['status' => 'confirmed']);
            }
            return $payment->load('cardPaymentDetail');
        }, 3);
    }
}
