@extends('admin.layout')

@section('title', 'Sửa rạp')

@section('content')
    <h2 class="h4 mb-3">Sửa rạp #{{ $cinema->id }}</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.cinemas.update', $cinema) }}">
                @csrf
                @method('PUT')
                @include('admin.cinemas._form')
            </form>
        </div>
    </div>
@endsection
