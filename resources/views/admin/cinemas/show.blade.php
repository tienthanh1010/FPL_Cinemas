@extends('admin.layout')

<<<<<<< HEAD
@section('title', 'Thông tin rạp')
=======
@section('title', 'Chi tiết rạp')
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

@section('content')
<section class="page-header">
    <div>
<<<<<<< HEAD
        <p class="eyebrow">Single cinema settings</p>
        <h2>{{ $cinema->name }}</h2>
        <p>Website hiện được khóa theo mô hình một rạp tuyệt đối. Mọi lịch chiếu, booking, thanh toán và vận hành đều quy về {{ $cinema->name }}.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.cinemas.edit', $cinema) }}" class="btn btn-primary">Cập nhật thông tin rạp</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-light-soft">Về tổng quan</a>
=======
        <p class="eyebrow">Cinema detail</p>
        <h2>{{ $cinema->name }}</h2>
        <p>{{ $cinema->cinema_code }} · {{ $cinema->timezone }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.cinemas.edit', $cinema) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft">Quay lại</a>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    </div>
</section>
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card h-100"><div class="card-body">
        <div class="fw-semibold mb-2">Thông tin liên hệ</div>
        <div>Tên rạp: {{ $cinema->name }}</div>
        <div>Điện thoại: {{ $cinema->phone ?: '—' }}</div>
        <div>Email: {{ $cinema->email ?: '—' }}</div>
        <div>Địa chỉ: {{ collect([$cinema->address_line, $cinema->ward, $cinema->district, $cinema->province])->filter()->implode(', ') ?: '—' }}</div>
    </div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-body">
        <div class="fw-semibold mb-2">Tổng quan</div>
        <div>Trạng thái: {{ $cinema->status }}</div>
        <div>Số phòng chiếu: {{ $cinema->auditoriums->count() }}</div>
<<<<<<< HEAD
        <div>Timezone: {{ $cinema->timezone }}</div>
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        <div>Mã quốc gia: {{ $cinema->country_code }}</div>
    </div></div></div>
</div>
<div class="card">
    <div class="card-header fw-semibold">Danh sách phòng chiếu</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Phòng</th><th>Loại màn</th><th>Hoạt động</th><th></th></tr></thead>
        <tbody>
        @forelse($cinema->auditoriums as $auditorium)
            <tr>
                <td>{{ $auditorium->name }} ({{ $auditorium->auditorium_code }})</td>
<<<<<<< HEAD
                <td>{{ strtoupper($auditorium->screen_type) }}</td>
=======
                <td>{{ $auditorium->screen_type }}</td>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                <td>{{ $auditorium->is_active ? 'Có' : 'Không' }}</td>
                <td class="text-end"><a href="{{ route('admin.auditoriums.show', $auditorium) }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="empty-state">Chưa có phòng chiếu.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
