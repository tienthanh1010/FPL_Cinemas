<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingProduct extends Model
{
    protected $table = 'booking_products';

    protected $fillable = [
        'booking_id', 'product_id', 'qty', 'unit_price_amount', 'discount_amount', 'final_amount',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
