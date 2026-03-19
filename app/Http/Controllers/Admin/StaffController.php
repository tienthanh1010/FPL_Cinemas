<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $staff = Staff::query()
            ->with(['cinema', 'roles'])
            ->withCount('shifts')
            ->when($q !== '', fn ($query) => $query->where('full_name', 'like', "%{$q}%")->orWhere('staff_code', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
        $report = [
            'total' => Staff::count(),
            'active' => Staff::where('status', 'ACTIVE')->count(),
            'manager' => Staff::whereHas('roles', fn ($q) => $q->where('code', 'MANAGER'))->count(),
            'ticket_counter' => Staff::whereHas('roles', fn ($q) => $q->where('code', 'TICKET_COUNTER'))->count(),
        ];
        return view('admin.staff.index', compact('staff', 'q', 'report'));
    }

    public function create(): View
    {
        return view('admin.staff.create', $this->formData(new Staff()));
    }

    public function store(Request $request): RedirectResponse
    {
        $staff = Staff::create($this->validateStaff($request) + ['public_id' => (string) Str::ulid()]);
        $staff->roles()->sync($request->input('role_ids', []));
        return redirect()->route('admin.staff.show', $staff)->with('success', 'Đã thêm nhân sự.');
    }

    public function show(Staff $staff): View
    {
        $staff->load(['cinema', 'roles', 'shifts', 'maintenanceRequests.equipment']);
        return view('admin.staff.show', compact('staff'));
    }

    public function edit(Staff $staff): View
    {
        $staff->load('roles');
        return view('admin.staff.edit', $this->formData($staff));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $staff->update($this->validateStaff($request, $staff));
        $staff->roles()->sync($request->input('role_ids', []));
        return redirect()->route('admin.staff.show', $staff)->with('success', 'Đã cập nhật nhân sự.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->roles()->detach();
        $staff->shifts()->detach();
        $staff->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Đã xoá nhân sự.');
    }

    private function formData(Staff $staff): array
    {
        return [
            'staff' => $staff,
            'cinemas' => Cinema::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
        ];
    }

    private function validateStaff(Request $request, ?Staff $staff = null): array
    {
        return $request->validate([
            'cinema_id' => ['required', 'integer', 'exists:cinemas,id'],
            'staff_code' => ['required', 'string', 'max:32', Rule::unique('staff', 'staff_code')->ignore($staff?->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'ON_LEAVE'])],
            'hired_at' => ['nullable', 'date'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);
    }
}
