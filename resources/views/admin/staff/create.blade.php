@extends('admin.layout')
@section('title', 'Nhân sự')
@section('content')
<section class="page-header"><div><p class="eyebrow">Staff form</p><h2>{{ $staff->exists ? 'Cập nhật nhân sự' : 'Thêm nhân sự' }}</h2></div></section>
<form method="POST" action="{{ $staff->exists ? route('admin.staff.update',$staff) : route('admin.staff.store') }}">@csrf @if($staff->exists) @method('PUT') @endif @include('admin.staff._form')</form>
@endsection
