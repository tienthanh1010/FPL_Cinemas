@extends('admin.layout')
@section('title', 'Khách hàng')
@section('content')
<section class="page-header"><div><p class="eyebrow">Customer form</p><h2>{{ $customer->exists ? 'Cập nhật khách hàng' : 'Thêm khách hàng' }}</h2></div></section>
<form method="POST" action="{{ $customer->exists ? route('admin.customers.update',$customer) : route('admin.customers.store') }}">@csrf @if($customer->exists) @method('PUT') @endif @include('admin.customers._form')</form>
@endsection
