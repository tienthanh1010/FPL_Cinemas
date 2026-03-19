@extends('admin.layout')
@section('title', 'Tạo voucher')
@section('content')
<section class="page-header"><div><p class="eyebrow">Create voucher</p><h2>Tạo mã voucher</h2></div></section>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('admin.coupons.store') }}" class="row g-3">@csrf
<div class="col-md-6"><label class="form-label">Khuyến mãi</label><select class="form-select" name="promotion_id">@foreach($promotions as $promotion)<option value="{{ $promotion->id }}" @selected(request('promotion_id')==$promotion->id)>{{ $promotion->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Mã voucher (để trống sẽ tự sinh)</label><input class="form-control" name="code" value="{{ old('code') }}"></div>
<div class="col-md-6"><label class="form-label">Hạn dùng</label><input type="datetime-local" class="form-control" name="expires_at" value="{{ old('expires_at') }}"></div>
<div class="col-md-6"><label class="form-label">Trạng thái</label><select class="form-select" name="status"><option value="ISSUED">ISSUED</option><option value="ACTIVE">ACTIVE</option></select></div>
<div class="col-12 d-flex gap-2"><button class="btn btn-primary">Tạo voucher</button><a href="{{ route('admin.coupons.index') }}" class="btn btn-light-soft">Quay lại</a></div>
</form></div></div>
@endsection
