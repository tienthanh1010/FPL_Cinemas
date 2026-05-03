@extends('admin.layout')

@section('title', 'Thêm phim')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Thêm phim</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movies.store') }}">
                @csrf
                @include('admin.movies._form')
            </form>
        </div>
    </div>
@endsection
