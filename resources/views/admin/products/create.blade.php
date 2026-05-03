@extends('admin.layout')
@section('title', 'Thêm sản phẩm F&B')
@section('content')
<section class="page-header"><div><p class="eyebrow">Create product</p><h2>Thêm sản phẩm / combo</h2></div></section>
<form method="POST" action="{{ route('admin.products.store') }}">@csrf @include('admin.products._form')</form>
@endsection
