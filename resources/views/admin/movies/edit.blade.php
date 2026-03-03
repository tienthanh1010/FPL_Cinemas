@extends('admin.layout')

@section('title', 'Sửa phim')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Sửa phim #{{ $movie->id }}</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movies.update', $movie) }}">
                @csrf
                @method('PUT')
                @include('admin.movies._form')
            </form>
        </div>
    </div>
@endsection
