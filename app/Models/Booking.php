<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'public_id',
        'booking_code',
        'show_id',
        'cinema_id',
        'customer_id',
        'sales_channel_id',
        'status',
        'contact_name',
        'contact_phone',
        'contact_email',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'currency',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(BookingTicket::class, 'booking_id');
    }
}
