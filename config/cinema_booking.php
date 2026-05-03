<?php

return [
    'seat_hold_minutes' => (int) env('CINEMA_SEAT_HOLD_MINUTES', 2),
    'seat_poll_seconds' => (int) env('CINEMA_SEAT_POLL_SECONDS', 5),
    'max_seats_per_booking' => (int) env('CINEMA_MAX_SEATS_PER_BOOKING', 10),
    'max_pending_per_contact_per_show' => (int) env('CINEMA_MAX_PENDING_PER_CONTACT_PER_SHOW', 2),
    'max_expired_per_contact_per_show' => (int) env('CINEMA_MAX_EXPIRED_PER_CONTACT_PER_SHOW', 3),
    'abuse_block_minutes' => (int) env('CINEMA_ABUSE_BLOCK_MINUTES', 60),
    'hold_sync_limit_per_2_minutes' => (int) env('CINEMA_HOLD_SYNC_LIMIT_PER_2_MINUTES', 40),
    'hold_sync_decay_seconds' => (int) env('CINEMA_HOLD_SYNC_DECAY_SECONDS', 120),

    'bank_transfer' => [
        'provider_label' => env('CINEMA_BANK_PROVIDER_LABEL', 'MB Bank'),
        'bank_id' => env('CINEMA_BANK_QR_ID', 'MBBank'),
        'account_no' => env('CINEMA_BANK_ACCOUNT_NO', '000230705'),
        'account_name' => env('CINEMA_BANK_ACCOUNT_NAME', 'FPL CINEMA'),
        'qr_template' => env('CINEMA_BANK_QR_TEMPLATE', 'compact2'),
        'description_prefix' => env('CINEMA_BANK_TRANSFER_PREFIX', 'FPL'),
    ],
];
