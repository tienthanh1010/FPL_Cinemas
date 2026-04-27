<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'public_id',
        'full_name',
        'dob',
        'country_code',
        'bio',
        'avatar_url',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_people', 'person_id', 'movie_id')
            ->withPivot(['role_type', 'character_name', 'sort_order']);
    }
}
