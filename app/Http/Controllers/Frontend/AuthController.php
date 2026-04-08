<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\User;
use App\Services\CustomerAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly CustomerAccountService $customerAccountService)
    {
    }

    public function showLogin(): View
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (Schema::hasTable('admin_users')) {
            $adminUser = AdminUser::query()->where('email', $credentials['email'])->first();
            if ($adminUser && Hash::check($credentials['password'], $adminUser->password)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->regenerate();
                $request->session()->put('admin_user_id', $adminUser->id);

                return redirect()->intended(route('admin.dashboard'));
            }
        }

        $request->session()->forget('admin_user_id');

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        $request->session()->regenerate();
        $this->customerAccountService->syncCustomerForUser($request->user());

        return redirect()->intended(route('member.account'));
    }

    public function showRegister(): View
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Schema::hasTable('admin_users') && AdminUser::query()->where('email', $value)->exists()) {
                        $fail('Email này đang được dùng cho tài khoản quản trị. Vui lòng dùng email khác.');
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->customerAccountService->syncCustomerForUser($user, [
            'full_name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
        ]);

        Auth::login($user, true);
        $request->session()->forget('admin_user_id');
        $request->session()->regenerate();

        return redirect()->route('member.account')
            ->with('success', 'Tạo tài khoản thành công. Bạn có thể bắt đầu đặt vé và tích điểm ngay bây giờ.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->forget('admin_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
