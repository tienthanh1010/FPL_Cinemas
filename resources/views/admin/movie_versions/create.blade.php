@extends('admin.layout')

@section('title', 'Thêm phiên bản phim')

@section('content')
    <h2 class="h4 mb-3">Thêm phiên bản phim</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movie_versions.store') }}">
                @csrf
                @include('admin.movie_versions._form')
            </form>
        </div>
    </div>
@endsection
