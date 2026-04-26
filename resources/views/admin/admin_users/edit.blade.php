@extends('admin.layout')
@section('title', 'Cập nhật tài khoản admin')
@section('content')
<section class="page-header"><div><p class="eyebrow">Admin form</p><h2>Cập nhật tài khoản admin</h2></div></section>
<form method="POST" action="{{ route('admin.admin_users.update', $adminUserModel) }}">@csrf @method('PUT') @include('admin.admin_users._form')</form>
@endsection
