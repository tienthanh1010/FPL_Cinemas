<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CinemaChain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CinemaChainController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $chains = CinemaChain::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('chain_code', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.chains.index', compact('chains', 'q'));
    }

    public function create(): View
    {
        $chain = new CinemaChain();

        return view('admin.chains.create', compact('chain'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['public_id'] = (string) Str::ulid();

        CinemaChain::create($data);

        return redirect()->route('admin.chains.index')->with('success', 'Đã tạo chuỗi rạp.');
    }

    public function edit(CinemaChain $chain): View
    {
        return view('admin.chains.edit', compact('chain'));
    }

    public function update(Request $request, CinemaChain $chain): RedirectResponse
    {
        $data = $this->validateData($request, $chain);

        $chain->update($data);

        return redirect()->route('admin.chains.index')->with('success', 'Đã cập nhật chuỗi rạp.');
    }

    public function destroy(CinemaChain $chain): RedirectResponse
    {
        try {
            DB::transaction(function () use ($chain) {
                $chain->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá chuỗi rạp (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá chuỗi rạp.');
    }

    private function validateData(Request $request, ?CinemaChain $chain = null): array
    {
        return $request->validate([
            'chain_code' => [
                'required',
                'string',
                'max:32',
                $chain ? Rule::unique('cinema_chains', 'chain_code')->ignore($chain->id) : Rule::unique('cinema_chains', 'chain_code'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_code' => ['nullable', 'string', 'max:32'],
            'hotline' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
