@php $isEdit = $profile->exists; @endphp
<div class="section-card">
    <h3>Thông tin giá vé</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Mã giá vé</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $profile->code) }}" placeholder="Ví dụ: GIA_VE_CHUAN">
        </div>
        <div class="col-md-5">
            <label class="form-label">Tên giá vé *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $profile->name ?: 'Giá Vé') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Rạp</label>
            <select name="cinema_id" class="form-select">
                <option value="">Dùng chung cho rạp</option>
                @foreach($cinemas as $cinema)
                    <option value="{{ $cinema->id }}" @selected((string) old('cinema_id', $profile->cinema_id) === (string) $cinema->id)>{{ $cinema->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-center">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $profile->exists ? $profile->is_active : true))>
                <label class="form-check-label" for="is_active">Đang hoạt động</label>
            </div>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="mb-3">
        <h3 class="mb-1">Giá vé cố định theo loại ghế</h3>
        <p class="section-description mb-0">Phòng Standard dùng trực tiếp các mức giá này. Mặc định: ghế thường 50.000đ, ghế VIP 70.000đ, ghế đôi 90.000đ.</p>
    </div>
    <div class="row g-3">
        @foreach($seatTypes as $seatType)
            <div class="col-md-4">
                <label class="form-label">{{ $seatType->name }}</label>
                <div class="input-group">
                    <input type="number" min="0" step="1000" name="base_prices[{{ $seatType->id }}]" class="form-control" value="{{ $baseSeatPrices[$seatType->id] ?? 0 }}" required>
                    <span class="input-group-text">đ</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="section-card">
    <div class="mb-3">
        <h3 class="mb-1">Điều chỉnh giá theo phòng</h3>
        <p class="section-description mb-0">Nhập số tiền tăng hoặc giảm theo từng định dạng phòng. Ví dụ IMAX tăng 10.000đ thì mọi loại ghế trong phòng IMAX sẽ cộng thêm 10.000đ.</p>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Phòng / định dạng</th>
                    <th style="width: 220px;">Tăng / giảm</th>
                    <th style="width: 260px;">Số tiền điều chỉnh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomTypes as $screenType => $label)
                    @php
                        $adjustment = $roomAdjustments[$screenType] ?? ['mode' => 'NONE', 'amount' => 0];
                        $isStandard = strtoupper($screenType) === 'STANDARD';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $label }}</strong>
                            @if($isStandard)
                                <div class="small text-muted">Phòng chuẩn, không cộng/trừ thêm.</div>
                            @endif
                        </td>
                        <td>
                            <select class="form-select" name="room_adjustments[{{ $screenType }}][mode]" @disabled($isStandard)>
                                <option value="NONE" @selected(($adjustment['mode'] ?? 'NONE') === 'NONE')>Không đổi</option>
                                <option value="SURCHARGE" @selected(($adjustment['mode'] ?? 'NONE') === 'SURCHARGE')>Tăng</option>
                                <option value="DISCOUNT" @selected(($adjustment['mode'] ?? 'NONE') === 'DISCOUNT')>Giảm</option>
                            </select>
                            @if($isStandard)
                                <input type="hidden" name="room_adjustments[{{ $screenType }}][mode]" value="NONE">
                            @endif
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="number" min="0" step="1000" class="form-control" name="room_adjustments[{{ $screenType }}][amount]" value="{{ $isStandard ? 0 : (int) ($adjustment['amount'] ?? 0) }}" @readonly($isStandard)>
                                <span class="input-group-text">đ</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="section-card">
    <div class="mb-3">
        <h3 class="mb-1">Phụ thu cuối tuần</h3>
        <p class="section-description mb-0">Nếu suất chiếu rơi vào Thứ 7 hoặc Chủ nhật, hệ thống sẽ tự động cộng thêm số tiền này vào giá từng ghế.</p>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Số tiền tăng thêm Thứ 7 / Chủ nhật</label>
            <div class="input-group">
                <input type="number" min="0" step="1000" name="weekend_surcharge_amount" class="form-control" value="{{ (int) ($weekendSurchargeAmount ?? 0) }}" placeholder="Ví dụ: 10000">
                <span class="input-group-text">đ</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo giá vé' }}</button>
    <a href="{{ route('admin.pricing_profiles.index') }}" class="btn btn-light-soft">Quay lại</a>
</div>
