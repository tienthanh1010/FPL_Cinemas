<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\HasOne;
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

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
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function seatType(): BelongsTo
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }
<<<<<<< HEAD

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'booking_ticket_id');
    }
=======
=======
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
}
