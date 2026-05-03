<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

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

    public function feedback(): HasOne
    {
        return $this->hasOne(CustomerFeedback::class, 'booking_id');
    }
}
