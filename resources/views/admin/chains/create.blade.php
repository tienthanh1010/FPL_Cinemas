@extends('admin.layout')

@section('title', 'Thêm chuỗi rạp')

@section('content')
    <h2 class="h4 mb-3">Thêm chuỗi rạp</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.chains.store') }}">
                @csrf
                @include('admin.chains._form')
            </form>
        </div>
    </div>
@endsection
