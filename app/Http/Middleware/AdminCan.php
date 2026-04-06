<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCan
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $adminId = (int) $request->session()->get('admin_user_id', 0);

        if ($adminId <= 0) {
            return redirect()->route('admin.login');
        }

        $adminUser = AdminUser::with('roles')->find($adminId);

        if (! $adminUser) {
            $request->session()->forget('admin_user_id');
            return redirect()->route('admin.login');
        }

        if ($permissions === []) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($adminUser->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Bạn không có quyền truy cập chức năng này.');
    }
}
