<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatType extends Model
{
    protected $table = 'seat_types';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
