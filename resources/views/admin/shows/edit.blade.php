@extends('admin.layout')

@section('title', 'Sửa suất chiếu')

@section('content')
    <h2 class="h4 mb-3">Sửa suất chiếu #{{ $show->id }}</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.shows.update', $show) }}">
                @csrf
                @method('PUT')
                @include('admin.shows._form')
            </form>
        </div>
    </div>
@endsection
