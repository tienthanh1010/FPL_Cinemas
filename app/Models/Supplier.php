<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'public_id', 'name', 'tax_code', 'phone', 'email', 'address_line', 'ward', 'district', 'province', 'status',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
}
