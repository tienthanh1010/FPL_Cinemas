@extends('admin.layout')
@section('title', 'Tạo hồ sơ giá')
@section('content')
<section class="page-header"><div><p class="eyebrow">Create pricing profile</p><h2>Tạo hồ sơ giá động</h2></div></section>
<form method="POST" action="{{ route('admin.pricing_profiles.store') }}">@csrf @include('admin.pricing_profiles._form')</form>
@endsection
