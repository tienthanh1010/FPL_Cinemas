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
        $cinemas = Cinema::orderBy('name')->get();
        $screenTypes = self::SCREEN_TYPES;

        return view('admin.auditoriums.create', compact('auditorium', 'cinemas', 'screenTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['public_id'] = (string) Str::ulid();

        Auditorium::create($data);

        return redirect()->route('admin.auditoriums.index')->with('success', 'Đã tạo phòng chiếu.');
    }

    public function edit(Auditorium $auditorium): View
    {
        $cinemas = Cinema::orderBy('name')->get();
        $screenTypes = self::SCREEN_TYPES;

        return view('admin.auditoriums.edit', compact('auditorium', 'cinemas', 'screenTypes'));
    }

    public function update(Request $request, Auditorium $auditorium): RedirectResponse
    {
        $data = $this->validateData($request, $auditorium);

        $auditorium->update($data);

        return redirect()->route('admin.auditoriums.index')->with('success', 'Đã cập nhật phòng chiếu.');
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
        $uniquePerCinema = Rule::unique('auditoriums', 'auditorium_code')
            ->where(fn ($q) => $q->where('cinema_id', $request->input('cinema_id')));

        if ($auditorium) {
            $uniquePerCinema = $uniquePerCinema->ignore($auditorium->id);
        }

        return $request->validate([
            'cinema_id' => ['required', 'integer', 'exists:cinemas,id'],
            'auditorium_code' => ['required', 'string', 'max:32', $uniquePerCinema],
            'name' => ['required', 'string', 'max:255'],
            'screen_type' => ['required', Rule::in(self::SCREEN_TYPES)],
            'seat_map_version' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
