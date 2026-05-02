<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\Cinema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditoriumController extends Controller
{
    private const SCREEN_TYPES = ['STANDARD', 'IMAX', 'DOLBY', '4DX', 'SCREENX', 'GOLDCLASS'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $auditoriums = Auditorium::query()
            ->with('cinema')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('auditorium_code', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.auditoriums.index', compact('auditoriums', 'q'));
    }

    public function create(): View
    {
        $auditorium = new Auditorium();
        $screenTypes = self::SCREEN_TYPES;

        return view('admin.auditoriums.create', compact('auditorium', 'screenTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['public_id'] = (string) Str::ulid();
        $data['cinema_id'] = $this->resolveCinemaId();

        $auditorium = Auditorium::create($data);

        return redirect()->route('admin.auditoriums.show', $auditorium)->with('success', 'Đã tạo phòng chiếu.');
    }

    public function show(Auditorium $auditorium): View
    {
        $auditorium->load(['cinema', 'shows.movieVersion.movie']);
        $seats = DB::table('seats as s')
            ->leftJoin('seat_types as st', 'st.id', '=', 's.seat_type_id')
            ->where('s.auditorium_id', $auditorium->id)
            ->orderBy('s.row_label')
            ->orderBy('s.col_number')
            ->get(['s.*', 'st.name as seat_type_name']);

        $seatStats = [
            'total' => $seats->count(),
            'active' => $seats->where('is_active', 1)->count(),
            'maintenance' => $seats->where('is_active', 0)->count(),
        ];

        return view('admin.auditoriums.show', compact('auditorium', 'seats', 'seatStats'));
    }

    public function edit(Auditorium $auditorium): View
    {
        $screenTypes = self::SCREEN_TYPES;

        return view('admin.auditoriums.edit', compact('auditorium', 'screenTypes'));
    }

    public function update(Request $request, Auditorium $auditorium): RedirectResponse
    {
        $data = $this->validateData($request, $auditorium);
        $data['cinema_id'] = $auditorium->cinema_id ?: $this->resolveCinemaId();

        $auditorium->update($data);

        return redirect()->route('admin.auditoriums.show', $auditorium)->with('success', 'Đã cập nhật phòng chiếu.');
    }

    public function destroy(Auditorium $auditorium): RedirectResponse
    {
        try {
            DB::transaction(function () use ($auditorium) {
                $auditorium->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá phòng chiếu (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá phòng chiếu.');
    }

    private function validateData(Request $request, ?Auditorium $auditorium = null): array
    {
        $cinemaId = $auditorium?->cinema_id ?: $this->resolveCinemaId();
        $uniquePerCinema = Rule::unique('auditoriums', 'auditorium_code')
            ->where(fn ($q) => $q->where('cinema_id', $cinemaId));

        if ($auditorium) {
            $uniquePerCinema = $uniquePerCinema->ignore($auditorium->id);
        }

        return $request->validate([
            'auditorium_code' => ['required', 'string', 'max:32', $uniquePerCinema],
            'name' => ['required', 'string', 'max:255'],
            'screen_type' => ['required', Rule::in(self::SCREEN_TYPES)],
            'seat_map_version' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function resolveCinemaId(): int
    {
        $cinema = Cinema::query()->first();

        if (! $cinema) {
            abort(422, 'Bạn cần tạo rạp trước khi tạo phòng chiếu.');
        }

        return (int) $cinema->id;
    }
}
