<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $adminUsers = AdminUser::query()
            ->with('roles')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $report = [
            'total' => AdminUser::count(),
            'admins' => AdminUser::whereHas('roles', fn ($query) => $query->where('code', 'ADMIN'))->count(),
            'managers' => AdminUser::whereHas('roles', fn ($query) => $query->where('code', 'MANAGER'))->count(),
            'checkin' => AdminUser::whereHas('roles', fn ($query) => $query->where('code', 'TICKET_CHECKIN'))->count(),
        ];

        return view('admin.admin_users.index', compact('adminUsers', 'q', 'report'));
    }

    public function create(): View
    {
        return view('admin.admin_users.create', $this->formData(new AdminUser()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAdminUser($request);
        $adminUser = AdminUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $adminUser->roles()->sync($data['role_ids']);

        return redirect()->route('admin.admin_users.show', $adminUser)->with('success', 'Đã tạo tài khoản admin.');
    }

    public function show(AdminUser $adminUser): View
    {
        $adminUser->load('roles');

        return view('admin.admin_users.show', compact('adminUser'));
    }

    public function edit(AdminUser $adminUser): View
    {
        $adminUser->load('roles');

        return view('admin.admin_users.edit', $this->formData($adminUser));
    }

    public function update(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $data = $this->validateAdminUser($request, $adminUser);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $adminUser->update($payload);
        $adminUser->roles()->sync($data['role_ids']);

        return redirect()->route('admin.admin_users.show', $adminUser)->with('success', 'Đã cập nhật tài khoản admin.');
    }

    public function destroy(Request $request, AdminUser $adminUser): RedirectResponse
    {
        if ((int) $request->session()->get('admin_user_id') === $adminUser->id) {
            return back()->withErrors(['delete' => 'Bạn không thể tự xoá tài khoản đang đăng nhập.']);
        }

        $adminUser->roles()->detach();
        $adminUser->delete();

        return redirect()->route('admin.admin_users.index')->with('success', 'Đã xoá tài khoản admin.');
    }

    private function formData(AdminUser $adminUser): array
    {
        return [
            'adminUserModel' => $adminUser,
            'roles' => Role::orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAdminUser(Request $request, ?AdminUser $adminUser = null): array
    {
        $isCreate = ! $adminUser?->exists;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admin_users', 'email')->ignore($adminUser?->id)],
            'password' => array_values(array_filter([
                $isCreate ? 'required' : 'nullable',
                'string',
                'min:6',
                'confirmed',
            ])),
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);
    }
}
