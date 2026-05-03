<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Show extends Model
{
    use HasFactory;

    protected $table = 'shows';

    protected $fillable = [
        'public_id',
        'auditorium_id',
        'movie_version_id',
        'start_time',
        'end_time',
        'on_sale_from',
        'on_sale_until',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'on_sale_from' => 'datetime',
        'on_sale_until' => 'datetime',
    ];

    public function auditorium(): BelongsTo
    {
        return $this->belongsTo(Auditorium::class, 'auditorium_id');
    }

    public function movieVersion(): BelongsTo
    {
        return $this->belongsTo(MovieVersion::class, 'movie_version_id');
    }
}
