<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable {
    use Notifiable;
    protected $fillable = ['name', 'email', 'phone', 'password', 'role_id', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean']; }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }
    public function hasPermission(string $code): bool {
        if (!$this->is_active) return false;
        return $this->role()->whereHas('permissions', fn($q) => $q->where('code', $code))->exists();
    }
}
