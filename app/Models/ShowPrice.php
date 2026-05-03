<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowPrice extends Model
{
    protected $table = 'show_prices';

    protected $fillable = [
        'show_id',
        'seat_type_id',
        'ticket_type_id',
        'price_amount',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class, 'show_id');
    }
}
