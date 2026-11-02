<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardPaymentDetail extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'payment_id', 'card_brand', 'card_last4', 'cardholder_name',
        'simulation_result', 'authorization_code', 'processed_at',
    ];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
