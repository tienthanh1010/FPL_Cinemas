<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movies';

    protected $fillable = [
        'public_id',
        'content_rating_id',
        'title',
        'original_title',
        'duration_minutes',
        'release_date',
        'language_original',
        'synopsis',
        'poster_url',
        'trailer_url',
        'censorship_license_no',
        'status',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function contentRating(): BelongsTo
    {
        return $this->belongsTo(ContentRating::class, 'content_rating_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MovieVersion::class, 'movie_id');
    }
}
