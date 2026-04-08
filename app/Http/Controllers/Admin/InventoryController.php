<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Collection;
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
<<<<<<< HEAD
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $cinemaId = (int) $request->integer('cinema_id');
        $locationId = (int) $request->integer('location_id');
        $productId = (int) $request->integer('product_id');
        $alert = trim((string) $request->get('alert', ''));

        $balances = InventoryBalance::query()
            ->with(['product.category', 'stockLocation.cinema'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('product', function ($productQuery) use ($q) {
                    $productQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->when($cinemaId > 0, fn ($query) => $query->whereHas('stockLocation', fn ($locationQuery) => $locationQuery->where('cinema_id', $cinemaId)))
            ->when($locationId > 0, fn ($query) => $query->where('stock_location_id', $locationId))
            ->when($productId > 0, fn ($query) => $query->where('product_id', $productId))
            ->when($alert === 'low', fn ($query) => $query->whereColumn('qty_on_hand', '<=', 'reorder_level'))
            ->when($alert === 'ok', fn ($query) => $query->whereColumn('qty_on_hand', '>', 'reorder_level'))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $this->hydrateBalanceCostMetrics($balances->getCollection());

        $locations = StockLocation::query()->with(['cinema'])->withCount('balances')->orderBy('name')->get();
        $cinemas = DB::table('cinemas')->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

=======
    public function index(): View
    {
        $balances = InventoryBalance::query()
            ->with(['product.category', 'stockLocation'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        $locations = StockLocation::query()->withCount('balances')->orderBy('name')->get();
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        $summary = [
            'total_sku' => Product::query()->count(),
            'locations' => $locations->count(),
            'low_stock' => InventoryBalance::query()->whereColumn('qty_on_hand', '<=', 'reorder_level')->count(),
            'total_qty' => (int) DB::table('inventory_balances')->sum(DB::raw('greatest(qty_on_hand,0)')),
<<<<<<< HEAD
            'stock_value' => $this->estimatePageStockValue($balances->getCollection()),
        ];

        return view('admin.inventory.index', compact(
            'balances',
            'locations',
            'cinemas',
            'products',
            'summary',
            'q',
            'cinemaId',
            'locationId',
            'productId',
            'alert'
        ));
    }

    public function movements(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $cinemaId = (int) $request->integer('cinema_id');
        $locationId = (int) $request->integer('location_id');
        $productId = (int) $request->integer('product_id');
        $movementType = trim((string) $request->get('movement_type', ''));
        $referenceType = trim((string) $request->get('reference_type', ''));

        $movements = StockMovement::query()
            ->with(['product.category', 'stockLocation.cinema'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('note', 'like', "%{$q}%")
                        ->orWhereHas('product', function ($productQuery) use ($q) {
                            $productQuery->where('name', 'like', "%{$q}%")
                                ->orWhere('sku', 'like', "%{$q}%");
                        });
                });
            })
            ->when($cinemaId > 0, fn ($query) => $query->whereHas('stockLocation', fn ($locationQuery) => $locationQuery->where('cinema_id', $cinemaId)))
            ->when($locationId > 0, fn ($query) => $query->where('stock_location_id', $locationId))
            ->when($productId > 0, fn ($query) => $query->where('product_id', $productId))
            ->when($movementType !== '', fn ($query) => $query->where('movement_type', $movementType))
            ->when($referenceType !== '', fn ($query) => $query->where('reference_type', $referenceType))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $cinemas = DB::table('cinemas')->orderBy('name')->get(['id', 'name']);
        $locations = StockLocation::query()->with('cinema')->orderBy('name')->get();
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);
        $summary = [
            'total_in' => (int) StockMovement::query()->where('qty_delta', '>', 0)->sum('qty_delta'),
            'total_out' => abs((int) StockMovement::query()->where('qty_delta', '<', 0)->sum('qty_delta')),
            'movement_count' => (int) StockMovement::query()->count(),
            'purchase_receipts' => (int) StockMovement::query()->where('reference_type', 'PURCHASE_ORDER')->count(),
        ];

        return view('admin.inventory.movements', compact(
            'movements',
            'cinemas',
            'locations',
            'products',
            'summary',
            'q',
            'cinemaId',
            'locationId',
            'productId',
            'movementType',
            'referenceType'
        ));
=======
        ];

        return view('admin.inventory.index', compact('balances', 'locations', 'summary'));
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    }

    public function adjust(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stock_location_id' => ['required', 'integer', 'exists:stock_locations,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'movement_type' => ['required', 'in:IN,OUT,ADJUST'],
            'qty_delta' => ['required', 'integer', 'not_in:0'],
<<<<<<< HEAD
            'unit_cost_amount' => ['nullable', 'integer', 'min:0'],
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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

<<<<<<< HEAD
            $oldQty = (int) $balance->qty_on_hand;
            $newQty = $movementType === 'ADJUST' ? $qtyDelta : ($oldQty + $qtyDelta);
=======
            $newQty = $movementType === 'ADJUST' ? $qtyDelta : ($balance->qty_on_hand + $qtyDelta);
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            if ($newQty < 0) {
                abort(422, 'Tồn kho không đủ để xuất.');
            }

            $balance->update(['qty_on_hand' => $newQty]);
            StockMovement::create([
                'stock_location_id' => $data['stock_location_id'],
                'product_id' => $data['product_id'],
                'movement_type' => $movementType,
<<<<<<< HEAD
                'qty_delta' => $movementType === 'ADJUST' ? ($newQty - $oldQty) : $qtyDelta,
                'unit_cost_amount' => $data['unit_cost_amount'] ?? null,
=======
                'qty_delta' => $movementType === 'ADJUST' ? ($newQty - $balance->getOriginal('qty_on_hand')) : $qtyDelta,
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                'reference_type' => 'ADJUSTMENT',
                'reference_id' => null,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Đã cập nhật tồn kho.');
    }
<<<<<<< HEAD

    private function hydrateBalanceCostMetrics(Collection $balances): void
    {
        if ($balances->isEmpty()) {
            return;
        }

        $locationIds = $balances->pluck('stock_location_id')->unique()->all();
        $productIds = $balances->pluck('product_id')->unique()->all();

        $movements = StockMovement::query()
            ->whereIn('stock_location_id', $locationIds)
            ->whereIn('product_id', $productIds)
            ->orderByDesc('id')
            ->get(['stock_location_id', 'product_id', 'movement_type', 'qty_delta', 'unit_cost_amount']);

        $grouped = $movements->groupBy(fn (StockMovement $movement) => $movement->stock_location_id . ':' . $movement->product_id);

        foreach ($balances as $balance) {
            $rows = $grouped->get($balance->stock_location_id . ':' . $balance->product_id, collect());
            $latestIn = $rows->first(fn (StockMovement $row) => $row->qty_delta > 0 && $row->unit_cost_amount !== null);
            $inRows = $rows->filter(fn (StockMovement $row) => $row->qty_delta > 0 && $row->unit_cost_amount !== null);
            $weightedQty = max(1, (int) $inRows->sum('qty_delta'));
            $weightedCost = (int) $inRows->sum(fn (StockMovement $row) => (int) $row->qty_delta * (int) $row->unit_cost_amount);
            $avgCost = $inRows->isNotEmpty() ? (int) round($weightedCost / $weightedQty) : null;
            $latestCost = $latestIn ? (int) $latestIn->unit_cost_amount : null;

            $balance->setAttribute('latest_unit_cost_amount', $latestCost);
            $balance->setAttribute('avg_unit_cost_amount', $avgCost);
            $balance->setAttribute('stock_value_amount', $latestCost !== null ? ((int) $balance->qty_on_hand * $latestCost) : null);
        }
    }

    private function estimatePageStockValue(Collection $balances): int
    {
        return (int) $balances->sum(fn ($balance) => (int) ($balance->stock_value_amount ?? 0));
    }
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
}
