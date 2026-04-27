<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Show;
use App\Services\BookingGuardService;
use App\Services\SeatHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatHoldController extends Controller
{
    public function __construct(
        private readonly SeatHoldService $seatHoldService,
        private readonly BookingGuardService $bookingGuardService,
    ) {
    }

    public function status(Request $request, Show $show): JsonResponse
    {
        $this->authorizeShow($show);
        $ownerToken = $this->seatHoldService->ownerToken($request->session()->get('seat_hold_owner_token'));

        return response()->json([
            'show_id' => (int) $show->id,
            'owner_token' => $ownerToken,
            'hold_minutes' => booking_hold_minutes(),
            'seats' => $this->seatHoldService->seatPayload($show, $ownerToken),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function sync(Request $request, Show $show): JsonResponse
    {
        $this->authorizeShow($show);
        $this->bookingGuardService->assertCanSyncSeats($request, $show);

        $data = $request->validate([
            'seat_ids' => ['nullable', 'array'],
            'seat_ids.*' => ['integer'],
        ]);

        $ownerToken = $this->seatHoldService->ownerToken($request->session()->get('seat_hold_owner_token'));
        $customer = member_customer();
        $seatPayload = $this->seatHoldService->syncSelectedSeats($show, $data['seat_ids'] ?? [], $customer, $ownerToken);

        return response()->json([
            'ok' => true,
            'show_id' => (int) $show->id,
            'owner_token' => $ownerToken,
            'hold_minutes' => booking_hold_minutes(),
            'selected_seat_ids' => collect($seatPayload)->where('selected_by_self', true)->pluck('id')->values()->all(),
            'seats' => $seatPayload,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function authorizeShow(Show $show): void
    {
        $show->loadMissing(['auditorium.cinema', 'movieVersion.movie']);

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $show->auditorium?->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        if (! $show->movieVersion?->movie || $show->movieVersion->movie->status !== 'ACTIVE') {
            abort(404);
        }
    }
}
