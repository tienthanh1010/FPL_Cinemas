<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class CinemaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // Truncate đúng thứ tự FK
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'tickets',
            'booking_tickets',
            'payments',
            'bookings',
            'show_prices',
            'shows',
            'movie_versions',
            'movie_genres',
            'genres',
            'movies',
            'content_ratings',
            'seats',
            'seat_types',
            'auditoriums',
            'cinemas',
            'cinema_chains',
            'customers',
            'sales_channels',
            'ticket_types',
        ] as $t) {
            DB::table($t)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 1) cinema_chains
        $chainId = DB::table('cinema_chains')->insertGetId([
            'public_id'  => (string) Str::ulid(),
            'chain_code' => 'CINEVN',
            'name'       => 'CineVN',
            'legal_name' => 'CÔNG TY TNHH CINEVN',
            'tax_code'   => '0312345678',
            'hotline'    => '1900 1234',
            'email'      => 'support@cinevn.test',
            'website'    => 'https://cinevn.test',
            'status'     => 'ACTIVE',
        ]);

        // 2) cinemas
        $cinemas = [];
        $cinemaSeeds = [
            ['code' => 'HCM01', 'name' => 'CineVN - Quận 1', 'province' => 'TP. Hồ Chí Minh', 'district' => 'Quận 1', 'ward' => 'Bến Nghé'],
            ['code' => 'HN01',  'name' => 'CineVN - Hoàn Kiếm', 'province' => 'Hà Nội', 'district' => 'Hoàn Kiếm', 'ward' => 'Hàng Trống'],
        ];

        foreach ($cinemaSeeds as $c) {
            $cinemas[] = DB::table('cinemas')->insertGetId([
                'public_id'     => (string) Str::ulid(),
                'chain_id'      => $chainId,
                'cinema_code'   => $c['code'],
                'name'          => $c['name'],
                'phone'         => $faker->phoneNumber(),
                'email'         => $faker->safeEmail(),
                'timezone'      => 'Asia/Ho_Chi_Minh',
                'address_line'  => $faker->streetAddress(),
                'ward'          => $c['ward'],
                'district'      => $c['district'],
                'province'      => $c['province'],
                'country_code'  => 'VN',
                'latitude'      => null,
                'longitude'     => null,
                'opening_hours' => json_encode([
                    'mon' => '09:00-23:00', 'tue' => '09:00-23:00', 'wed' => '09:00-23:00',
                    'thu' => '09:00-23:00', 'fri' => '09:00-24:00', 'sat' => '09:00-24:00', 'sun' => '09:00-23:00',
                ], JSON_UNESCAPED_UNICODE),
                'status'        => 'ACTIVE',
            ]);
        }

        // 3) auditoriums (KHÔNG có sound_system/status)
        $auditoriums = []; // [cinema_id => [auditorium_ids...]]
        foreach ($cinemas as $cinemaId) {
            $auditoriums[$cinemaId] = [];
            for ($i = 1; $i <= 3; $i++) {
                $auditoriums[$cinemaId][] = DB::table('auditoriums')->insertGetId([
                    'public_id'       => (string) Str::ulid(),
                    'cinema_id'       => $cinemaId,
                    'auditorium_code' => "AUD{$cinemaId}_{$i}",
                    'name'            => "Phòng {$i}",
                    'screen_type'     => ($i === 3) ? 'IMAX' : 'STANDARD',
                    'seat_map_version'=> 1,
                    'is_active'       => 1,
                ]);
            }
        }

        // 4) seat_types
        $seatTypeStandardId = DB::table('seat_types')->insertGetId([
            'code' => 'REGULAR',
            'name' => 'Ghế thường',
            'description' => 'Ghế tiêu chuẩn',
        ]);

        $seatTypeVipId = DB::table('seat_types')->insertGetId([
            'code' => 'VIP',
            'name' => 'Ghế VIP',
            'description' => 'Ghế VIP (hàng đẹp)',
        ]);

        $seatTypeCoupleId = DB::table('seat_types')->insertGetId([
            'code' => 'COUPLE',
            'name' => 'Ghế đôi',
            'description' => 'Ghế đôi (sweetbox)',
        ]);

        // 5) seats (KHÔNG có public_id, cột là col_number)
        $seatsByAuditorium = []; // [auditorium_id => [seat_ids...]]
        foreach ($auditoriums as $cinemaId => $audIds) {
            foreach ($audIds as $audId) {
                $seatsByAuditorium[$audId] = [];
                $rows = range('A', 'J'); // 10 hàng
                $cols = range(1, 12);    // 12 cột

                foreach ($rows as $rIdx => $row) {
                    foreach ($cols as $col) {
                        $seatTypeId = $seatTypeStandardId;
                        if (in_array($row, ['E','F'])) $seatTypeId = $seatTypeVipId;
                        if ($row === 'J' && $col <= 4) $seatTypeId = $seatTypeCoupleId;

                        $seatCode = sprintf("%s%02d", $row, $col); // A01, A02...

                        $seatsByAuditorium[$audId][] = DB::table('seats')->insertGetId([
                            'auditorium_id' => $audId,
                            'seat_type_id'  => $seatTypeId,
                            'seat_code'     => $seatCode,
                            'row_label'     => $row,
                            'col_number'    => $col,
                            'x'             => $col,
                            'y'             => $rIdx + 1,
                            'is_active'     => 1,
                        ]);
                    }
                }
            }
        }

        // 6) content_ratings (code,name,min_age,description)
        $ratingP   = DB::table('content_ratings')->insertGetId(['code' => 'P',   'name' => 'P',   'min_age' => null, 'description' => 'Phù hợp mọi lứa tuổi']);
        $ratingT13 = DB::table('content_ratings')->insertGetId(['code' => 'T13', 'name' => 'T13', 'min_age' => 13,   'description' => 'Từ 13 tuổi']);
        $ratingT16 = DB::table('content_ratings')->insertGetId(['code' => 'T16', 'name' => 'T16', 'min_age' => 16,   'description' => 'Từ 16 tuổi']);
        $ratingT18 = DB::table('content_ratings')->insertGetId(['code' => 'T18', 'name' => 'T18', 'min_age' => 18,   'description' => 'Từ 18 tuổi']);

        // 7) genres
        $genreIds = [];
        foreach (['Hành động','Hài','Tình cảm','Kinh dị','Hoạt hình','Phiêu lưu','Khoa học viễn tưởng'] as $i => $g) {
            $genreIds[] = DB::table('genres')->insertGetId([
                'code' => 'GEN'.str_pad((string)($i+1), 2, '0', STR_PAD_LEFT),
                'name' => $g,
            ]);
        }

        // 8) movies + movie_genres + movie_versions (movie_versions KHÔNG có public_id)
        $movieVersionIds = [];
        for ($i = 1; $i <= 8; $i++) {
            $movieId = DB::table('movies')->insertGetId([
                'public_id'         => (string) Str::ulid(),
                'content_rating_id' => [$ratingP,$ratingT13,$ratingT16,$ratingT18][array_rand([0,1,2,3])],
                'title'             => "Phim Demo {$i}",
                'original_title'    => "Demo Movie {$i}",
                'duration_minutes'  => random_int(85, 140),
                'release_date'      => now()->subDays(random_int(0, 180))->toDateString(),
                'language_original' => 'VI',
                'synopsis'          => $faker->paragraph(),
                'poster_url'        => null,
                'trailer_url'       => null,
                'censorship_license_no' => null,
                'status'            => 'ACTIVE',
            ]);

            // gán 1-2 thể loại
            $pick = array_rand($genreIds, random_int(1, 2));
            $pick = is_array($pick) ? $pick : [$pick];
            foreach ($pick as $idx) {
                DB::table('movie_genres')->insert([
                    'movie_id' => $movieId,
                    'genre_id' => $genreIds[$idx],
                ]);
            }

            // versions: 2D + optional 3D
            $mv2d = DB::table('movie_versions')->insertGetId([
                'movie_id'          => $movieId,
                'format'            => '2D',
                'audio_language'    => 'VI',
                'subtitle_language' => null,
                'notes'             => null,
            ]);
            $movieVersionIds[] = $mv2d;

            if (random_int(0, 1) === 1) {
                $mv3d = DB::table('movie_versions')->insertGetId([
                    'movie_id'          => $movieId,
                    'format'            => '3D',
                    'audio_language'    => 'VI',
                    'subtitle_language' => 'EN',
                    'notes'             => null,
                ]);
                $movieVersionIds[] = $mv3d;
            }
        }

        // 9) ticket_types + sales_channels
        $ticketAdultId   = DB::table('ticket_types')->insertGetId(['code' => 'ADULT',   'name' => 'Người lớn', 'description' => null]);
        $ticketStudentId = DB::table('ticket_types')->insertGetId(['code' => 'STUDENT', 'name' => 'HSSV',      'description' => null]);
        $ticketChildId   = DB::table('ticket_types')->insertGetId(['code' => 'CHILD',   'name' => 'Trẻ em',    'description' => null]);

        $channelWebId = DB::table('sales_channels')->insertGetId(['code' => 'WEB', 'name' => 'Website']);
        $channelPosId = DB::table('sales_channels')->insertGetId(['code' => 'POS', 'name' => 'Quầy vé']);

        // 10) shows (2 ngày tới)
        $shows = []; // each: ['id'=>, 'auditorium_id'=>, 'cinema_id'=>]
        $today = now()->startOfDay();
        $timeSlots = ['10:00', '13:00', '16:00', '19:00', '21:30'];

        foreach ($auditoriums as $cinemaId => $audIds) {
            foreach ($audIds as $audId) {
                for ($d = 0; $d < 2; $d++) {
                    $slots = (array) array_rand($timeSlots, 3);
                    foreach ($slots as $slotIdx) {
                        $start = $today->copy()->addDays($d)->setTimeFromTimeString($timeSlots[$slotIdx]);
                        $mvId = $movieVersionIds[array_rand($movieVersionIds)];

                        $movieId = DB::table('movie_versions')->where('id', $mvId)->value('movie_id');
                        $duration = (int) DB::table('movies')->where('id', $movieId)->value('duration_minutes');
                        $end = $start->copy()->addMinutes($duration + 15);

                        $showId = DB::table('shows')->insertGetId([
                            'public_id'        => (string) Str::ulid(),
                            'auditorium_id'    => $audId,
                            'movie_version_id' => $mvId,
                            'start_time'       => $start->format('Y-m-d H:i:s'),
                            'end_time'         => $end->format('Y-m-d H:i:s'),
                            'on_sale_from'     => $today->copy()->subDay()->format('Y-m-d H:i:s'),
                            'on_sale_until'    => $end->copy()->subHour()->format('Y-m-d H:i:s'),
                            'status'           => 'ON_SALE',
                            'created_by'       => null,
                        ]);

                        $shows[] = ['id' => $showId, 'auditorium_id' => $audId, 'cinema_id' => $cinemaId];
                    }
                }
            }
        }

        // 11) show_prices (show x seat_type x ticket_type)
        foreach ($shows as $s) {
            $base = random_int(70000, 120000);

            $matrix = [
                [$seatTypeStandardId, $ticketAdultId,   $base],
                [$seatTypeStandardId, $ticketStudentId, (int) round($base * 0.85)],
                [$seatTypeStandardId, $ticketChildId,   (int) round($base * 0.75)],

                [$seatTypeVipId,      $ticketAdultId,   $base + 30000],
                [$seatTypeVipId,      $ticketStudentId, (int) round(($base + 30000) * 0.85)],
                [$seatTypeVipId,      $ticketChildId,   (int) round(($base + 30000) * 0.75)],

                [$seatTypeCoupleId,   $ticketAdultId,   $base + 60000],
                [$seatTypeCoupleId,   $ticketStudentId, (int) round(($base + 60000) * 0.85)],
                [$seatTypeCoupleId,   $ticketChildId,   (int) round(($base + 60000) * 0.75)],
            ];

            foreach ($matrix as [$seatTypeId, $ticketTypeId, $price]) {
                DB::table('show_prices')->insert([
                    'show_id'        => $s['id'],
                    'seat_type_id'   => $seatTypeId,
                    'ticket_type_id' => $ticketTypeId,
                    'price_amount'   => (int) $price,
                    'currency'       => 'VND',
                    'is_active'      => 1,
                ]);
            }
        }

        // 12) customers (cột dob, city)
        $customerIds = [];
        for ($i = 1; $i <= 30; $i++) {
            $customerIds[] = DB::table('customers')->insertGetId([
                'public_id'  => (string) Str::ulid(),
                'full_name'  => $faker->name(),
                'phone'      => $faker->unique()->numerify('09########'),
                'email'      => $faker->unique()->safeEmail(),
                'dob'        => $faker->date('Y-m-d', '-15 years'),
                'gender'     => ['MALE','FEMALE','OTHER'][array_rand([0,1,2])],
                'city'       => $faker->city(),
            ]);
        }

        // 13) bookings + booking_tickets + tickets + payments
        $availableSeatsByShow = [];
        foreach ($shows as $s) {
            $availableSeatsByShow[$s['id']] = $seatsByAuditorium[$s['auditorium_id']];
            shuffle($availableSeatsByShow[$s['id']]);
        }

        for ($b = 1; $b <= 40; $b++) {
            $s = $shows[array_rand($shows)];
            $showId = $s['id'];

            if (count($availableSeatsByShow[$showId]) < 1) continue;

            $customerId = $customerIds[array_rand($customerIds)];
            $channelId  = (random_int(0, 1) === 1) ? $channelWebId : $channelPosId;

            $ticketCount = min(random_int(1, 5), count($availableSeatsByShow[$showId]));
            $pickedSeatIds = array_splice($availableSeatsByShow[$showId], 0, $ticketCount);

            $bookingCode = 'BK'.now()->format('ymd').str_pad((string)$b, 5, '0', STR_PAD_LEFT);

            $bookingId = DB::table('bookings')->insertGetId([
                'public_id'         => (string) Str::ulid(),
                'booking_code'      => $bookingCode,
                'show_id'           => $showId,
                'cinema_id'         => $s['cinema_id'],
                'customer_id'       => $customerId,
                'sales_channel_id'  => $channelId,
                'status'            => 'PAID',
                'contact_name'      => DB::table('customers')->where('id', $customerId)->value('full_name'),
                'contact_phone'     => DB::table('customers')->where('id', $customerId)->value('phone'),
                'contact_email'     => DB::table('customers')->where('id', $customerId)->value('email'),
                'subtotal_amount'   => 0,
                'discount_amount'   => 0,
                'total_amount'      => 0,
                'paid_amount'       => 0,
                'currency'          => 'VND',
                'notes'             => null,
                'expires_at'        => null,
            ]);

            $subtotal = 0;

            foreach ($pickedSeatIds as $seatId) {
                $seatTypeId = (int) DB::table('seats')->where('id', $seatId)->value('seat_type_id');
                $ticketTypeId = [$ticketAdultId, $ticketStudentId, $ticketChildId][array_rand([0,1,2])];

                $unit = (int) DB::table('show_prices')
                    ->where('show_id', $showId)
                    ->where('seat_type_id', $seatTypeId)
                    ->where('ticket_type_id', $ticketTypeId)
                    ->value('price_amount');

                $final = $unit;
                $subtotal += $final;

                $bookingTicketId = DB::table('booking_tickets')->insertGetId([
                    'booking_id'         => $bookingId,
                    'show_id'            => $showId,
                    'seat_id'            => $seatId,
                    'ticket_type_id'     => $ticketTypeId,
                    'seat_type_id'       => $seatTypeId,
                    'unit_price_amount'  => $unit,
                    'discount_amount'    => 0,
                    'final_price_amount' => $final,
                    'status'             => 'ISSUED',
                ]);

                DB::table('tickets')->insert([
                    'booking_ticket_id' => $bookingTicketId,
                    'ticket_code'       => 'T'.Str::upper(Str::random(10)).$bookingTicketId,
                    'qr_payload'        => null,
                    'status'            => 'ISSUED',
                    'issued_at'         => now()->format('Y-m-d H:i:s'),
                    'used_at'           => null,
                ]);
            }

            DB::table('bookings')->where('id', $bookingId)->update([
                'subtotal_amount' => $subtotal,
                'total_amount'    => $subtotal,
                'paid_amount'     => $subtotal,
            ]);

            DB::table('payments')->insert([
                'booking_id'       => $bookingId,
                'provider'         => (random_int(0, 1) === 1) ? 'VNPAY' : 'CASH',
                'method'           => (random_int(0, 1) === 1) ? 'EWALLET' : 'CASH',
                'status'           => 'CAPTURED',
                'amount'           => $subtotal,
                'currency'         => 'VND',
                'external_txn_ref' => (string) Str::ulid(),
                'request_payload'  => null,
                'response_payload' => null,
                'paid_at'          => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
