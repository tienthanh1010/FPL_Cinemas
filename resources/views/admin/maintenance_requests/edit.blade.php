@extends('admin.layout')
@section('title', 'Bảo trì')
@section('content')
<section class="page-header"><div><p class="eyebrow">Maintenance form</p><h2>{{ $maintenanceRequest->exists ? 'Cập nhật yêu cầu bảo trì' : 'Tạo yêu cầu bảo trì' }}</h2></div></section>
<form method="POST" action="{{ $maintenanceRequest->exists ? route('admin.maintenance_requests.update',$maintenanceRequest) : route('admin.maintenance_requests.store') }}">@csrf @if($maintenanceRequest->exists) @method('PUT') @endif @include('admin.maintenance_requests._form')</form>
@endsection
