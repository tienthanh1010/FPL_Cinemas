@extends('admin.layout')

@section('title', 'Sửa phiên bản phim')

@section('content')
    <h2 class="h4 mb-3">Sửa phiên bản phim #{{ $movieVersion->id }}</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movie_versions.update', $movieVersion) }}">
                @csrf
                @method('PUT')
                @include('admin.movie_versions._form')
            </form>
        </div>
    </div>
@endsection
