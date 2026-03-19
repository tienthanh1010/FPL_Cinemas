@extends('admin.layout')
@section('title', 'Chi tiết hồ sơ giá')
@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Pricing profile detail</p>
        <h2>{{ $profile->name }}</h2>
        <p>{{ $profile->code }} · {{ $profile->cinema?->name ?: 'Toàn hệ thống' }}</p>
    </div>
    <div class="d-flex gap-2"><a href="{{ route('admin.pricing_profiles.edit', $profile) }}" class="btn btn-primary">Sửa</a><a href="{{ route('admin.pricing_profiles.index') }}" class="btn btn-light-soft">Quay lại</a></div>
</section>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>Tên rule</th><th>Loại</th><th>Ngày</th><th>Khung giờ</th><th>Ghế</th><th>Vé</th><th>Kiểu giá</th><th>Giá</th><th>Điều chỉnh</th><th>Ưu tiên</th></tr></thead>
    <tbody>
    @forelse($profile->rules as $rule)
        <tr>
            <td>{{ $rule->rule_name ?: '—' }}</td>
            <td>{{ $rule->rule_type }}</td>
            <td>
                {{ $rule->day_of_week !== null ? ($weekdays[$rule->day_of_week] ?? '—') : 'Mọi ngày' }}
                @if($rule->valid_from || $rule->valid_to)
                    <div class="small text-muted">{{ optional($rule->valid_from)->format('d/m/Y') ?: '...' }} - {{ optional($rule->valid_to)->format('d/m/Y') ?: '...' }}</div>
                @endif
            </td>
            <td>{{ $rule->start_time ?: '00:00' }} - {{ $rule->end_time ?: '23:59' }}</td>
            <td>{{ $rule->seatType?->name }}</td>
            <td>{{ $rule->ticketType?->name }}</td>
            <td>{{ $rule->price_mode }}</td>
            <td>{{ number_format($rule->price_amount) }}</td>
            <td>{{ $rule->adjustment_value !== null ? number_format($rule->adjustment_value) : '—' }}</td>
            <td>{{ $rule->priority }}</td>
        </tr>
    @empty
        <tr><td colspan="10" class="empty-state">Chưa có rule nào.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
