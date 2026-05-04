<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\MovieVersion;
use App\Models\PricingProfile;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\TicketType;
use App\Services\ShowPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShowController extends Controller
{
    private const STATUSES = [
        'SCHEDULED' => 'Sắp mở bán',
        'ON_SALE' => 'Đang mở bán',
        'SOLD_OUT' => 'Hết vé',
        'ENDED' => 'Đã chiếu',
        'CANCELLED' => 'Huỷ',
    ];

    private const MAX_END_CLOCK = '23:00';

    public function __construct(private readonly ShowPricingService $pricingService)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $shows = Show::query()
            ->with(['movieVersion.movie', 'auditorium.cinema', 'pricingProfile'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->whereHas('movieVersion.movie', function ($movieQuery) use ($q) {
                        $movieQuery->where('title', 'like', "%{$q}%")
                            ->orWhere('original_title', 'like', "%{$q}%");
                    })->orWhereHas('auditorium', function ($auditoriumQuery) use ($q) {
                        $auditoriumQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('auditorium_code', 'like', "%{$q}%");
                    });
                });
            })
            ->orderByDesc('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shows.index', [
            'shows' => $shows,
            'q' => $q,
            'statusOptions' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.shows.create', $this->formData(new Show()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $schedulePlan = $this->buildSchedulePlan($validated);

        $createdShows = DB::transaction(function () use ($validated, $schedulePlan, $request) {
            $shows = collect();

            foreach ($schedulePlan as $slot) {
                $show = Show::create([
                    'public_id' => (string) Str::ulid(),
                    'auditorium_id' => $validated['auditorium_id'],
                    'movie_version_id' => $validated['movie_version_id'],
                    'pricing_profile_id' => $validated['pricing_profile_id'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'on_sale_from' => $slot['on_sale_from'],
                    'on_sale_until' => $slot['on_sale_until'],
                    'status' => $validated['status'],
                    'created_by' => (int) $request->session()->get('admin_user_id'),
                ]);

                $this->pricingService->syncShowPrices($show->load('pricingProfile'));
                $shows->push($show);
            }

            return $shows;
        });

        $count = $createdShows->count();
        $message = $count > 1
            ? "Đã tạo {$count} suất chiếu liên tiếp."
            : 'Đã tạo suất chiếu.';

        return redirect()->route('admin.shows.index')->with('success', $message);
    }

    public function show(Show $show): View
    {
        $show->load(['movieVersion.movie', 'auditorium.cinema', 'prices', 'pricingProfile']);

        $seatRows = Seat::query()
            ->where('auditorium_id', $show->auditorium_id)
            ->orderBy('row_label')
            ->orderBy('col_number')
            ->get();

        $seatTypeNames = SeatType::query()->pluck('name', 'id');
        $ticketTypeNames = TicketType::query()->orderBy('id')->limit(1)->pluck('name', 'id');

        $heldSeatIds = DB::table('seat_holds')
            ->where('show_id', $show->id)
            ->where('status', 'HELD')
            ->where('expires_at', '>', now())
            ->pluck('seat_id')
            ->flip();

        $bookedSeatIds = DB::table('booking_tickets')
            ->where('show_id', $show->id)
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->pluck('seat_id')
            ->flip();

        $blockedSeatIds = SeatBlock::query()
            ->where('auditorium_id', $show->auditorium_id)
            ->where('start_at', '<', $show->end_time)
            ->where('end_at', '>', $show->start_time)
            ->pluck('id', 'seat_id');

        $prices = $show->prices->groupBy('seat_type_id');
        $seats = $seatRows->map(function ($seat) use ($heldSeatIds, $bookedSeatIds, $blockedSeatIds, $prices, $seatTypeNames) {
            $status = ! $seat->is_active
                ? 'maintenance'
                : ($blockedSeatIds->has($seat->id)
                    ? 'blocked'
                    : ($bookedSeatIds->has($seat->id)
                        ? 'booked'
                        : ($heldSeatIds->has($seat->id) ? 'hold' : 'empty')));

            return [
                'id' => $seat->id,
                'seat_code' => $seat->seat_code,
                'row_label' => $seat->row_label,
                'col_number' => $seat->col_number,
                'seat_type_id' => $seat->seat_type_id,
                'seat_type_name' => $seatTypeNames[$seat->seat_type_id] ?? 'Ghế',
                'status' => $status,
                'block_id' => $blockedSeatIds[$seat->id] ?? null,
                'prices' => optional($prices->get($seat->seat_type_id))->pluck('price_amount')->all() ?? [],
            ];
        })->groupBy('row_label');

        $tickets = DB::table('booking_tickets')->where('show_id', $show->id)->whereIn('status', ['RESERVED', 'ISSUED']);
        $soldTickets = (clone $tickets)->count();
        $revenue = (int) (clone $tickets)->sum('final_price_amount');
        $totalSeats = $seatRows->where('is_active', 1)->count();
        $fillRate = $totalSeats > 0 ? round(($soldTickets / $totalSeats) * 100, 1) : 0;

        $bookings = Booking::query()
            ->where('show_id', $show->id)
            ->with(['tickets.seat'])
            ->orderByDesc('id')
            ->get();

        $priceMatrix = [];
        foreach ($show->prices as $price) {
            $priceMatrix[$price->seat_type_id][$price->ticket_type_id] = $price->price_amount;
        }

        return view('admin.shows.show', [
            'show' => $show,
            'seats' => $seats,
            'soldTickets' => $soldTickets,
            'revenue' => $revenue,
            'fillRate' => $fillRate,
            'totalSeats' => $totalSeats,
            'bookings' => $bookings,
            'statusOptions' => self::STATUSES,
            'ticketTypeNames' => $ticketTypeNames,
            'seatTypeNames' => $seatTypeNames,
            'priceMatrix' => $priceMatrix,
        ]);
    }

    public function edit(Show $show): View
    {
        return view('admin.shows.edit', $this->formData($show));
    }

    public function update(Request $request, Show $show): RedirectResponse
    {
        $validated = $this->validateData($request, $show);
        $priceRelatedChanges = $this->hasPriceRelevantChanges($show, $validated);
        $hasLockedTickets = $this->showHasLockedTickets($show);

        if ($priceRelatedChanges && $hasLockedTickets) {
            throw ValidationException::withMessages([
                'show_date' => 'Suất chiếu này đã có vé giữ chỗ/đã bán. Bạn không thể đổi phim, giá vé, phòng hoặc giờ chiếu vì sẽ làm sai snapshot giá của khách đã mua.',
            ]);
        }

        DB::transaction(function () use ($validated, $show, $priceRelatedChanges) {
            $show->update([
                'auditorium_id' => $validated['auditorium_id'],
                'movie_version_id' => $validated['movie_version_id'],
                'pricing_profile_id' => $validated['pricing_profile_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'on_sale_from' => $validated['on_sale_from'],
                'on_sale_until' => $validated['on_sale_until'],
                'status' => $validated['status'],
            ]);

            if ($priceRelatedChanges) {
                $this->pricingService->syncShowPrices($show->load('pricingProfile'));
            }
        });

        $message = $priceRelatedChanges
            ? 'Đã cập nhật suất chiếu và chụp lại bảng giá cho suất này.'
            : 'Đã cập nhật suất chiếu.';

        return redirect()->route('admin.shows.show', $show)->with('success', $message);
    }

    public function blockSeat(Request $request, Show $show): RedirectResponse
    {
        $data = $request->validate([
            'seat_id' => ['required', 'integer', 'exists:seats,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $seat = Seat::query()->where('auditorium_id', $show->auditorium_id)->findOrFail($data['seat_id']);

        $existingBlock = SeatBlock::query()
            ->where('auditorium_id', $show->auditorium_id)
            ->where('seat_id', $seat->id)
            ->where('start_at', '<', $show->end_time)
            ->where('end_at', '>', $show->start_time)
            ->exists();

        if ($existingBlock) {
            return back()->with('error', 'Ghế ' . $seat->seat_code . ' đã bị khoá trong suất này.');
        }

        $busySeat = DB::table('booking_tickets')
            ->where('show_id', $show->id)
            ->where('seat_id', $seat->id)
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->exists();

        $heldSeat = DB::table('seat_holds')
            ->where('show_id', $show->id)
            ->where('seat_id', $seat->id)
            ->whereIn('status', ['HELD', 'CONFIRMED'])
            ->where('expires_at', '>', now())
            ->exists();

        if ($busySeat || $heldSeat) {
            return back()->with('error', 'Ghế ' . $seat->seat_code . ' đã được giữ hoặc đặt, không thể khoá thêm.');
        }

        SeatBlock::create([
            'auditorium_id' => $show->auditorium_id,
            'seat_id' => $seat->id,
            'reason' => $data['reason'] ?: 'Khoá thủ công theo suất',
            'start_at' => $show->start_time,
            'end_at' => $show->end_time,
        ]);

        return back()->with('success', 'Đã khoá ghế ' . $seat->seat_code . ' cho suất này.');
    }

    public function unblockSeat(Show $show, SeatBlock $seatBlock): RedirectResponse
    {
        abort_if((int) $seatBlock->auditorium_id !== (int) $show->auditorium_id, 404);
        $seatBlock->delete();

        return back()->with('success', 'Đã mở khoá ghế.');
    }

    public function destroy(Show $show): RedirectResponse
    {
        try {
            DB::transaction(function () use ($show) {
                $show->prices()->delete();
                $show->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá suất chiếu (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá suất chiếu.');
    }

    private function formData(Show $show): array
    {
        $show->loadMissing(['movieVersion.movie', 'pricingProfile']);

        $existingShows = Show::query()
            ->with(['movieVersion.movie', 'auditorium'])
            ->where('status', '!=', 'CANCELLED')
            ->when($show->exists, fn ($query) => $query->where('id', '!=', $show->id))
            ->orderBy('start_time')
            ->get()
            ->map(fn (Show $item) => [
                'id' => (int) $item->id,
                'auditorium_id' => (int) $item->auditorium_id,
                'movie_title' => $item->movieVersion?->movie?->title ?: 'Phim chưa rõ',
                'format' => $item->movieVersion?->format ?: '2D',
                'start_time' => optional($item->start_time)->format('Y-m-d H:i'),
                'end_time' => optional($item->end_time)->format('Y-m-d H:i'),
                'date' => optional($item->start_time)->format('Y-m-d'),
                'start_clock' => optional($item->start_time)->format('H:i'),
                'end_clock' => optional($item->end_time)->format('H:i'),
                'status' => $item->status,
            ])
            ->values();

        return [
            'show' => $show,
            'auditoriums' => Auditorium::with('cinema')->orderBy('name')->get(),
            'movies' => Movie::query()->where('status', 'ACTIVE')->with('versions')->orderBy('title')->get(),
            'profiles' => PricingProfile::query()->where('is_active', 1)->orderBy('name')->get(),
            'statusOptions' => self::STATUSES,
            'selectedMovieId' => old('movie_id', $show->movieVersion?->movie_id),
            'selectedMovieVersionId' => old('movie_version_id', $show->movie_version_id),
            'existingShows' => $existingShows,
        ];
    }

    private function validateData(Request $request, ?Show $show = null): array
    {
        $data = $request->validate([
            'auditorium_id' => ['required', 'integer', 'exists:auditoriums,id'],
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'movie_version_id' => ['required', 'integer', 'exists:movie_versions,id'],
            'pricing_profile_id' => ['required', 'integer', 'exists:pricing_profiles,id'],
            'show_date' => ['required', 'date'],
            'start_clock' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'show_count' => [$show ? 'nullable' : 'required', 'integer', 'min:1', 'max:12'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:180'],
        ]);

        $auditorium = Auditorium::query()->with('cinema')->findOrFail($data['auditorium_id']);
        $movieVersion = MovieVersion::query()
            ->with('movie')
            ->where('movie_id', $data['movie_id'])
            ->find($data['movie_version_id']);

        if (! $movieVersion) {
            throw ValidationException::withMessages([
                'movie_version_id' => 'Phiên bản phim không thuộc phim đã chọn. Hãy chọn lại đúng phiên bản 2D/3D của phim này.',
            ]);
        }

        $pricingProfile = PricingProfile::query()->findOrFail($data['pricing_profile_id']);
        if ((int) $pricingProfile->is_active !== 1) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Giá Vé đang tạm ngưng hoạt động.',
            ]);
        }
        if ($pricingProfile->cinema_id && (int) $pricingProfile->cinema_id !== (int) $auditorium->cinema_id) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Giá Vé này thuộc rạp khác, không thể áp cho phòng đã chọn.',
            ]);
        }

        $timezone = $this->resolveCinemaTimezone($auditorium->cinema->timezone ?? null);
        $startAt = Carbon::parse($data['show_date'] . ' ' . $data['start_clock'], $timezone);
        $durationMinutes = (int) $movieVersion->movie->duration_minutes;
        $endAt = $startAt->copy()->addMinutes($durationMinutes);

        $this->assertEndWithinBusinessHours($startAt, $endAt);
        $this->ensureNoOverlap($data['auditorium_id'], $startAt, $endAt, $data['status'], $show?->id);

        $onSaleFrom = $data['status'] === 'ON_SALE'
            ? now($timezone)->startOfMinute()
            : $startAt->copy()->subDays(7);

        $onSaleUntil = $startAt->copy();

        return [
            'auditorium_id' => (int) $data['auditorium_id'],
            'movie_version_id' => (int) $movieVersion->id,
            'pricing_profile_id' => (int) $pricingProfile->id,
            'start_time' => $startAt,
            'end_time' => $endAt,
            'on_sale_from' => $onSaleFrom,
            'on_sale_until' => $onSaleUntil,
            'status' => $data['status'],
            'show_count' => (int) ($data['show_count'] ?? 1),
            'break_minutes' => (int) ($data['break_minutes'] ?? 20),
            'duration_minutes' => $durationMinutes,
            'timezone' => $timezone,
        ];
    }

    private function buildSchedulePlan(array $validated): array
    {
        $showCount = max(1, (int) ($validated['show_count'] ?? 1));
        $breakMinutes = max(0, (int) ($validated['break_minutes'] ?? 20));

        $plan = [];
        $cursor = $validated['start_time']->copy();
        $latestAllowedEnd = $cursor->copy()->setTimeFromTimeString(self::MAX_END_CLOCK);

        for ($index = 0; $index < $showCount; $index++) {
            $slotStart = $cursor->copy();
            $slotEnd = $slotStart->copy()->addMinutes((int) $validated['duration_minutes']);

            if ($slotEnd->gt($latestAllowedEnd)) {
                if ($index === 0) {
                    throw ValidationException::withMessages([
                        'start_clock' => 'Suất chiếu vượt quá 23:00. Hãy chọn giờ sớm hơn hoặc phim ngắn hơn.',
                    ]);
                }

                throw ValidationException::withMessages([
                    'show_count' => "Chỉ có thể xếp được {$index} suất chiếu trước 23:00 với thời lượng phim và thời gian nghỉ hiện tại.",
                ]);
            }

            $this->ensureNoOverlap(
                $validated['auditorium_id'],
                $slotStart,
                $slotEnd,
                $validated['status'],
                null,
                $plan
            );

            $plan[] = [
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
                'on_sale_from' => $validated['status'] === 'ON_SALE'
                    ? now($validated['timezone'])->startOfMinute()
                    : $slotStart->copy()->subDays(7),
                'on_sale_until' => $slotStart->copy(),
            ];

            $cursor = $slotEnd->copy()->addMinutes($breakMinutes);
        }

        return $plan;
    }

    private function ensureNoOverlap(
        int $auditoriumId,
        Carbon $startAt,
        Carbon $endAt,
        string $status,
        ?int $ignoreShowId = null,
        array $plannedSlots = []
    ): void {
        if ($status === 'CANCELLED') {
            return;
        }

        $hasOverlap = Show::query()
            ->where('auditorium_id', $auditoriumId)
            ->where('status', '!=', 'CANCELLED')
            ->when($ignoreShowId, fn ($query) => $query->where('id', '!=', $ignoreShowId))
            ->where('start_time', '<', $endAt)
            ->where('end_time', '>', $startAt)
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'start_clock' => 'Không được để 2 phim chiếu cùng 1 phòng cùng giờ.',
            ]);
        }

        foreach ($plannedSlots as $slot) {
            if ($slot['start_time']->lt($endAt) && $slot['end_time']->gt($startAt)) {
                throw ValidationException::withMessages([
                    'show_count' => 'Chuỗi suất chiếu liên tiếp đang bị chồng thời gian. Hãy tăng thời gian nghỉ giữa các suất.',
                ]);
            }
        }
    }

    private function assertEndWithinBusinessHours(Carbon $startAt, Carbon $endAt): void
    {
        $latestAllowedEnd = $startAt->copy()->setTimeFromTimeString(self::MAX_END_CLOCK);

        if ($endAt->gt($latestAllowedEnd)) {
            throw ValidationException::withMessages([
                'start_clock' => 'Suất chiếu không được kết thúc sau 23:00.',
            ]);
        }
    }

    private function hasPriceRelevantChanges(Show $show, array $validated): bool
    {
        return (int) $show->auditorium_id !== (int) $validated['auditorium_id']
            || (int) $show->movie_version_id !== (int) $validated['movie_version_id']
            || (int) $show->pricing_profile_id !== (int) $validated['pricing_profile_id']
            || ! $show->start_time?->equalTo($validated['start_time'])
            || ! $show->end_time?->equalTo($validated['end_time']);
    }

    private function showHasLockedTickets(Show $show): bool
    {
        return $show->tickets()
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->exists();
    }

    private function resolveCinemaTimezone(?string $timezone): string
    {
        $timezone = $timezone ?: config('app.timezone', 'UTC');

        $aliases = [
            'Asia/Ha_Noi' => 'Asia/Ho_Chi_Minh',
            'Asia/Saigon' => 'Asia/Ho_Chi_Minh',
            'Vietnam' => 'Asia/Ho_Chi_Minh',
        ];

        $timezone = $aliases[$timezone] ?? $timezone;

        try {
            new \DateTimeZone($timezone);

            return $timezone;
        } catch (\Throwable $e) {
            return config('app.timezone', 'UTC');
        }
    }
}
