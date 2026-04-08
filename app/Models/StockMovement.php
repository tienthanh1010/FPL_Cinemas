<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $table = 'stock_movements';

    protected $fillable = [
        'stock_location_id', 'product_id', 'movement_type', 'qty_delta', 'unit_cost_amount', 'reference_type', 'reference_id', 'note', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }
<<<<<<< HEAD

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'reference_id')
            ->where('reference_type', 'PURCHASE_ORDER');
    }
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
}
