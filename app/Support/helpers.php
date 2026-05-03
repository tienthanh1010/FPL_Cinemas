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

if (! function_exists('admin_language_options')) {
    function admin_language_options(): array
    {
        return [
            'VI' => 'Tiếng Việt',
            'EN' => 'Tiếng Anh',
            'JA' => 'Tiếng Nhật',
            'KO' => 'Tiếng Hàn',
            'ZH' => 'Tiếng Trung',
            'TH' => 'Tiếng Thái',
            'FR' => 'Tiếng Pháp',
            'DE' => 'Tiếng Đức',
            'ES' => 'Tiếng Tây Ban Nha',
            'OTHER' => 'Khác',
        ];
    }
}

if (! function_exists('admin_movie_format_options')) {
    function admin_movie_format_options(): array
    {
        return [
            '2D' => '2D',
            '3D' => '3D',
            'IMAX' => 'IMAX',
            '4DX' => '4DX',
            'SCREENX' => 'SCREENX',
            'DOLBY' => 'DOLBY',
        ];
    }
}

if (! function_exists('admin_timezone_options')) {
    function admin_timezone_options(): array
    {
        return [
            'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh (Việt Nam)',
            'Asia/Bangkok' => 'Asia/Bangkok (Thái Lan)',
            'Asia/Singapore' => 'Asia/Singapore',
            'Asia/Tokyo' => 'Asia/Tokyo (Nhật Bản)',
            'Asia/Seoul' => 'Asia/Seoul (Hàn Quốc)',
            'UTC' => 'UTC',
        ];
    }
}

if (! function_exists('admin_opening_hour_day_labels')) {
    function admin_opening_hour_day_labels(): array
    {
        return [
            'mon' => 'Thứ 2',
            'tue' => 'Thứ 3',
            'wed' => 'Thứ 4',
            'thu' => 'Thứ 5',
            'fri' => 'Thứ 6',
            'sat' => 'Thứ 7',
            'sun' => 'Chủ nhật',
        ];
    }
}

if (! function_exists('admin_movie_role_labels')) {
    function admin_movie_role_labels(): array
    {
        return [
            'DIRECTOR' => 'Đạo diễn',
            'WRITER' => 'Biên kịch',
            'CAST' => 'Diễn viên',
        ];
    }
}


if (! function_exists('single_cinema_mode')) {
    function single_cinema_mode(): bool
    {
        return app(\App\Services\CinemaContextService::class)->singleMode();
    }
}

if (! function_exists('current_cinema')) {
    function current_cinema(): ?\App\Models\Cinema
    {
        return app(\App\Services\CinemaContextService::class)->currentCinema();
    }
}

if (! function_exists('current_cinema_id')) {
    function current_cinema_id(): ?int
    {
        return app(\App\Services\CinemaContextService::class)->currentCinemaId();
    }
}

if (! function_exists('member_customer')) {
    function member_customer(): ?\App\Models\Customer
    {
        $user = auth()->user();

        return $user ? app(\App\Services\CustomerAccountService::class)->customerForUser($user) : null;
    }
}

if (! function_exists('loyalty_preview_points')) {
    function loyalty_preview_points(int $amount): int
    {
        return app(\App\Services\LoyaltyPointService::class)->previewPoints($amount);
    }
}
