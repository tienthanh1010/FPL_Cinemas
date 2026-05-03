<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((int) $request->session()->get('admin_user_id', 0) > 0) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
