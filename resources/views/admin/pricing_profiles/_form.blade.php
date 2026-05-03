@php $isEdit = $profile->exists; @endphp
<div class="section-card">
    <h3>Thông tin hồ sơ giá</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Mã hồ sơ</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $profile->code) }}" placeholder="Ví dụ: WEEKEND_IMAX">
        </div>
        <div class="col-md-5">
            <label class="form-label">Tên hồ sơ *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $profile->name) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Rạp</label>
            <select name="cinema_id" class="form-select">
                <option value="">Toàn hệ thống / 1 rạp dùng chung</option>
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
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-1">Danh sách quy tắc giá</h3>
            <p class="section-description mb-0">BASE = giá gốc, SURCHARGE = phụ thu, DISCOUNT = giảm giá. Có thể giới hạn theo thứ, khung giờ và khoảng ngày (lễ/tết/promo).</p>
        </div>
        <button class="btn btn-light-soft" type="button" id="add-rule-row">Thêm rule</button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="pricing-rules-table">
            <thead>
                <tr>
                    <th>Tên rule</th>
                    <th>Loại</th>
                    <th>Ngày áp dụng</th>
                    <th>Khung giờ</th>
                    <th>Ghế</th>
                    <th>Đối tượng vé</th>
                    <th>Kiểu giá</th>
                    <th>Giá / Điều chỉnh</th>
                    <th>Ưu tiên</th>
                    <th>Kích hoạt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($ruleRows as $index => $row)
                    @include('admin.pricing_profiles._rule_row', ['row' => $row, 'index' => $index, 'seatTypes' => $seatTypes, 'ticketTypes' => $ticketTypes, 'weekdays' => $weekdays])
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo hồ sơ giá' }}</button>
    <a href="{{ route('admin.pricing_profiles.index') }}" class="btn btn-light-soft">Quay lại</a>
</div>

<template id="rule-row-template">
    @include('admin.pricing_profiles._rule_row', ['row' => ['rule_name' => 'Rule mới', 'rule_type' => 'SURCHARGE', 'valid_from' => null, 'valid_to' => null, 'day_of_week' => null, 'start_time' => null, 'end_time' => null, 'seat_type_id' => 1, 'ticket_type_id' => 1, 'price_amount' => 0, 'price_mode' => 'AMOUNT_DELTA', 'adjustment_value' => 0, 'priority' => 100, 'is_active' => 1], 'index' => '__INDEX__', 'seatTypes' => $seatTypes, 'ticketTypes' => $ticketTypes, 'weekdays' => $weekdays])
</template>

@push('scripts')
<script>
(function(){
    const tableBody = document.querySelector('#pricing-rules-table tbody');
    const tpl = document.getElementById('rule-row-template');
    document.getElementById('add-rule-row')?.addEventListener('click', () => {
        const idx = tableBody.querySelectorAll('tr').length;
        tableBody.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__INDEX__', idx));
    });
    tableBody?.addEventListener('click', (e) => {
        if (e.target.closest('.remove-rule-row')) {
            e.target.closest('tr').remove();
        }
    });
})();
</script>
@endpush
