@extends('admin.layout')

@section('title', 'Quản lý hoàn tiền')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Refund Center</p>
        <h2>Danh sách yêu cầu hoàn tiền</h2>
        <p>Tra cứu theo mã booking, mã giao dịch, external ref, số điện thoại hoặc email; đồng thời lọc theo trạng thái refund, trạng thái payment, phim và suất chiếu.</p>
    </div>
</section>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng yêu cầu</div><div class="metric-value">{{ number_format($summary['refunds']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Hoàn tiền thành công</div><div class="metric-value">{{ number_format($summary['success_amount']) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Đang chờ xử lý</div><div class="metric-value">{{ number_format($summary['pending_amount']) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Bị từ chối / lỗi</div><div class="metric-value">{{ number_format($summary['rejected_count']) }}</div></div></div>
</div>

<div class="card toolbar-card mb-3">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-4">
                <label class="form-label">Tìm kiếm</label>
                <input class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Booking / txn ref / external ref / SĐT / email / tên khách">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Trạng thái refund</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Trạng thái payment</label>
                <select class="form-select" name="payment_status">
                    <option value="">Tất cả</option>
                    @foreach($paymentStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['payment_status'] === $value)>{{ $label }}</option>
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
                <a href="{{ route('admin.refunds.index') }}" class="btn btn-light-soft flex-fill">Xoá lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Refund</th>
                    <th>Booking / khách hàng</th>
                    <th>Giao dịch gốc</th>
                    <th>Phim / suất</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($refunds as $refund)
                @php
                    $badgeClass = match($refund->status) {
                        'SUCCESS' => 'badge-soft-success',
                        'PENDING' => 'badge-soft-warning',
                        'FAILED', 'REJECTED', 'CANCELLED' => 'badge-soft-danger',
                        default => 'badge-soft-secondary',
                    };
                    $payment = $refund->payment;
                    $booking = $payment?->booking;
                @endphp
                <tr>
                    <td>
                        <div class="list-primary">{{ $refund->external_ref ?: ('RF-'.$refund->id) }}</div>
                        <div class="list-secondary">#{{ $refund->id }} · Tạo lúc {{ optional($refund->created_at)->format('d/m/Y H:i') }}</div>
                        <div class="list-secondary">{{ number_format($refund->amount) }}đ · {{ $refund->reason ?: 'Chưa ghi lý do' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $booking?->booking_code ?: 'Không có booking' }}</div>
                        <div>{{ $booking?->contact_name ?: ($booking?->customer?->full_name ?? 'Khách lẻ') }}</div>
                        <div class="list-secondary">{{ $booking?->contact_phone ?: ($booking?->contact_email ?: 'Chưa có liên hệ') }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $payment?->external_txn_ref ?: ('PAY-'.($payment?->id ?? 'N/A')) }}</div>
                        <div>{{ $payment?->provider ?: 'N/A' }} / {{ $payment?->method ?: 'N/A' }}</div>
                        <div class="list-secondary">Payment status: {{ $payment?->status ?: '—' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $booking?->show?->movieVersion?->movie?->title ?? 'Suất chiếu' }}</div>
                        <div class="list-secondary">{{ optional($booking?->show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }}</div>
                        <div class="list-secondary">{{ $booking?->show?->auditorium?->name ?: 'Chưa có phòng' }}</div>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusOptions[$refund->status] ?? $refund->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">Chưa có yêu cầu hoàn tiền nào khớp điều kiện lọc.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $refunds->links() }}</div>
</div>
@endsection
