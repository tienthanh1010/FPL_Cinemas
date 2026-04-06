<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $table = 'pricing_rules';

    protected $fillable = [
        'pricing_profile_id',
        'rule_name',
        'rule_type',
        'valid_from',
        'valid_to',
        'day_of_week',
        'start_time',
        'end_time',
        'seat_type_id',
        'ticket_type_id',
        'price_amount',
        'price_mode',
        'adjustment_value',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(PricingProfile::class, 'pricing_profile_id');
    }

    public function seatType(): BelongsTo
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }
}
