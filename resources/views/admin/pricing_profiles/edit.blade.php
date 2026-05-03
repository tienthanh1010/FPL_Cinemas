@extends('admin.layout')
@section('title', 'Sửa giá vé')
@section('content')
<section class="page-header"><div><p class="eyebrow">Edit pricing profile</p><h2>{{ $profile->name }}</h2></div></section>
<form method="POST" action="{{ route('admin.pricing_profiles.update', $profile) }}">@csrf @method('PUT') @include('admin.pricing_profiles._form')</form>
@endsection
