<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
    protected $fillable = ['booking_id','payment_code','provider','provider_transaction_id','method','amount','status','paid_at'];
    protected function casts(): array { return ['amount'=>'decimal:2','paid_at'=>'datetime']; }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function cardPaymentDetail(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(CardPaymentDetail::class); }
}
