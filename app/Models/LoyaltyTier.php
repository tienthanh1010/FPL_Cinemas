<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    protected $table = 'loyalty_tiers';

    protected $fillable = [
        'code',
        'name',
        'min_points',
        'benefits',
    ];

    protected $casts = [
        'benefits' => 'array',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(LoyaltyAccount::class, 'tier_id');
    }
}
