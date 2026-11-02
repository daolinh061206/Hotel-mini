<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BookingDetail extends Model {
    const UPDATED_AT = null;
    protected $fillable = ['booking_id','room_id','price_per_night','line_total'];
    protected function casts(): array { return ['price_per_night'=>'decimal:2','line_total'=>'decimal:2']; }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
}
