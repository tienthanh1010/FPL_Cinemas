@extends('admin.layout')

@section('title', 'Edit Category')

@section('content')
  <h1 class="h4 mb-3">Edit Category #{{ $category->id }}</h1>

  <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card p-3">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Code</label>
      <input class="form-control" name="code" value="{{ old('code', $category->code) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Update</button>
      <a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Back</a>
    </div>
  </form>
@endsection
