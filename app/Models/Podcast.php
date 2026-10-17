<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'season_number',
        'episode_number',
        'excerpt',
        'description',
        'source_type',
        'youtube_url',
        'youtube_video_id',
        'video_file_path',
        'audio_file_path',
        'thumbnail_url',
        'duration_label',
        'guest_name',
        'guest_role',
        'host_name',
        'category_id',
        'status',
        'is_featured',
        'view_count',
        'social_distribution',
        'published_at',
    ];

    protected $attributes = [
        'social_distribution' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'social_distribution' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function socialShares(): HasMany
    {
        return $this->hasMany(SocialShare::class);
    }

    /**
     * Extrait l'identifiant vidéo YouTube depuis n'importe quel format d'URL YouTube.
     */
    public static function extractYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function embedYoutubeUrl(): ?string
    {
        $id = $this->youtube_video_id ?: self::extractYoutubeId($this->youtube_url);

        return $id ? "https://www.youtube.com/embed/{$id}?rel=0" : null;
    }

    public function coverImage(): string
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }

        $id = $this->youtube_video_id ?: self::extractYoutubeId($this->youtube_url);
        if ($id) {
            return "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
        }

        return 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&q=80';
    }

    public function resolvedThumbnail(): string
    {
        return $this->coverImage();
    }

    public function videoFileUrl(): ?string
    {
        if (! $this->video_file_path) {
            return null;
        }

        if (str_starts_with($this->video_file_path, 'http://') || str_starts_with($this->video_file_path, 'https://')) {
            return $this->video_file_path;
        }

        return asset('storage/' . ltrim($this->video_file_path, '/'));
    }
}
