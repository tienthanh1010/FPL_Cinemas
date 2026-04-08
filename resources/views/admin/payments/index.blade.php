@extends('admin.layout')

@section('title', 'Quản lý thanh toán')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Payment Center</p>
        <h2>Danh sách giao dịch thanh toán</h2>
        <p>Tra cứu theo mã booking, mã giao dịch, số điện thoại, email; đồng thời lọc theo provider, phương thức, phim, suất chiếu và trạng thái.</p>
    </div>
</section>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng giao dịch</div><div class="metric-value">{{ number_format($summary['payments']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Đã thu tiền</div><div class="metric-value">{{ number_format($summary['captured_amount']) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Hoàn tiền thành công</div><div class="metric-value">{{ number_format($summary['refund_amount']) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Giao dịch lỗi</div><div class="metric-value">{{ number_format($summary['failed_count']) }}</div></div></div>
</div>

<div class="card toolbar-card mb-3">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-4">
                <label class="form-label">Tìm kiếm</label>
                <input class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Mã booking / mã GD / SĐT / email / tên khách">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Provider</label>
                <select class="form-select" name="provider">
                    <option value="">Tất cả</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider }}" @selected($filters['provider'] === $provider)>{{ $provider }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Phương thức</label>
                <select class="form-select" name="method">
                    <option value="">Tất cả</option>
                    @foreach($methods as $method)
                        <option value="{{ $method }}" @selected($filters['method'] === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Phim</label>
                <select class="form-select" name="movie_id">
                    <option value="">Tất cả phim</option>
                    @foreach($movies as $movie)
                        <option value="{{ $movie->id }}" @selected((int) $filters['movie_id'] === (int) $movie->id)>{{ $movie->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label class="form-label">Suất chiếu</label>
                <select class="form-select" name="show_id">
                    <option value="">Tất cả suất</option>
                    @foreach($shows as $show)
                        <option value="{{ $show->id }}" @selected((int) $filters['show_id'] === (int) $show->id)>
                            {{ $show->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($show->start_time)->format('d/m/Y H:i') }} · {{ $show->auditorium?->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Từ ngày tạo</label>
                <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Đến ngày tạo</label>
                <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Tìm & lọc</button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-light-soft flex-fill">Xoá lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Giao dịch</th>
                    <th>Booking / khách hàng</th>
                    <th>Phim / suất</th>
                    <th>Provider / method</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($payments as $payment)
                @php
                    $badgeClass = match($payment->status) {
                        'CAPTURED' => 'badge-soft-success',
                        'REFUNDED' => 'badge-soft-primary',
                        'FAILED', 'CANCELLED' => 'badge-soft-danger',
                        'AUTHORIZED' => 'badge-soft-info',
                        default => 'badge-soft-warning',
                    };
                    $refundAmount = (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount');
                @endphp
                <tr>
                    <td>
                        <div class="list-primary">{{ $payment->external_txn_ref ?: ('PAY-'.$payment->id) }}</div>
                        <div class="list-secondary">#{{ $payment->id }} · Tạo lúc {{ optional($payment->created_at)->format('d/m/Y H:i') }}</div>
                        <div class="list-secondary">Thanh toán: {{ optional($payment->paid_at)->format('d/m/Y H:i') ?: 'Chưa ghi nhận' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $payment->booking?->booking_code ?: 'Không có booking' }}</div>
                        <div>{{ $payment->booking?->contact_name ?: ($payment->booking?->customer?->full_name ?? 'Khách lẻ') }}</div>
                        <div class="list-secondary">{{ $payment->booking?->contact_phone ?: 'Chưa có SĐT' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $payment->booking?->show?->movieVersion?->movie?->title ?? 'Suất chiếu' }}</div>
                        <div class="list-secondary">{{ optional($payment->booking?->show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }}</div>
                        <div class="list-secondary">{{ $payment->booking?->show?->auditorium?->name ?: 'Chưa có phòng' }}</div>
                    </td>
                    <td>
                        <div>{{ $payment->provider ?: 'N/A' }}</div>
                        <div class="list-secondary">{{ $payment->method ?: 'N/A' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ number_format($payment->amount) }}{{ $payment->currency }}</div>
                        <div class="list-secondary">Refund thành công: {{ number_format($refundAmount) }}đ</div>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusOptions[$payment->status] ?? $payment->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">Chưa có giao dịch nào khớp điều kiện lọc.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $payments->links() }}</div>
</div>
@endsection
