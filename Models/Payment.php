<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $table = 'payments';
    protected $fillable = ['booking_id','provider','method','status','amount','currency','external_txn_ref','request_payload','response_payload','paid_at'];
    protected $casts = ['request_payload' => 'array', 'response_payload' => 'array', 'paid_at' => 'datetime'];
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class, 'booking_id'); }
    public function refunds(): HasMany { return $this->hasMany(Refund::class, 'payment_id'); }
}
