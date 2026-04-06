<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $table = 'seats';

    protected $fillable = [
        'auditorium_id',
        'seat_type_id',
        'seat_code',
        'row_label',
        'col_number',
        'x',
        'y',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function auditorium(): BelongsTo
    {
        return $this->belongsTo(Auditorium::class, 'auditorium_id');
    }

    public function seatType(): BelongsTo
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }
}
