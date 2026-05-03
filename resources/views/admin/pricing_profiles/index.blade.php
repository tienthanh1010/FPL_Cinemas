@extends('admin.layout')

@section('title', 'Giá Vé')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Pricing profiles</p>
        <h2>Giá Vé</h2>
        <p>Quản lý quy tắc giá theo ngày, giờ, loại ghế, đối tượng vé, cuối tuần và ngày đặc biệt.</p>
    </div>
    <a href="{{ route('admin.pricing_profiles.create') }}" class="btn btn-primary">Tạo giá vé</a>
</section>

<div class="card">
    <div class="card-body border-bottom">
        <form class="row g-2">
            <div class="col-md-6"><input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Tìm theo tên hoặc mã giá vé"></div>
            <div class="col-md-auto"><button class="btn btn-light-soft">Lọc</button></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Mã</th><th>Tên</th><th>Rạp</th><th>Số rule</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
            @forelse($profiles as $profile)
                <tr>
                    <td>{{ $profile->code }}</td>
                    <td>{{ $profile->name }}</td>
                    <td>{{ $profile->cinema?->name ?: 'Toàn hệ thống' }}</td>
                    <td>{{ $profile->rules_count }}</td>
                    <td>{!! $profile->is_active ? '<span class="badge badge-soft-success">Đang dùng</span>' : '<span class="badge badge-soft-danger">Tạm khoá</span>' !!}</td>
                    <td class="text-end d-flex gap-2 justify-content-end">
                        <a class="btn btn-sm btn-light-soft" href="{{ route('admin.pricing_profiles.show', $profile) }}">Xem</a>
                        <a class="btn btn-sm btn-light-soft" href="{{ route('admin.pricing_profiles.edit', $profile) }}">Sửa</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">Chưa có giá vé nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $profiles->links() }}</div>
@endsection
