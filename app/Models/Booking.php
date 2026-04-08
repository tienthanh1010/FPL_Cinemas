<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class, 'show_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

<<<<<<< HEAD
=======
=======
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    public function bookingProducts(): HasMany
    {
        return $this->hasMany(BookingProduct::class, 'booking_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(BookingDiscount::class, 'booking_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }
}

