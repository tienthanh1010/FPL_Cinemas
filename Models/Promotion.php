<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'code', 'name', 'description', 'promo_type', 'discount_value', 'max_discount_amount', 'min_order_amount',
        'applies_to', 'is_stackable', 'start_at', 'end_at', 'usage_limit_total', 'usage_limit_per_customer', 'status',
        'day_of_week', 'show_start_from', 'show_start_to', 'customer_scope', 'auto_apply', 'coupon_required',
    ];

    protected $casts = [
        'is_stackable' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'auto_apply' => 'boolean',
        'coupon_required' => 'boolean',
    ];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'promotion_movies', 'promotion_id', 'movie_id');
    }

    public function cinemas(): BelongsToMany
    {
        return $this->belongsToMany(Cinema::class, 'promotion_cinemas', 'promotion_id', 'cinema_id');
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'promotion_id');
    }

    public function bookingDiscounts(): HasMany
    {
        return $this->hasMany(BookingDiscount::class, 'promotion_id');
    }
}
