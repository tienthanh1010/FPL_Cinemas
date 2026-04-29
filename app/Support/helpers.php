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

if (! function_exists('booking_hold_minutes')) {
    function booking_hold_minutes(): int
    {
        return max(1, (int) config('cinema_booking.seat_hold_minutes', 2));
    }
}

if (! function_exists('movie_blocks_child_tickets')) {
    function movie_blocks_child_tickets(?\App\Models\Movie $movie): bool
    {
        if (! $movie || ! $movie->contentRating) {
            return true;
        }

        $ratingCode = strtoupper(trim((string) ($movie->contentRating?->code ?? '')));
        $minAge = (int) ($movie->contentRating?->min_age ?? 0);

        return ! ($ratingCode === 'P' && $minAge <= 0);
    }
}

if (! function_exists('booking_transfer_reference')) {
    function booking_transfer_reference(string $value): string
    {
        $normalized = \Illuminate\Support\Str::of($value)
            ->upper()
            ->ascii()
            ->replaceMatches('/[^A-Z0-9 ]+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();

        return \Illuminate\Support\Str::limit($normalized, 50, '');
    }
}

if (! function_exists('vietqr_url')) {
    function vietqr_url(
        string $bankId,
        string $accountNo,
        int $amount = 0,
        ?string $addInfo = null,
        ?string $accountName = null,
        ?string $template = null,
    ): string {
        $template ??= (string) config('cinema_booking.bank_transfer.qr_template', 'compact2');

        $query = array_filter([
            'amount' => max(0, $amount),
            'addInfo' => $addInfo ? booking_transfer_reference($addInfo) : null,
            'accountName' => $accountName ?: null,
        ], fn ($value) => $value !== null && $value !== '');

        return 'https://img.vietqr.io/image/'
            . rawurlencode($bankId)
            . '-'
            . rawurlencode($accountNo)
            . '-'
            . rawurlencode($template)
            . '.png'
            . ($query ? ('?' . http_build_query($query)) : '');
    }
}

if (! function_exists('ticket_qr_image_url')) {
    function ticket_qr_image_url(?string $payload, int $size = 220): ?string
    {
        if (! $payload) {
            return null;
        }

        return 'https://quickchart.io/qr?text=' . rawurlencode($payload) . '&size=' . max(120, $size);
    }
}


if (! function_exists('ticket_barcode_image_url')) {
    function ticket_barcode_image_url(?string $payload, int $height = 72): ?string
    {
        if (! $payload) {
            return null;
        }

        $value = strtoupper(trim((string) $payload));
        if ($value === '') {
            return null;
        }

        $patterns = [
            '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn',
            '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw',
            '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn', 'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw',
            'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw', 'E' => 'wnnnwwnnn', 'F' => 'nnwnwwnnn',
            'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn', 'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn',
            'K' => 'wnnnnnnww', 'L' => 'nnwnnnnww', 'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww',
            'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn', 'Q' => 'nnnnnnwww', 'R' => 'wnnnnnwwn',
            'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn', 'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw',
            'W' => 'wwwnnnnnn', 'X' => 'nwnnwnnnw', 'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn',
            '-' => 'nwnnnnwnw', '.' => 'wwnnnnwnn', ' ' => 'nwwnnnwnn', '$' => 'nwnwnwnnn',
            '/' => 'nwnwnnnwn', '+' => 'nwnnnwnwn', '%' => 'nnnwnwnwn', '*' => 'nwnnwnwnn',
        ];

        $encoded = '*';
        foreach (str_split($value) as $char) {
            $encoded .= array_key_exists($char, $patterns) ? $char : '-';
        }
        $encoded .= '*';

        $narrow = 2;
        $wide = 5;
        $gap = 2;
        $quiet = 12;
        $barHeight = max(36, $height);
        $textHeight = 18;
        $totalHeight = $barHeight + $textHeight;

        $width = $quiet * 2;
        foreach (str_split($encoded) as $char) {
            foreach (str_split($patterns[$char]) as $stroke) {
                $width += $stroke === 'w' ? $wide : $narrow;
            }
            $width += $gap;
        }
        $width = max($width, 180);

        $x = $quiet;
        $bars = [];
        foreach (str_split($encoded) as $char) {
            $sequence = str_split($patterns[$char]);
            foreach ($sequence as $index => $stroke) {
                $strokeWidth = $stroke === 'w' ? $wide : $narrow;
                if ($index % 2 === 0) {
                    $bars[] = '<rect x="' . $x . '" y="0" width="' . $strokeWidth . '" height="' . $barHeight . '" rx="0.4" ry="0.4" fill="#111827" />';
                }
                $x += $strokeWidth;
            }
            $x += $gap;
        }

        $labelY = $barHeight + 13;
        $safeLabel = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $totalHeight . '" viewBox="0 0 ' . $width . ' ' . $totalHeight . '" role="img" aria-label="Barcode ' . $safeLabel . '">'
            . '<rect width="100%" height="100%" fill="#ffffff"/>'
            . implode('', $bars)
            . '<text x="50%" y="' . $labelY . '" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="700" letter-spacing="1.2" fill="#111827">' . $safeLabel . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}

if (! function_exists('ticket_scan_payload')) {
    function ticket_scan_payload(?\App\Models\Ticket $ticket): ?string
    {
        if (! $ticket) {
            return null;
        }

        return $ticket->ticket_code ?: ($ticket->qr_payload ?: null);
    }
}
