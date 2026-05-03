@extends('admin.layout')
@section('title', 'Thêm tài khoản admin')
@section('content')
<section class="page-header"><div><p class="eyebrow">Admin form</p><h2>Tạo tài khoản admin</h2></div></section>
<form method="POST" action="{{ route('admin.admin_users.store') }}">@csrf @include('admin.admin_users._form')</form>
@endsection
