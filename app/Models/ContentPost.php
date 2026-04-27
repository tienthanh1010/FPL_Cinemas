<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContentPost extends Model
{
    protected $table = 'content_posts';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image_url',
        'badge_label',
        'status',
        'is_featured',
        'published_at',
        'starts_at',
        'ends_at',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ContentPost $post) {
            if (! $post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeNews(Builder $query): Builder
    {
        return $query->where('type', 'NEWS');
    }

    public function scopeOffers(Builder $query): Builder
    {
        return $query->where('type', 'OFFER');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'PUBLISHED')
            ->where(function (Builder $inner) {
                $inner->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $inner) {
                $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $inner) {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeVisibleOnHome(Builder $query): Builder
    {
        return $query->published()->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    public static function typeOptions(): array
    {
        return [
            'NEWS' => 'Tin tức',
            'OFFER' => 'Ưu đãi',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'DRAFT' => 'Nháp',
            'PUBLISHED' => 'Đang hiển thị',
            'ARCHIVED' => 'Lưu trữ',
        ];
    }
}
