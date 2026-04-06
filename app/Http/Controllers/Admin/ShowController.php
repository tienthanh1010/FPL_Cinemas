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
        'ON_SALE' => 'Đang mở bán',
        'SOLD_OUT' => 'Hết vé',
        'ENDED' => 'Đã chiếu',
        'CANCELLED' => 'Huỷ',
    ];

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

        $show = DB::transaction(function () use ($validated, $request) {
            $show = Show::create([
                'public_id' => (string) Str::ulid(),
                'auditorium_id' => $validated['auditorium_id'],
                'movie_version_id' => $validated['movie_version_id'],
                'pricing_profile_id' => $validated['pricing_profile_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'on_sale_from' => $validated['start_time']->copy()->subDays(7),
                'on_sale_until' => $validated['start_time'],
                'status' => $validated['status'],
                'created_by' => (int) $request->session()->get('admin_user_id'),
            ]);

            $this->pricingService->syncShowPrices($show->load('pricingProfile'));

            return $show;
        });

        return redirect()->route('admin.shows.show', $show)->with('success', 'Đã tạo suất chiếu.');
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
        $ticketTypeNames = TicketType::query()->pluck('name', 'id');

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

        DB::transaction(function () use ($validated, $show) {
            $show->update([
                'auditorium_id' => $validated['auditorium_id'],
                'movie_version_id' => $validated['movie_version_id'],
                'pricing_profile_id' => $validated['pricing_profile_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'on_sale_from' => $validated['start_time']->copy()->subDays(7),
                'on_sale_until' => $validated['start_time'],
                'status' => $validated['status'],
            ]);

            $this->pricingService->syncShowPrices($show->load('pricingProfile'));
        });

        return redirect()->route('admin.shows.show', $show)->with('success', 'Đã cập nhật suất chiếu.');
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

        return [
            'show' => $show,
            'auditoriums' => Auditorium::with('cinema')->orderBy('name')->get(),
            'movies' => Movie::query()->where('status', 'ACTIVE')->with('versions')->orderBy('title')->get(),
            'profiles' => PricingProfile::query()->where('is_active', 1)->orderBy('name')->get(),
            'statusOptions' => self::STATUSES,
            'selectedMovieId' => old('movie_id', $show->movieVersion?->movie_id),
        ];
    }

    private function validateData(Request $request, ?Show $show = null): array
    {
        $data = $request->validate([
            'auditorium_id' => ['required', 'integer', 'exists:auditoriums,id'],
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'pricing_profile_id' => ['required', 'integer', 'exists:pricing_profiles,id'],
            'show_date' => ['required', 'date'],
            'start_clock' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $auditorium = Auditorium::query()->with('cinema')->findOrFail($data['auditorium_id']);
        $movieVersion = MovieVersion::query()->where('movie_id', $data['movie_id'])->orderBy('id')->first();
        if (! $movieVersion) {
            throw ValidationException::withMessages([
                'movie_id' => 'Phim này chưa có phiên bản chiếu. Hãy vào phần Phim để thêm ít nhất 1 phiên bản.',
            ]);
        }

        $pricingProfile = PricingProfile::query()->findOrFail($data['pricing_profile_id']);
        if ((int) $pricingProfile->is_active !== 1) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Hồ sơ giá đang tạm ngưng hoạt động.',
            ]);
        }
        if ($pricingProfile->cinema_id && (int) $pricingProfile->cinema_id !== (int) $auditorium->cinema_id) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Hồ sơ giá này thuộc rạp khác, không thể áp cho phòng đã chọn.',
            ]);
        }

        $startAt = Carbon::parse(
            $data['show_date'] . ' ' . $data['start_clock'],
            $this->resolveCinemaTimezone($auditorium->cinema->timezone ?? null)
        );
        $endAt = $startAt->copy()->addMinutes((int) $movieVersion->movie->duration_minutes);

        if ($data['status'] !== 'CANCELLED') {
            $hasOverlap = Show::query()
                ->where('auditorium_id', $data['auditorium_id'])
                ->where('status', '!=', 'CANCELLED')
                ->when($show, fn ($query) => $query->where('id', '!=', $show->id))
                ->where('start_time', '<', $endAt)
                ->where('end_time', '>', $startAt)
                ->exists();

            if ($hasOverlap) {
                throw ValidationException::withMessages([
                    'start_clock' => 'Không được để 2 phim chiếu cùng 1 phòng cùng giờ.',
                ]);
            }
        }

        return [
            'auditorium_id' => (int) $data['auditorium_id'],
            'movie_version_id' => (int) $movieVersion->id,
            'pricing_profile_id' => (int) $pricingProfile->id,
            'start_time' => $startAt,
            'end_time' => $endAt,
            'status' => $data['status'],
        ];
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
