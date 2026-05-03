@extends('admin.layout')
@section('title', 'Tạo khuyến mãi')
@section('content')
<section class="page-header"><div><p class="eyebrow">Create promotion</p><h2>Tạo khuyến mãi / voucher rule</h2></div></section>
<form method="POST" action="{{ route('admin.promotions.store') }}">@csrf @include('admin.promotions._form')</form>
@endsection
