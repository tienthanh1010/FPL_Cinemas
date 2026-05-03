@extends('admin.layout')

@section('title', 'Tin tức & ưu đãi')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Marketing Content</p>
            <h2>Tin tức &amp; ưu đãi</h2>
            <p>Quản lý bài viết hiển thị ngoài giao diện người dùng từ một màn hình thống nhất.</p>
        </div>
        <div>
            <a href="{{ route('admin.content_posts.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Thêm nội dung</a>
        </div>
    </section>

    <div class="row g-3 mb-3">
        <div class="col-md-4 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body"><div class="metric-label">Tổng nội dung</div><div class="metric-value">{{ number_format($summary['total']) }}</div></div></div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body"><div class="metric-label">Đang hiển thị</div><div class="metric-value">{{ number_format($summary['published']) }}</div></div></div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body"><div class="metric-label">Nổi bật</div><div class="metric-value">{{ number_format($summary['featured']) }}</div></div></div>
        </div>
    </div>

    <div class="card toolbar-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.content_posts.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Tiêu đề, slug, nhãn nội dung...">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-1">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
                <div class="col-sm-6 col-lg-1">
                    <a href="{{ route('admin.content_posts.index') }}" class="btn btn-light-soft w-100">Xoá</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nội dung</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Lịch hiển thị</th>
                        <th>Nổi bật</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>
                                <div class="list-primary">{{ $post->title }}</div>
                                <div class="list-secondary">/{{ $post->slug }}</div>
                                <div class="list-secondary mt-1">{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 90) }}</div>
                            </td>
                            <td><span class="badge badge-soft-primary">{{ $typeOptions[$post->type] ?? $post->type }}</span></td>
                            <td><span class="badge {{ $post->status === 'PUBLISHED' ? 'badge-soft-success' : ($post->status === 'ARCHIVED' ? 'badge-soft-secondary' : 'badge-soft-warning') }}">{{ $statusOptions[$post->status] ?? $post->status }}</span></td>
                            <td>
                                <div class="list-primary">{{ optional($post->published_at)->format('d/m/Y H:i') ?: 'Chưa lên lịch' }}</div>
                                <div class="list-secondary">{{ $post->starts_at ? 'Từ ' . $post->starts_at->format('d/m/Y H:i') : 'Không giới hạn bắt đầu' }}</div>
                                <div class="list-secondary">{{ $post->ends_at ? 'Đến ' . $post->ends_at->format('d/m/Y H:i') : 'Không giới hạn kết thúc' }}</div>
                            </td>
                            <td>{{ $post->is_featured ? 'Có' : 'Không' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.content_posts.show', $post) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                    <a href="{{ route('admin.content_posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form method="POST" action="{{ route('admin.content_posts.destroy', $post) }}" onsubmit="return confirm('Xoá nội dung này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Chưa có tin tức hoặc ưu đãi nào. Hãy tạo nội dung đầu tiên.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $posts->links() }}</div>
    </div>
@endsection
