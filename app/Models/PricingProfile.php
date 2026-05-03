<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingProfile extends Model
{
    protected $table = 'pricing_profiles';

    protected $fillable = [
        'cinema_id',
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(PricingRule::class, 'pricing_profile_id')->orderBy('priority')->orderBy('id');
    }
}
