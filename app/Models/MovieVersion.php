<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovieVersion extends Model
{
    use HasFactory;

    protected $table = 'movie_versions';

    protected $fillable = [
        'movie_id',
        'format',
        'audio_language',
        'subtitle_language',
        'notes',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    public function shows(): HasMany
    {
        return $this->hasMany(Show::class, 'movie_version_id');
    }

    public function getLabelAttribute(): string
    {
        $sub = $this->subtitle_language ? " / {$this->subtitle_language}" : '';
        return "{$this->movie?->title} - {$this->format} - {$this->audio_language}{$sub}";
    }
}
