<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        return $this->hasMany(MovieVersion::class, 'movie_id')->orderBy('id');
<<<<<<< HEAD
=======
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'movie_genres', 'movie_id', 'genre_id')
            ->orderBy('genres.name');
    }

    public function categories(): BelongsToMany
    {
        return $this->genres();
    }

    public function credits(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'movie_people', 'movie_id', 'person_id')
            ->withPivot(['role_type', 'character_name', 'sort_order'])
            ->orderBy('movie_people.sort_order');
    }

    public function directorCredits(): BelongsToMany
    {
        return $this->credits()->wherePivot('role_type', 'DIRECTOR');
    }

    public function writerCredits(): BelongsToMany
    {
        return $this->credits()->wherePivot('role_type', 'WRITER');
    }

    public function castCredits(): BelongsToMany
    {
        return $this->credits()->wherePivot('role_type', 'CAST');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'movie_id')->latest('id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'movie_genres', 'movie_id', 'genre_id')
            ->orderBy('genres.name');
    }

    public function credits(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'movie_people', 'movie_id', 'person_id')
            ->withPivot(['role_type', 'character_name', 'sort_order'])
            ->orderBy('movie_people.sort_order');
    }

    public function directorCredits(): BelongsToMany
    {
        return $this->credits()->wherePivot('role_type', 'DIRECTOR');
    }

    public function writerCredits(): BelongsToMany
    {
        return $this->credits()->wherePivot('role_type', 'WRITER');
    }

    public function castCredits(): BelongsToMany
    {
        return $this->credits()->wherePivotIn('role_type', ['ACTOR', 'CAST']);
    }


    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

}
