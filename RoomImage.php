<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RoomImage extends Model {
    const UPDATED_AT = null;
    protected $fillable = ['room_id', 'image_path', 'is_cover', 'display_order'];
    protected function casts(): array { return ['is_cover' => 'boolean']; }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
}
