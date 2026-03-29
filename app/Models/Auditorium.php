<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditorium extends Model
{
    use HasFactory;

    protected $table = 'auditoriums';

    protected $fillable = [
        'public_id',
        'cinema_id',
        'auditorium_code',
        'name',
        'screen_type',
        'seat_map_version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

    public function shows(): HasMany
    {
        return $this->hasMany(Show::class, 'auditorium_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class, 'auditorium_id');
    }
}
