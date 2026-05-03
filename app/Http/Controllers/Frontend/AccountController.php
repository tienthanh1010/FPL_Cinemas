<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CustomerAccountService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private readonly CustomerAccountService $customerAccountService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $customer = $this->customerAccountService->customerForUser($user)
            ?? $this->customerAccountService->syncCustomerForUser($user);

        $customer->load('loyaltyAccount.tier');

        /** @var LengthAwarePaginator $bookings */
        $bookings = Booking::query()
            ->where('customer_id', $customer->id)
            ->with([
                'show.movieVersion.movie',
                'show.auditorium.cinema',
                'tickets.seat',
                'payments.refunds',
            ])
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        $summary = [
            'total_bookings' => Booking::query()->where('customer_id', $customer->id)->count(),
            'paid_bookings' => Booking::query()->where('customer_id', $customer->id)->whereIn('status', ['PAID', 'CONFIRMED', 'COMPLETED'])->count(),
            'total_spent' => (int) Booking::query()->where('customer_id', $customer->id)->whereIn('status', ['PAID', 'CONFIRMED', 'COMPLETED'])->sum('paid_amount'),
        ];

        return view('frontend.account.index', [
            'customer' => $customer,
            'bookings' => $bookings,
            'summary' => $summary,
        ]);
    }
}
