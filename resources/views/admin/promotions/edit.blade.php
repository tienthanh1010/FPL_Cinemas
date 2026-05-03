@extends('admin.layout')
@section('title', 'Sửa khuyến mãi')
@section('content')
<section class="page-header"><div><p class="eyebrow">Edit promotion</p><h2>{{ $promotion->name }}</h2></div></section>
<form method="POST" action="{{ route('admin.promotions.update', $promotion) }}">@csrf @method('PUT') @include('admin.promotions._form')</form>
@endsection
