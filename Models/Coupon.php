<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'promotion_id', 'code', 'customer_id', 'status', 'issued_at', 'redeemed_at', 'expires_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
