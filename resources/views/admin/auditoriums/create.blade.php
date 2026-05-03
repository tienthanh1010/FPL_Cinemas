@extends('admin.layout')

@section('title', 'Thêm phòng chiếu')

@section('content')
    <h2 class="h4 mb-3">Thêm phòng chiếu</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.auditoriums.store') }}">
                @csrf
                @include('admin.auditoriums._form')
            </form>
        </div>
    </div>
@endsection
