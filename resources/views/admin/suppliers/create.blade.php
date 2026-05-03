@extends('admin.layout')
@section('title', 'Thêm nhà cung cấp')
@section('content')
<section class="page-header"><div><p class="eyebrow">Create supplier</p><h2>Thêm nhà cung cấp F&B</h2></div></section>
<form method="POST" action="{{ route('admin.suppliers.store') }}">@csrf @include('admin.suppliers._form')</form>
@endsection
