<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;

class ProductPricingService
{
    public function currentPrice(Product $product, ?int $cinemaId): ?ProductPrice
    {
        return ProductPrice::query()
            ->where('product_id', $product->id)
            ->where('is_active', 1)
            ->where(function ($query) use ($cinemaId) {
                if ($cinemaId) {
                    $query->where('cinema_id', $cinemaId)->orWhereNull('cinema_id');
                } else {
                    $query->whereNull('cinema_id');
                }
            })
            ->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->orderByRaw('cinema_id is null')
            ->orderByDesc('effective_from')
            ->first();
    }
}
