@extends('admin.layout')

@section('title', 'Thêm rạp')

@section('content')
    <h2 class="h4 mb-3">Thêm rạp</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.cinemas.store') }}">
                @csrf
                @include('admin.cinemas._form')
            </form>
        </div>
    </div>
@endsection
