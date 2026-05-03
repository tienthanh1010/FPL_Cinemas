@extends('admin.layout')
@section('title', 'Chi tiết ca làm')
@section('content')
<section class="page-header"><div><p class="eyebrow">Shift detail</p><h2>Ca ngày {{ optional($shift->shift_date)->format('d/m/Y') }}</h2><p>{{ $shift->start_time }} - {{ $shift->end_time }}</p></div><div class="d-flex gap-2"><a class="btn btn-primary" href="{{ route('admin.staff_shifts.edit',$shift) }}">Sửa</a><a class="btn btn-light-soft" href="{{ route('admin.staff_shifts.index') }}">Quay lại</a></div></section>
<div class="card"><div class="card-body"><div class="fw-semibold mb-2">Danh sách nhân sự</div>@forelse($shift->staff as $member)<div class="border-bottom py-2"><div>{{ $member->full_name }}</div><div class="list-secondary">{{ $member->staff_code }} · {{ $member->roles->pluck('name')->implode(', ') }}</div></div>@empty<div class="text-muted">Chưa phân công nhân sự.</div>@endforelse</div></div>
@endsection
