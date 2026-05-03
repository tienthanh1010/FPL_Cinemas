<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatHold extends Model
{
    protected $table = 'seat_holds';

    protected $fillable = [
        'show_id',
        'seat_id',
        'customer_id',
        'hold_token',
        'owner_token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class, 'show_id');
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
