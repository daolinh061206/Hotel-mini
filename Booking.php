<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Booking extends Model {
    protected $fillable = ['booking_code','user_id','contact_name','contact_phone','guest_count','check_in_date','check_out_date','status','total_amount','reviewed_by_user_id','reviewed_at','expires_at','cancelled_at','actual_check_in_at','actual_check_out_at'];
    protected function casts(): array { return ['check_in_date'=>'date','check_out_date'=>'date','reviewed_at'=>'datetime','expires_at'=>'datetime','cancelled_at'=>'datetime','actual_check_in_at'=>'datetime','actual_check_out_at'=>'datetime','total_amount'=>'decimal:2']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
    public function details(): HasMany { return $this->hasMany(BookingDetail::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
