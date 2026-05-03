<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentPostController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(array_keys(ContentPost::typeOptions()))],
            'status' => ['nullable', Rule::in(array_keys(ContentPost::statusOptions()))],
        ]);

        $query = ContentPost::query()
            ->when(! empty($filters['q']), function ($postQuery) use ($filters) {
                $q = trim((string) $filters['q']);
                $postQuery->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%")
                        ->orWhere('badge_label', 'like', "%{$q}%");
                });
            })
            ->when(! empty($filters['type']), fn ($postQuery) => $postQuery->where('type', $filters['type']))
            ->when(! empty($filters['status']), fn ($postQuery) => $postQuery->where('status', $filters['status']));

        $summary = [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->where('status', 'PUBLISHED')->count(),
            'featured' => (clone $query)->where('is_featured', true)->count(),
        ];

        $posts = $query
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.content_posts.index', [
            'posts' => $posts,
            'summary' => $summary,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'typeOptions' => ContentPost::typeOptions(),
            'statusOptions' => ContentPost::statusOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.content_posts.create', [
            'contentPost' => new ContentPost([
                'type' => 'NEWS',
                'status' => 'DRAFT',
                'sort_order' => 0,
                'published_at' => now(),
            ]),
            'typeOptions' => ContentPost::typeOptions(),
            'statusOptions' => ContentPost::statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);
        $payload['slug'] = $this->uniqueSlug($payload['slug'] ?? '', $payload['title']);
        $payload['created_by'] = (int) $request->session()->get('admin_user_id');
        $payload['updated_by'] = (int) $request->session()->get('admin_user_id');

        $post = ContentPost::query()->create($payload);

        return redirect()->route('admin.content_posts.edit', $post)
            ->with('success', 'Đã tạo bài viết / ưu đãi mới.');
    }

    public function show(ContentPost $contentPost): View
    {
        return view('admin.content_posts.show', [
            'contentPost' => $contentPost,
            'typeOptions' => ContentPost::typeOptions(),
            'statusOptions' => ContentPost::statusOptions(),
        ]);
    }

    public function edit(ContentPost $contentPost): View
    {
        return view('admin.content_posts.edit', [
            'contentPost' => $contentPost,
            'typeOptions' => ContentPost::typeOptions(),
            'statusOptions' => ContentPost::statusOptions(),
        ]);
    }

    public function update(Request $request, ContentPost $contentPost): RedirectResponse
    {
        $payload = $this->validatedPayload($request, $contentPost);
        $payload['slug'] = $this->uniqueSlug($payload['slug'] ?? '', $payload['title'], $contentPost->id);
        $payload['updated_by'] = (int) $request->session()->get('admin_user_id');

        $contentPost->update($payload);

        return redirect()->route('admin.content_posts.edit', $contentPost)
            ->with('success', 'Đã cập nhật nội dung thành công.');
    }

    public function destroy(ContentPost $contentPost): RedirectResponse
    {
        $contentPost->delete();

        return redirect()->route('admin.content_posts.index')
            ->with('success', 'Đã xoá nội dung khỏi hệ thống.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?ContentPost $contentPost = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(ContentPost::typeOptions()))],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('content_posts', 'slug')->ignore($contentPost?->id),
            ],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:512'],
            'badge_label' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(ContentPost::statusOptions()))],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ]);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function uniqueSlug(string $customSlug, string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($customSlug ?: $title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'noi-dung';
        $slug = $baseSlug;
        $counter = 2;

        while (ContentPost::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
