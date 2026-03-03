<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\CinemaChain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CinemaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $cinemas = Cinema::query()
            ->with('chain')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('cinema_code', 'like', "%{$q}%")
                    ->orWhere('province', 'like', "%{$q}%")
                    ->orWhere('district', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cinemas.index', compact('cinemas', 'q'));
    }

    public function create(): View
    {
        $cinema = new Cinema();
        $chains = CinemaChain::orderBy('name')->get();

        return view('admin.cinemas.create', compact('cinema', 'chains'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->normalizeJson($data);
        $data['public_id'] = (string) Str::ulid();

        Cinema::create($data);

        return redirect()->route('admin.cinemas.index')->with('success', 'Đã tạo rạp.');
    }

    public function edit(Cinema $cinema): View
    {
        $chains = CinemaChain::orderBy('name')->get();

        return view('admin.cinemas.edit', compact('cinema', 'chains'));
    }

    public function update(Request $request, Cinema $cinema): RedirectResponse
    {
        $data = $this->validateData($request, $cinema);
        $data = $this->normalizeJson($data);

        $cinema->update($data);

        return redirect()->route('admin.cinemas.index')->with('success', 'Đã cập nhật rạp.');
    }

    public function destroy(Cinema $cinema): RedirectResponse
    {
        try {
            DB::transaction(function () use ($cinema) {
                $cinema->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá rạp (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá rạp.');
    }

    private function validateData(Request $request, ?Cinema $cinema = null): array
    {
        return $request->validate([
            'chain_id' => ['required', 'integer', 'exists:cinema_chains,id'],
            'cinema_code' => [
                'required',
                'string',
                'max:32',
                $cinema
                    ? Rule::unique('cinemas', 'cinema_code')->ignore($cinema->id)
                    : Rule::unique('cinemas', 'cinema_code'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:128'],
            'district' => ['nullable', 'string', 'max:128'],
            'province' => ['nullable', 'string', 'max:128'],
            'country_code' => ['required', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'opening_hours' => ['nullable', 'json'], // JSON string
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }

    private function normalizeJson(array $data): array
    {
        if (array_key_exists('opening_hours', $data)) {
            $raw = $data['opening_hours'];
            if ($raw === null || $raw === '') {
                $data['opening_hours'] = null;
            } else {
                $data['opening_hours'] = json_decode($raw, true);
            }
        }

        return $data;
    }
}
