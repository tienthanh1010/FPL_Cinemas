<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'booking_ticket_id',
        'ticket_code',
        'qr_payload',
        'status',
        'issued_at',
        'used_at',
        'printed_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'used_at' => 'datetime',
        'printed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bookingTicket(): BelongsTo
    {
        return $this->belongsTo(BookingTicket::class, 'booking_ticket_id');
    }
}
