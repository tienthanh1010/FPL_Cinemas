<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $balances = InventoryBalance::query()
            ->with(['product.category', 'stockLocation'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        $locations = StockLocation::query()->withCount('balances')->orderBy('name')->get();
        $summary = [
            'total_sku' => Product::query()->count(),
            'locations' => $locations->count(),
            'low_stock' => InventoryBalance::query()->whereColumn('qty_on_hand', '<=', 'reorder_level')->count(),
            'total_qty' => (int) DB::table('inventory_balances')->sum(DB::raw('greatest(qty_on_hand,0)')),
        ];

        return view('admin.inventory.index', compact('balances', 'locations', 'summary'));
    }

    public function adjust(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stock_location_id' => ['required', 'integer', 'exists:stock_locations,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'movement_type' => ['required', 'in:IN,OUT,ADJUST'],
            'qty_delta' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $movementType = $data['movement_type'];
        $qtyDelta = (int) $data['qty_delta'];
        if ($movementType === 'IN' && $qtyDelta < 0) {
            $qtyDelta = abs($qtyDelta);
        }
        if ($movementType === 'OUT' && $qtyDelta > 0) {
            $qtyDelta *= -1;
        }

        DB::transaction(function () use ($data, $movementType, $qtyDelta) {
            $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
                ['stock_location_id' => $data['stock_location_id'], 'product_id' => $data['product_id']],
                ['qty_on_hand' => 0, 'reorder_level' => 5]
            );

            $newQty = $movementType === 'ADJUST' ? $qtyDelta : ($balance->qty_on_hand + $qtyDelta);
            if ($newQty < 0) {
                abort(422, 'Tồn kho không đủ để xuất.');
            }

            $balance->update(['qty_on_hand' => $newQty]);
            StockMovement::create([
                'stock_location_id' => $data['stock_location_id'],
                'product_id' => $data['product_id'],
                'movement_type' => $movementType,
                'qty_delta' => $movementType === 'ADJUST' ? ($newQty - $balance->getOriginal('qty_on_hand')) : $qtyDelta,
                'reference_type' => 'ADJUSTMENT',
                'reference_id' => null,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Đã cập nhật tồn kho.');
    }
}
