<?php

return [
    'time_windows' => [
        [
            'name' => 'Giờ cao điểm tối',
            'start' => '18:00:00',
            'end' => '23:00:00',
            'mode' => 'AMOUNT_DELTA',
            'value' => 15000,
        ],
        [
            'name' => 'Cuối tuần',
            'days' => [6, 7],
            'mode' => 'AMOUNT_DELTA',
            'value' => 10000,
        ],
    ],

    'holidays' => [
        ['name' => 'Tết Dương lịch', 'from' => '2026-01-01', 'to' => '2026-01-03', 'mode' => 'AMOUNT_DELTA', 'value' => 20000],
        ['name' => 'Giỗ tổ Hùng Vương', 'from' => '2026-04-26', 'to' => '2026-04-26', 'mode' => 'AMOUNT_DELTA', 'value' => 20000],
        ['name' => '30/4 - 1/5', 'from' => '2026-04-30', 'to' => '2026-05-01', 'mode' => 'AMOUNT_DELTA', 'value' => 20000],
        ['name' => 'Quốc khánh', 'from' => '2026-09-02', 'to' => '2026-09-02', 'mode' => 'AMOUNT_DELTA', 'value' => 20000],
        ['name' => 'Tết Âm lịch (demo)', 'from' => '2026-02-15', 'to' => '2026-02-21', 'mode' => 'AMOUNT_DELTA', 'value' => 25000],
    ],
];
