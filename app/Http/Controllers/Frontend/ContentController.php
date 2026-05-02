<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContentPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function news(): View
    {
        return $this->indexByType('NEWS', 'Tin tức', 'news.index');
    }

    public function offers(): View
    {
        return $this->indexByType('OFFER', 'Ưu đãi', 'offers.index');
    }

    public function showNews(ContentPost $contentPost): View
    {
        return $this->showPost($contentPost, 'NEWS', 'Tin tức');
    }

    public function showOffer(ContentPost $contentPost): View
    {
        return $this->showPost($contentPost, 'OFFER', 'Ưu đãi');
    }

    private function indexByType(string $type, string $heading, string $routeName): View
    {
        /** @var LengthAwarePaginator $posts */
        $posts = ContentPost::query()
            ->where('type', $type)
            ->visibleOnHome()
            ->paginate(9)
            ->withQueryString();

        return view('frontend.content.index', [
            'posts' => $posts,
            'heading' => $heading,
            'routeName' => $routeName,
            'type' => $type,
        ]);
    }

    private function showPost(ContentPost $contentPost, string $type, string $heading): View
    {
        $post = ContentPost::query()
            ->whereKey($contentPost->id)
            ->where('type', $type)
            ->published()
            ->firstOrFail();

        $relatedPosts = ContentPost::query()
            ->where('type', $type)
            ->published()
            ->whereKeyNot($post->id)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('frontend.content.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'heading' => $heading,
            'type' => $type,
        ]);
    }
}
