<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $table = 'inventory_balances';

    protected $fillable = [
        'stock_location_id', 'product_id', 'qty_on_hand', 'reorder_level',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }
}
