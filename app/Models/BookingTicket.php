<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingTicket extends Model
{
    protected $table = 'booking_tickets';

    protected $fillable = [
        'booking_id',
        'show_id',
        'seat_id',
        'ticket_type_id',
        'seat_type_id',
        'unit_price_amount',
        'discount_amount',
        'final_price_amount',
        'status',
    ];

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function seatType(): BelongsTo
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'booking_ticket_id');
    }
}
