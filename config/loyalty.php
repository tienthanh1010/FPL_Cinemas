<?php

return [
    'enabled' => (bool) env('LOYALTY_ENABLED', true),
    'amount_per_point' => max(1, (int) env('LOYALTY_AMOUNT_PER_POINT', 10000)),
    'point_name' => env('LOYALTY_POINT_NAME', 'Điểm thưởng'),
    'welcome_points' => max(0, (int) env('LOYALTY_WELCOME_POINTS', 0)),
];
