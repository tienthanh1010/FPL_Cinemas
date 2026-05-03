@extends('admin.layout')

@section('title', 'Categories')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Categories</h1>
    <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">+ New</a>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0 align-middle">
        <thead>
        <tr>
          <th>ID</th>
          <th>Code</th>
          <th>Name</th>
          <th>Active</th>
          <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($categories as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td><code>{{ $c->code }}</code></td>
            <td>{{ $c->name }}</td>
            <td>
              @if($c->is_active)
                <span class="badge bg-success">Yes</span>
              @else
                <span class="badge bg-secondary">No</span>
              @endif
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.categories.edit', $c) }}">Edit</a>
              <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Xoá danh mục?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $categories->links() }}</div>
@endsection
