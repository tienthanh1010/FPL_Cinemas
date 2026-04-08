<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cinema extends Model
{
    use HasFactory;

    protected $table = 'cinemas';

    protected $fillable = [
        'public_id',
        'chain_id',
        'cinema_code',
        'name',
        'phone',
        'email',
        'timezone',
        'address_line',
        'ward',
        'district',
        'province',
        'country_code',
        'latitude',
        'longitude',
        'opening_hours',
        'status',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function chain(): BelongsTo
    {
        return $this->belongsTo(CinemaChain::class, 'chain_id');
    }

    public function auditoriums(): HasMany
    {
        return $this->hasMany(Auditorium::class, 'cinema_id');
    }
}
