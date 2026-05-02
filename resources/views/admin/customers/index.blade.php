@extends('admin.layout')
@section('title', 'Khách hàng')
@section('content')
<section class="page-header"><div><p class="eyebrow">Customer management</p><h2>Khách hàng</h2><p>Tra cứu theo SĐT, email, mã booking và theo dõi lịch sử đặt vé/hoàn tiền/điểm thành viên.</p></div><div class="d-flex gap-2"><a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Thêm khách hàng</a></div></section>
<div class="row g-3 mb-3">
@foreach([['Tổng khách',$report['customers'],'bi-people'],['Lượt đặt vé',$report['booking_count'],'bi-ticket-perforated'],['Hoàn tiền',$report['refund_count'],'bi-arrow-counterclockwise'],['Điểm hiện có',number_format($report['points']),'bi-gem']] as [$label,$value,$icon])
<div class="col-md-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="list-secondary">{{ $label }}</div><div class="d-flex justify-content-between align-items-center mt-2"><div class="h3 mb-0">{{ $value }}</div><i class="bi {{ $icon }} text-primary fs-4"></i></div></div></div></div>
@endforeach
</div>
<div class="card toolbar-card"><div class="card-body"><form class="row g-3 align-items-end" method="GET"><div class="col-lg-8"><label class="form-label">Tìm kiếm</label><input class="form-control" name="q" value="{{ $q }}" placeholder="Tên, số điện thoại, email, mã booking..."></div><div class="col-lg-2"><button class="btn btn-primary w-100">Tìm</button></div><div class="col-lg-2"><a href="{{ route('admin.customers.index') }}" class="btn btn-light-soft w-100">Xoá lọc</a></div></form></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Khách hàng</th><th>Liên hệ</th><th>Booking</th><th>Điểm thành viên</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
@forelse($customers as $customer)
<tr><td><div class="list-primary">{{ $customer->full_name }}</div><div class="list-secondary">{{ optional($customer->dob)->format('d/m/Y') ?: 'Chưa có ngày sinh' }} · {{ $customer->gender ?: 'Chưa rõ' }}</div></td><td><div>{{ $customer->phone ?: 'Chưa có SĐT' }}</div><div class="list-secondary">{{ $customer->email ?: 'Chưa có email' }}</div><div class="list-secondary">{{ $customer->city ?: 'Chưa có thành phố' }}</div></td><td><div class="list-primary">{{ $customer->bookings_count }} đơn</div><div class="list-secondary">{{ optional($customer->bookings()->latest('id')->first())->booking_code ?: 'Chưa có' }}</div></td><td><div class="list-primary">{{ number_format($customer->loyaltyAccount?->points_balance ?? 0) }} điểm</div><div class="list-secondary">Lifetime {{ number_format($customer->loyaltyAccount?->lifetime_points ?? 0) }}</div></td><td><span class="badge {{ ($customer->account_status ?? 'ACTIVE') === 'ACTIVE' ? 'badge-soft-success' : 'badge-soft-warning' }}">{{ $customer->account_status ?? 'ACTIVE' }}</span></td><td class="text-end"><div class="d-inline-flex gap-2"><a href="{{ route('admin.customers.show',$customer) }}" class="btn btn-sm btn-outline-secondary">Xem</a><a href="{{ route('admin.customers.edit',$customer) }}" class="btn btn-sm btn-outline-primary">Sửa</a><form method="POST" action="{{ route('admin.customers.destroy',$customer) }}" onsubmit="return confirm('Xoá khách hàng này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xoá</button></form></div></td></tr>
@empty
<tr><td colspan="6" class="empty-state">Chưa có khách hàng nào.</td></tr>
@endforelse
</tbody></table></div><div class="card-body border-top">{{ $customers->links() }}</div></div>
@endsection
