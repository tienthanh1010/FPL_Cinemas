<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $categories = Category::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->withCount('movies')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'q'));
    }

    public function create(): View
    {
        $category = new Category();

        return view('admin.categories.create', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $category = Category::create($data);

        return redirect()->route('admin.categories.show', $category)->with('success', 'Đã tạo thể loại phim.');
    }

    public function show(Category $category): View
    {
        $category->load(['movies' => fn ($q) => $q->orderByDesc('release_date')]);

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateData($request, $category);

        $category->update($data);

        return redirect()->route('admin.categories.show', $category)->with('success', 'Đã cập nhật thể loại phim.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->movies()->detach();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Đã xoá thể loại phim.');
    }

    private function validateData(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:32', $category ? Rule::unique('genres', 'code')->ignore($category->id) : Rule::unique('genres', 'code')],
            'name' => ['required', 'string', 'max:255'],
        ]);
    }
}
