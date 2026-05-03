@extends('admin.layout')

@section('title', 'Thêm suất chiếu')

@section('content')
    <h2 class="h4 mb-3">Thêm suất chiếu</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.shows.store') }}">
                @csrf
                @include('admin.shows._form')
            </form>
        </div>
    </div>
@endsection
