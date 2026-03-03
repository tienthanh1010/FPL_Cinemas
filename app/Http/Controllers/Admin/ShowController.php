<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\MovieVersion;
use App\Models\Show;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShowController extends Controller
{
    private const STATUSES = ['SCHEDULED', 'ON_SALE', 'SOLD_OUT', 'CANCELLED', 'ENDED'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $shows = Show::query()
            ->with(['movieVersion.movie', 'auditorium.cinema'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('movieVersion.movie', function ($q2) use ($q) {
                    $q2->where('title', 'like', "%{$q}%");
                })->orWhereHas('auditorium.cinema', function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shows.index', compact('shows', 'q'));
    }

    public function create(): View
    {
        $show = new Show();
        $auditoriums = Auditorium::with('cinema')->orderBy('name')->get();
        $movieVersions = MovieVersion::with('movie')->orderByDesc('id')->get();
        $statuses = self::STATUSES;

        return view('admin.shows.create', compact('show', 'auditoriums', 'movieVersions', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['public_id'] = (string) Str::ulid();
        $data['created_by'] = (int) $request->session()->get('admin_user_id');

        Show::create($data);

        return redirect()->route('admin.shows.index')->with('success', 'Đã tạo suất chiếu.');
    }

    public function edit(Show $show): View
    {
        $auditoriums = Auditorium::with('cinema')->orderBy('name')->get();
        $movieVersions = MovieVersion::with('movie')->orderByDesc('id')->get();
        $statuses = self::STATUSES;

        return view('admin.shows.edit', compact('show', 'auditoriums', 'movieVersions', 'statuses'));
    }

    public function update(Request $request, Show $show): RedirectResponse
    {
        $data = $this->validateData($request);

        $show->update($data);

        return redirect()->route('admin.shows.index')->with('success', 'Đã cập nhật suất chiếu.');
    }

    public function destroy(Show $show): RedirectResponse
    {
        try {
            DB::transaction(function () use ($show) {
                $show->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá suất chiếu (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá suất chiếu.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'auditorium_id' => ['required', 'integer', 'exists:auditoriums,id'],
            'movie_version_id' => ['required', 'integer', 'exists:movie_versions,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'on_sale_from' => ['nullable', 'date'],
            'on_sale_until' => ['nullable', 'date'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);
    }
}
