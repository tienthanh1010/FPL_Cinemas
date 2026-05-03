@extends('admin.layout')
@section('title', 'Sửa sản phẩm F&B')
@section('content')
<section class="page-header"><div><p class="eyebrow">Edit product</p><h2>{{ $product->name }}</h2></div></section>
<form method="POST" action="{{ route('admin.products.update', $product) }}">@csrf @method('PUT') @include('admin.products._form')</form>
@endsection
