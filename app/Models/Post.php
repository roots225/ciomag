<?php

namespace App\Models;

use App\Enums\ContentFormat;
use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'author_id',
        'featured_media_id',
        'parent_id',
        'type',
        'status',
        'title',
        'slug',
        'excerpt',
        'content',
        'content_format',
        'is_featured',
        'is_premium',
        'allow_comments',
        'reading_time_minutes',
        'view_count',
        'seo_meta',
        'published_at',
    ];

    protected $attributes = [
        'seo_meta' => '{}',
        'type' => 'post',
        'status' => 'draft',
        'content_format' => 'html',
        'is_featured' => false,
        'is_premium' => false,
        'allow_comments' => true,
        'reading_time_minutes' => 1,
        'view_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'status' => PostStatus::class,
            'content_format' => ContentFormat::class,
            'is_featured' => 'boolean',
            'is_premium' => 'boolean',
            'allow_comments' => 'boolean',
            'reading_time_minutes' => 'integer',
            'view_count' => 'integer',
            'seo_meta' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (empty($post->seo_meta)) {
                $post->seo_meta = [];
            }

            // Calcul automatique du temps de lecture (base ~220 mots/minute)
            $wordCount = str_word_count(strip_tags((string) $post->content));
            $post->reading_time_minutes = max(1, (int) ceil($wordCount / 220));

            if ($post->status === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }
        });
    }

    // ==========================================
    // RELATIONS
    // ==========================================

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)->orderByDesc('created_at');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories')
            ->withPivot('is_primary');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published->value)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeArticles(Builder $query): Builder
    {
        return $query->where('type', PostType::Post->value);
    }

    public function scopePages(Builder $query): Builder
    {
        return $query->where('type', PostType::Page->value);
    }

    public function scopeFeedOrder(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderByDesc('published_at');
    }

    public function scopeFullTextSearch(Builder $query, string $term): Builder
    {
        $cleanTerm = trim($term);
        if ($cleanTerm === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($cleanTerm) {
            $q->whereFullText(['title', 'excerpt', 'content'], $cleanTerm)
                ->orWhere('title', 'like', "%{$cleanTerm}%")
                ->orWhere('excerpt', 'like', "%{$cleanTerm}%");
        });
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function primaryCategory(): ?Category
    {
        if ($this->relationLoaded('categories')) {
            return $this->categories->firstWhere('pivot.is_primary', true)
                ?? $this->categories->first();
        }

        return $this->categories()->wherePivot('is_primary', true)->first()
            ?? $this->categories()->first();
    }

    public function url(): string
    {
        if ($this->type === PostType::Page) {
            return route('magazine.page', $this->slug);
        }

        return route('magazine.post', $this->slug);
    }

    public function featuredImageUrl(): ?string
    {
        if ($this->featuredMedia) {
            return $this->featuredMedia->url();
        }

        return $this->seo_meta['og_image'] ?? null;
    }

    public function seoTitle(): string
    {
        return ! empty($this->seo_meta['meta_title'])
            ? $this->seo_meta['meta_title']
            : $this->title;
    }

    public function seoDescription(): string
    {
        return ! empty($this->seo_meta['meta_description'])
            ? $this->seo_meta['meta_description']
            : (string) ($this->excerpt ?: Str::limit(strip_tags($this->content), 160));
    }

    public function socialShares(): HasMany
    {
        return $this->hasMany(SocialShare::class)->orderByDesc('created_at');
    }

    public function previewContent(): string
    {
        $paragraphs = preg_split('/<\/p>/i', (string) $this->content) ?: [];
        if (count($paragraphs) >= 2) {
            return implode('</p>', array_slice($paragraphs, 0, 2)).'</p>';
        }

        return '<p>'.e(Str::limit(strip_tags((string) $this->content), 550)).'</p>';
    }
}
