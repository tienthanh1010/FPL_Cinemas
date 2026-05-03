<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentRating extends Model
{
    use HasFactory;

    protected $table = 'content_ratings';

    protected $fillable = [
        'code',
        'name',
        'min_age',
        'description',
    ];

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class, 'content_rating_id');
    }
}
