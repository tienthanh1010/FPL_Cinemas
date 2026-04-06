<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Booking;
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $customers = Customer::query()
<<<<<<< HEAD
            ->with(['user', 'loyaltyAccount.tier'])
            ->withCount('bookings')
            ->addSelect([
                'last_booking_code' => Booking::query()
                    ->select('booking_code')
                    ->whereColumn('customer_id', 'customers.id')
                    ->latest('id')
                    ->limit(1),
            ])
=======
            ->with(['loyaltyAccount'])
            ->withCount('bookings')
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('full_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
<<<<<<< HEAD
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$q}%"))
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                        ->orWhereHas('bookings', fn ($booking) => $booking->where('booking_code', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $report = [
            'customers' => Customer::count(),
            'booking_count' => DB::table('bookings')->count(),
            'refund_count' => Refund::count(),
            'points' => (int) DB::table('loyalty_accounts')->sum('points_balance'),
        ];

        return view('admin.customers.index', compact('customers', 'q', 'report'));
    }

    public function create(): View
    {
        return view('admin.customers.create', ['customer' => new Customer()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Customer::create($this->validateCustomer($request) + ['public_id' => (string) Str::ulid()]);
<<<<<<< HEAD

=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Đã tạo khách hàng.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
<<<<<<< HEAD
            'user',
            'loyaltyAccount.tier',
            'loyaltyAccount.transactions',
            'bookings.show.movieVersion.movie',
            'bookings.show.auditorium.cinema',
            'bookings.tickets.seat',
            'bookings.bookingProducts.product',
            'bookings.discounts.promotion',
            'bookings.discounts.coupon',
        ]);

        $bookingIds = $customer->bookings->pluck('id');

        $payments = Payment::query()
            ->when($bookingIds->isNotEmpty(), fn ($query) => $query->whereIn('booking_id', $bookingIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->with('refunds')
            ->latest('id')
            ->get();

        $refundAmount = (int) $payments->flatMap->refunds->where('status', 'SUCCESS')->sum('amount');
        $paidAmount = (int) $payments->sum('amount');

=======
            'loyaltyAccount',
            'bookings.show.movieVersion.movie',
            'bookings.tickets.seat',
            'bookings.bookingProducts.product',
            'bookings.discounts',
        ]);
        $payments = Payment::query()->whereIn('booking_id', $customer->bookings->pluck('id'))->with('refunds')->latest('id')->get();
        $refundAmount = (int) $payments->flatMap->refunds->sum('amount');
        $paidAmount = (int) $payments->sum('amount');
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        return view('admin.customers.show', compact('customer', 'payments', 'refundAmount', 'paidAmount'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validateCustomer($request, $customer));
<<<<<<< HEAD

=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Đã cập nhật khách hàng.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();
<<<<<<< HEAD

=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        return redirect()->route('admin.customers.index')->with('success', 'Đã xoá khách hàng.');
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('customers', 'phone')->ignore($customer?->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['MALE', 'FEMALE', 'OTHER'])],
            'city' => ['nullable', 'string', 'max:128'],
            'account_status' => ['nullable', Rule::in(['ACTIVE', 'LOCKED', 'INACTIVE'])],
        ]) + [
            'account_status' => $request->input('account_status', 'ACTIVE'),
        ];
    }
}
