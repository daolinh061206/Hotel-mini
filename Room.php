<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Room extends Model {
    protected $fillable = ['room_type_id', 'room_number', 'floor', 'is_active', 'housekeeping_status'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function roomType(): BelongsTo { return $this->belongsTo(RoomType::class); }
    public function images(): HasMany { return $this->hasMany(RoomImage::class); }
    public function bookingDetails(): HasMany { return $this->hasMany(BookingDetail::class); }
}
