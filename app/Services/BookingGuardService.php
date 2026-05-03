<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class BookingGuardService
{
    public function assertCanSyncSeats(Request $request, Show $show): void
    {
        $key = $this->syncRateKey($request, $show);
        $maxAttempts = max(10, (int) config('cinema_booking.hold_sync_limit_per_2_minutes', 40));

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            abort(429, 'Bạn thao tác giữ ghế quá nhanh. Vui lòng chờ trong giây lát rồi thử lại.');
        }

        RateLimiter::hit($key, (int) config('cinema_booking.hold_sync_decay_seconds', 120));
    }

    public function assertCanCreateBooking(Request $request, Show $show, array $identity = []): void
    {
        $identityKeys = $this->identityKeysFromRequest($request, $identity);
        $blockUntil = $this->blockedUntilForShow($show->id, $identityKeys);

        if ($blockUntil !== null && now()->lt($blockUntil)) {
            abort(429, 'Tài khoản / thông tin này đang bị tạm chặn đặt vé cho suất chiếu này do giữ ghế nhiều lần nhưng không thanh toán. Vui lòng thử lại sau ' . $blockUntil->format('H:i d/m') . '.');
        }

        $phone = trim((string) ($identity['contact_phone'] ?? ''));
        $email = mb_strtolower(trim((string) ($identity['contact_email'] ?? '')));
        $customerId = isset($identity['customer_id']) ? (int) $identity['customer_id'] : null;
        $maxPending = max(1, (int) config('cinema_booking.max_pending_per_contact_per_show', 2));

        $pendingQuery = Booking::query()
            ->where('show_id', $show->id)
            ->where('status', 'PENDING')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now());

        if ($customerId) {
            $pendingQuery->where('customer_id', $customerId);
        } elseif ($phone !== '' || $email !== '') {
            $pendingQuery->where(function ($query) use ($phone, $email) {
                if ($phone !== '') {
                    $query->orWhere('contact_phone', $phone);
                }

                if ($email !== '') {
                    $query->orWhereRaw('LOWER(contact_email) = ?', [$email]);
                }
            });
        }

        if ($pendingQuery->count() >= $maxPending) {
            abort(429, 'Bạn đang có quá nhiều booking chờ thanh toán cho cùng suất chiếu. Hãy hoàn tất hoặc chờ booking cũ hết hạn rồi thử lại.');
        }
    }

    public function registerExpiredBooking(Booking $booking): void
    {
        $keys = collect([
            $booking->customer_id ? 'customer:' . $booking->customer_id : null,
            $booking->contact_phone ? 'phone:' . preg_replace('/\D+/', '', (string) $booking->contact_phone) : null,
            $booking->contact_email ? 'email:' . mb_strtolower((string) $booking->contact_email) : null,
        ])->filter()->unique()->values();

        if ($keys->isEmpty()) {
            return;
        }

        $maxExpired = max(1, (int) config('cinema_booking.max_expired_per_contact_per_show', 3));
        $blockMinutes = max(5, (int) config('cinema_booking.abuse_block_minutes', 60));

        foreach ($keys as $key) {
            $counterKey = $this->expiredStrikeKey((int) $booking->show_id, $key);
            $count = (int) cache()->increment($counterKey);
            cache()->put($counterKey, $count, now()->addHours(6));

            if ($count >= $maxExpired) {
                cache()->put($this->blockKey((int) $booking->show_id, $key), now()->addMinutes($blockMinutes), now()->addMinutes($blockMinutes));
            }
        }
    }

    public function identityKeysFromRequest(Request $request, array $identity = []): array
    {
        $keys = [];

        $customerId = isset($identity['customer_id']) ? (int) $identity['customer_id'] : null;
        if ($customerId > 0) {
            $keys[] = 'customer:' . $customerId;
        }

        $phone = preg_replace('/\D+/', '', (string) ($identity['contact_phone'] ?? ''));
        if ($phone !== '') {
            $keys[] = 'phone:' . $phone;
        }

        $email = mb_strtolower(trim((string) ($identity['contact_email'] ?? '')));
        if ($email !== '') {
            $keys[] = 'email:' . $email;
        }

        if ($request->ip()) {
            $keys[] = 'ip:' . $request->ip();
        }

        $keys[] = 'session:' . $request->session()->getId();

        return array_values(array_unique(array_filter($keys)));
    }

    private function blockedUntilForShow(int $showId, array $keys): ?\Illuminate\Support\Carbon
    {
        $latest = null;

        foreach ($keys as $key) {
            $blockedUntil = cache()->get($this->blockKey($showId, $key));
            if ($blockedUntil instanceof \Illuminate\Support\Carbon && ($latest === null || $blockedUntil->gt($latest))) {
                $latest = $blockedUntil;
            }
        }

        return $latest;
    }

    private function syncRateKey(Request $request, Show $show): string
    {
        return 'booking:hold-sync:' . $show->id . ':' . sha1($request->ip() . '|' . $request->session()->getId());
    }

    private function expiredStrikeKey(int $showId, string $identityKey): string
    {
        return 'booking:expired-strikes:' . $showId . ':' . sha1($identityKey);
    }

    private function blockKey(int $showId, string $identityKey): string
    {
        return 'booking:block:' . $showId . ':' . sha1($identityKey);
    }
}
