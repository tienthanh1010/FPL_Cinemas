<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLocation extends Model
{
    protected $table = 'stock_locations';

    protected $fillable = [
        'cinema_id', 'code', 'name', 'location_type', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class, 'cinema_id');
    }

    public function balances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class, 'stock_location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'stock_location_id');
    }
}
