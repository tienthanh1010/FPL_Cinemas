<?php

use Illuminate\Http\RedirectResponse;

/**
 * Custom helpers (Laravel already has url() and redirect()).
 * These wrappers are kept to match the requested deliverables.
 */

if (! function_exists('url_to')) {
    function url_to(string $path = ''): string
    {
        return url($path);
    }
}

if (! function_exists('redirect_to')) {
    function redirect_to(string $to, int $status = 302): RedirectResponse
    {
        return redirect($to, $status);
    }
}

if (! function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        return url('admin' . ($path ? '/' . $path : ''));
    }
}
