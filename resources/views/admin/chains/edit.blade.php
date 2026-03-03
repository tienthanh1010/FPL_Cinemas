@extends('admin.layout')

@section('title', 'Sửa chuỗi rạp')

@section('content')
    <h2 class="h4 mb-3">Sửa chuỗi rạp #{{ $chain->id }}</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.chains.update', $chain) }}">
                @csrf
                @method('PUT')
                @include('admin.chains._form')
            </form>
        </div>
    </div>
@endsection
