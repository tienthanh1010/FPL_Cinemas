<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = (int) $request->session()->get('admin_user_id', 0);

        if ($adminId <= 0) {
            return redirect()->route('admin.login');
        }

        // Share current admin user to all admin views
        $adminUser = AdminUser::find($adminId);
        if (!$adminUser) {
            $request->session()->forget('admin_user_id');
            return redirect()->route('admin.login');
        }

        view()->share('adminUser', $adminUser);

        return $next($request);
    }
}
