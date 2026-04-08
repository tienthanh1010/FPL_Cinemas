@extends('admin.layout')
@section('title', 'Thiết bị')
@section('content')
<section class="page-header"><div><p class="eyebrow">Equipment form</p><h2>{{ $equipment->exists ? 'Cập nhật thiết bị' : 'Thêm thiết bị' }}</h2></div></section>
<form method="POST" action="{{ $equipment->exists ? route('admin.equipment.update',$equipment) : route('admin.equipment.store') }}">@csrf @if($equipment->exists) @method('PUT') @endif @include('admin.equipment._form')</form>
@endsection
