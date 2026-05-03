<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table = 'genres';

    protected $fillable = [
        'code',
        'name',
    ];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_genres', 'genre_id', 'movie_id');
    }
}
