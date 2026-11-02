<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class RoomType extends Model {
    protected $fillable = ['name', 'capacity', 'base_price', 'description'];
    protected function casts(): array { return ['capacity' => 'integer', 'base_price' => 'decimal:2']; }
    public function rooms(): HasMany { return $this->hasMany(Room::class); }
}
