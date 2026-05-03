@extends('admin.layout')
@section('title', 'Sửa nhà cung cấp')
@section('content')
<section class="page-header"><div><p class="eyebrow">Edit supplier</p><h2>{{ $supplier->name }}</h2></div></section>
<form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}">@csrf @method('PUT') @include('admin.suppliers._form')</form>
@endsection
