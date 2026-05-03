@extends('admin.layout')
@section('title', 'Ca làm')
@section('content')
<section class="page-header"><div><p class="eyebrow">Shift form</p><h2>{{ $shift->exists ? 'Cập nhật ca làm' : 'Tạo ca làm' }}</h2></div></section>
<form method="POST" action="{{ $shift->exists ? route('admin.staff_shifts.update',$shift) : route('admin.staff_shifts.store') }}">@csrf @if($shift->exists) @method('PUT') @endif @include('admin.staff_shifts._form')</form>
@endsection
