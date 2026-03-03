@extends('admin.layout')

@section('title', 'Sửa phòng chiếu')

@section('content')
    <h2 class="h4 mb-3">Sửa phòng chiếu #{{ $auditorium->id }}</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.auditoriums.update', $auditorium) }}">
                @csrf
                @method('PUT')
                @include('admin.auditoriums._form')
            </form>
        </div>
    </div>
@endsection
