<tr>
    <td>
        <input type="text" class="form-control form-control-sm" name="rules[{{ $index }}][rule_name]" value="{{ $row['rule_name'] ?? '' }}">
        <div class="d-flex gap-1 mt-1">
            <input type="date" class="form-control form-control-sm" name="rules[{{ $index }}][valid_from]" value="{{ $row['valid_from'] ?? '' }}" title="Từ ngày">
            <input type="date" class="form-control form-control-sm" name="rules[{{ $index }}][valid_to]" value="{{ $row['valid_to'] ?? '' }}" title="Đến ngày">
        </div>
    </td>
    <td>
        <select class="form-select form-select-sm" name="rules[{{ $index }}][rule_type]">
            @foreach(['BASE' => 'Giá gốc', 'SURCHARGE' => 'Phụ thu', 'DISCOUNT' => 'Giảm giá'] as $value => $label)
                <option value="{{ $value }}" @selected(($row['rule_type'] ?? 'BASE') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="form-select form-select-sm" name="rules[{{ $index }}][day_of_week]">
            <option value="">Mọi ngày</option>
            @foreach($weekdays as $value => $label)
                <option value="{{ $value }}" @selected((string) ($row['day_of_week'] ?? '') === (string) $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <div class="d-flex gap-1">
            <input type="time" step="1" class="form-control form-control-sm" name="rules[{{ $index }}][start_time]" value="{{ $row['start_time'] ?? '' }}">
            <input type="time" step="1" class="form-control form-control-sm" name="rules[{{ $index }}][end_time]" value="{{ $row['end_time'] ?? '' }}">
        </div>
    </td>
    <td>
        <select class="form-select form-select-sm" name="rules[{{ $index }}][seat_type_id]">
            @foreach($seatTypes as $seatType)
                <option value="{{ $seatType->id }}" @selected((string) ($row['seat_type_id'] ?? '') === (string) $seatType->id)>{{ $seatType->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="form-select form-select-sm" name="rules[{{ $index }}][ticket_type_id]">
            @foreach($ticketTypes as $ticketType)
                <option value="{{ $ticketType->id }}" @selected((string) ($row['ticket_type_id'] ?? '') === (string) $ticketType->id)>{{ $ticketType->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="form-select form-select-sm" name="rules[{{ $index }}][price_mode]">
            @foreach(['FIXED' => 'Giá cố định', 'AMOUNT_DELTA' => '+/- số tiền', 'PERCENT_DELTA' => '+/- %'] as $value => $label)
                <option value="{{ $value }}" @selected(($row['price_mode'] ?? 'FIXED') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" class="form-control form-control-sm mb-1" name="rules[{{ $index }}][price_amount]" value="{{ $row['price_amount'] ?? 0 }}" placeholder="Giá cố định">
        <input type="number" class="form-control form-control-sm" name="rules[{{ $index }}][adjustment_value]" value="{{ $row['adjustment_value'] ?? '' }}" placeholder="Điều chỉnh">
    </td>
    <td><input type="number" class="form-control form-control-sm" name="rules[{{ $index }}][priority]" value="{{ $row['priority'] ?? 100 }}"></td>
    <td class="text-center"><input type="checkbox" class="form-check-input" name="rules[{{ $index }}][is_active]" value="1" @checked(!empty($row['is_active']))></td>
    <td><button class="btn btn-sm btn-outline-danger remove-rule-row" type="button">X</button></td>
</tr>
