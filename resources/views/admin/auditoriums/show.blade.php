@extends('admin.layout')

@section('title', 'Chi tiết phòng chiếu')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Auditorium detail</p>
        <h2>{{ $auditorium->name }}</h2>
        <p>{{ $auditorium->auditorium_code }} · {{ $auditorium->screen_type }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.auditoriums.edit', $auditorium) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="fw-semibold">Tổng ghế</div><div class="display-6">{{ $seats->count() }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="fw-semibold">Seat map version</div><div class="display-6">{{ $auditorium->seat_map_version }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="fw-semibold">Hoạt động</div><div class="display-6">{{ $auditorium->is_active ? 'Có' : 'Không' }}</div></div></div></div>
</div>
<div class="card mb-4">
    <div class="card-header fw-semibold">Sơ đồ ghế</div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @foreach($seats as $seat)
                <span class="badge badge-soft-primary">{{ $seat->seat_code }} · {{ $seat->seat_type_name }}</span>
            @endforeach
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header fw-semibold">Suất chiếu gần đây</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Phim</th><th>Bắt đầu</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        @forelse($auditorium->shows as $show)
            <tr>
                <td>{{ $show->movieVersion?->movie?->title ?: '-' }}</td>
                <td>{{ optional($show->start_time)->format('d/m/Y H:i') }}</td>
                <td>{{ $show->status }}</td>
                <td class="text-end"><a href="{{ route('admin.shows.show', $show) }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="empty-state">Chưa có suất chiếu.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
