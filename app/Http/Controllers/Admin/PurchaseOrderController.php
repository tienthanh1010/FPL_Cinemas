<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $cinemaId = (int) $request->integer('cinema_id');

        $purchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'cinema'])
            ->withCount('lines')
            ->withSum('lines as qty_ordered_total', 'qty_ordered')
            ->withSum('lines as qty_received_total', 'qty_received')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('po_code', 'like', "%{$q}%")
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($cinemaId > 0, fn ($query) => $query->where('cinema_id', $cinemaId))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $cinemas = Cinema::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.purchase_orders.index', compact('purchaseOrders', 'q', 'status', 'cinemaId', 'cinemas'));
    }

    public function create(): View
    {
        return view('admin.purchase_orders.create', [
            'purchaseOrder' => new PurchaseOrder(['status' => 'DRAFT']),
            'suppliers' => Supplier::query()->where('status', 'ACTIVE')->orderBy('name')->get(),
            'cinemas' => Cinema::query()->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'cinema_id' => ['required', 'integer', 'exists:cinemas,id'],
            'status' => ['required', 'in:DRAFT,ORDERED,CANCELLED'],
            'ordered_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'lines.*.qty_ordered' => ['nullable', 'integer', 'min:1'],
            'lines.*.unit_cost_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $lines = collect($data['lines'])
            ->filter(fn ($line) => !empty($line['product_id']) && !empty($line['qty_ordered']))
            ->map(function ($line) {
                $qtyOrdered = (int) $line['qty_ordered'];
                $unitCost = (int) ($line['unit_cost_amount'] ?? 0);

                return [
                    'product_id' => (int) $line['product_id'],
                    'qty_ordered' => $qtyOrdered,
                    'qty_received' => 0,
                    'unit_cost_amount' => $unitCost,
                    'line_amount' => $qtyOrdered * $unitCost,
                ];
            })
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['lines' => 'Cần ít nhất 1 dòng sản phẩm hợp lệ.']);
        }

        $purchaseOrder = DB::transaction(function () use ($data, $lines) {
            $sequence = str_pad((string) ((int) PurchaseOrder::query()->max('id') + 1), 5, '0', STR_PAD_LEFT);

            $purchaseOrder = PurchaseOrder::create([
                'public_id' => (string) Str::ulid(),
                'supplier_id' => (int) $data['supplier_id'],
                'cinema_id' => (int) $data['cinema_id'],
                'po_code' => 'PO' . now()->format('ymd') . $sequence,
                'status' => $data['status'],
                'ordered_at' => $data['ordered_at'] ?: now(),
                'received_at' => null,
                'total_amount' => (int) $lines->sum('line_amount'),
                'currency' => 'VND',
                'note' => $data['note'] ?? null,
            ]);

            foreach ($lines as $line) {
                $purchaseOrder->lines()->create($line);
            }

            return $purchaseOrder;
        });

        return redirect()->route('admin.purchase_orders.show', $purchaseOrder)->with('success', 'Đã tạo phiếu nhập hàng.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load([
            'supplier',
            'cinema',
            'lines.product.category',
        ]);

        $locations = StockLocation::query()
            ->with('cinema')
            ->where('cinema_id', $purchaseOrder->cinema_id)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $movements = StockMovement::query()
            ->with(['product', 'stockLocation.cinema'])
            ->where('reference_type', 'PURCHASE_ORDER')
            ->where('reference_id', $purchaseOrder->id)
            ->latest('id')
            ->get();

        return view('admin.purchase_orders.show', compact('purchaseOrder', 'locations', 'movements'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['DRAFT', 'ORDERED', 'CANCELLED', 'RECEIVED'])],
            'ordered_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $receivedQty = (int) $purchaseOrder->lines()->sum('qty_received');
        $orderedQty = (int) $purchaseOrder->lines()->sum('qty_ordered');
        if ($data['status'] === 'RECEIVED' && $receivedQty < $orderedQty) {
            throw ValidationException::withMessages(['status' => 'Phiếu chưa nhận đủ hàng nên chưa thể chuyển sang RECEIVED.']);
        }

        $purchaseOrder->update([
            'status' => $data['status'],
            'ordered_at' => $data['ordered_at'] ?: $purchaseOrder->ordered_at,
            'note' => $data['note'] ?? null,
            'received_at' => $data['status'] === 'RECEIVED' ? ($purchaseOrder->received_at ?: now()) : null,
        ]);

        return back()->with('success', 'Đã cập nhật phiếu nhập.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->load('lines.product');

        if ($purchaseOrder->status === 'CANCELLED') {
            throw ValidationException::withMessages(['status' => 'Phiếu đã huỷ, không thể nhập hàng.']);
        }

        $data = $request->validate([
            'stock_location_id' => ['required', 'integer', 'exists:stock_locations,id'],
            'receive_qty' => ['required', 'array'],
            'receive_qty.*' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $location = StockLocation::query()->findOrFail($data['stock_location_id']);
        if ((int) $location->cinema_id !== (int) $purchaseOrder->cinema_id) {
            throw ValidationException::withMessages(['stock_location_id' => 'Vị trí nhập không thuộc cùng rạp với phiếu nhập.']);
        }

        DB::transaction(function () use ($purchaseOrder, $data) {
            $hasReceipt = false;

            foreach ($purchaseOrder->lines as $line) {
                $receiveQty = (int) ($data['receive_qty'][$line->id] ?? 0);
                if ($receiveQty <= 0) {
                    continue;
                }

                $remaining = max(0, (int) $line->qty_ordered - (int) $line->qty_received);
                if ($receiveQty > $remaining) {
                    throw ValidationException::withMessages([
                        "receive_qty.{$line->id}" => "Số lượng nhận vượt quá số lượng còn lại của {$line->product?->name}.",
                    ]);
                }

                $line->increment('qty_received', $receiveQty);

                $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
                    ['stock_location_id' => $data['stock_location_id'], 'product_id' => $line->product_id],
                    ['qty_on_hand' => 0, 'reorder_level' => 5]
                );

                $balance->increment('qty_on_hand', $receiveQty);

                StockMovement::create([
                    'stock_location_id' => $data['stock_location_id'],
                    'product_id' => $line->product_id,
                    'movement_type' => 'IN',
                    'qty_delta' => $receiveQty,
                    'unit_cost_amount' => (int) $line->unit_cost_amount,
                    'reference_type' => 'PURCHASE_ORDER',
                    'reference_id' => $purchaseOrder->id,
                    'note' => $data['note'] ?? ('Nhập hàng từ phiếu ' . $purchaseOrder->po_code),
                    'created_at' => now(),
                ]);

                $hasReceipt = true;
            }

            if (!$hasReceipt) {
                throw ValidationException::withMessages(['receive_qty' => 'Chưa có dòng nào được nhập hàng.']);
            }

            $isFullyReceived = !$purchaseOrder->lines()->whereColumn('qty_received', '<', 'qty_ordered')->exists();
            $purchaseOrder->update([
                'status' => $isFullyReceived ? 'RECEIVED' : 'ORDERED',
                'received_at' => $isFullyReceived ? now() : null,
            ]);
        });

        return back()->with('success', 'Đã nhập kho thành công.');
    }
}
