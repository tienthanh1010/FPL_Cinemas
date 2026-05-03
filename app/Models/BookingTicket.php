<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
