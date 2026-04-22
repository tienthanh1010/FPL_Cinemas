<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\StockLocation;
use App\Services\ProductPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductPricingService $pricingService)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $products = Product::query()
            ->with(['category', 'prices' => fn ($query) => $query->orderByDesc('effective_from')])
            ->withSum('bookingProducts as sold_qty', 'qty')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $cinemaId = (int) (Cinema::query()->value('id') ?? 0);
        $report = [
            'product_count' => Product::query()->count(),
            'combo_count' => Product::query()->where('is_combo', 1)->count(),
            'fb_revenue' => (int) DB::table('booking_products')->sum('final_amount'),
            'low_stock' => InventoryBalance::query()->whereColumn('qty_on_hand', '<=', 'reorder_level')->count(),
            'cinema_id' => $cinemaId,
        ];

        return view('admin.products.index', compact('products', 'q', 'report', 'cinemaId'));
    }

    public function create(): View
    {
        return view('admin.products.create', $this->formData(new Product()));
    }

    public function store(Request $request): RedirectResponse
    {
        [$productData, $priceData] = $this->validatedPayload($request);

        $product = DB::transaction(function () use ($productData, $priceData) {
            $product = Product::create($productData + ['public_id' => (string) Str::ulid()]);
            $this->savePrice($product, $priceData);
            $this->ensureInventoryRows($product);

            return $product;
        });

        return redirect()->route('admin.products.show', $product)->with('success', 'Đã tạo sản phẩm F&B.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'prices.cinema', 'bookingProducts.booking', 'inventoryBalances.stockLocation']);
        $currentPrice = $this->pricingService->currentPrice($product, (int) (Cinema::query()->value('id') ?? 0));
        $soldQty = (int) $product->bookingProducts()->sum('qty');
        $revenue = (int) $product->bookingProducts()->sum('final_amount');

        return view('admin.products.show', compact('product', 'currentPrice', 'soldQty', 'revenue'));
    }

    public function edit(Product $product): View
    {
        $product->load(['prices' => fn ($query) => $query->orderByDesc('effective_from')]);

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        [$productData, $priceData] = $this->validatedPayload($request, $product);

        DB::transaction(function () use ($product, $productData, $priceData) {
            $product->update($productData);
            $this->savePrice($product, $priceData);
            $this->ensureInventoryRows($product);
        });

        return redirect()->route('admin.products.show', $product)->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Đã xoá sản phẩm.');
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            'cinemas' => Cinema::query()->orderBy('name')->get(),
            'latestPrice' => $product->exists ? $product->prices()->latest('effective_from')->first() : null,
        ];
    }

    private function validatedPayload(Request $request, ?Product $product = null): array
    {
        $productData = $request->validate([
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:32'],
            'is_combo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'attributes_text' => ['nullable', 'string'],
            'cinema_id' => ['nullable', 'integer', 'exists:cinemas,id'],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
        ]) + [
            'attributes' => $request->filled('attributes_text') ? ['description' => $request->input('attributes_text')] : null,
            'is_combo' => $request->boolean('is_combo'),
            'is_active' => $request->boolean('is_active', true),
        ];

        $priceData = [
            'cinema_id' => $request->input('cinema_id') ?: Cinema::query()->value('id'),
            'price_amount' => (int) $request->input('price_amount', 0),
            'effective_from' => $request->input('effective_from') ?: now(),
            'effective_to' => $request->input('effective_to') ?: null,
        ];

        return [$productData, $priceData];
    }

    private function savePrice(Product $product, array $priceData): void
    {
        if (($priceData['price_amount'] ?? 0) <= 0) {
            return;
        }

        ProductPrice::query()
            ->where('product_id', $product->id)
            ->where('cinema_id', $priceData['cinema_id'])
            ->where('is_active', 1)
            ->update(['is_active' => 0]);

        ProductPrice::create([
            'product_id' => $product->id,
            'cinema_id' => $priceData['cinema_id'],
            'price_amount' => $priceData['price_amount'],
            'currency' => 'VND',
            'effective_from' => $priceData['effective_from'],
            'effective_to' => $priceData['effective_to'],
            'is_active' => 1,
        ]);
    }

    private function ensureInventoryRows(Product $product): void
    {
        foreach (StockLocation::query()->where('is_active', 1)->get() as $location) {
            InventoryBalance::firstOrCreate(
                ['stock_location_id' => $location->id, 'product_id' => $product->id],
                ['qty_on_hand' => 0, 'reorder_level' => 5]
            );
        }
    }
}
