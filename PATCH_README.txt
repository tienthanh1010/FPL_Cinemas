PATCH UPDATE - SO DO GHE TRUC QUAN + GIA DONG
===========================================

1) Copy code de len project Laravel hien tai.

2) Chay database update theo 1 trong 2 cach:
   - Cach A: php artisan migrate
   - Cach B: import file database/sql/dynamic_pricing_and_seatmap_patch.sql vao MySQL

3) Neu chua co ho so gia, vao Admin -> Ho so gia dong:
   - tao 1 ho so gia
   - them cac rule BASE / SURCHARGE / DISCOUNT
   - co the cau hinh theo:
     + ngay trong tuan
     + khung gio
     + loai ghe
     + doi tuong ve
     + ngay le / khoang ngay dac biet
     + khuyen mai tu dong theo rule

4) Vao Admin -> Suat chieu:
   - chon Ho so gia dong cho tung suat
   - gia show_prices se duoc sinh tu dong
   - trang chi tiet suat co so do ghe truc quan
   - co the khoa / mo khoa ghe thu cong
   - hien thi ghe hold / booked / blocked / maintenance

5) Frontend dat ve:
   - chon doi tuong ve
   - chi cho chon ghe trong
   - ghe bi khoa / bao tri se bi disable

GHI CHU:
- Seat block su dung bang seat_blocks co san, khoa theo khoang thoi gian cua suat chieu.
- Ghe is_active = 0 duoc hien nhu ghe hong / bao tri.
- Neu du lieu cu chua co pricing_profile_id, patch SQL se gan tam theo profile dau tien.
