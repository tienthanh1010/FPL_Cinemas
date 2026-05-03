<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedback extends Model
{
    protected $table = 'customer_feedback';

    protected $fillable = [
        'booking_id',
        'customer_id',
        'movie_id',
        'show_id',
        'reviewer_name',
        'reviewer_email',
        'movie_rating',
        'movie_comment',
        'food_rating',
        'food_comment',
        'facility_rating',
        'facility_comment',
        'staff_rating',
        'staff_comment',
        'overall_comment',
        'status',
    ];

    protected $casts = [
        'movie_rating' => 'integer',
        'food_rating' => 'integer',
        'facility_rating' => 'integer',
        'staff_rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class, 'show_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED');
    }
}
