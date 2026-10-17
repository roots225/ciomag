<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialShare extends Model
{
    use HasUuids;

    protected $fillable = [
        'post_id',
        'podcast_id',
        'platform',
        'status',
        'message',
        'target_url',
        'external_post_id',
        'external_url',
        'response_payload',
        'shared_by',
        'shared_at',
    ];

    protected $attributes = [
        'response_payload' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'shared_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function platformLabel(): string
    {
        return match ($this->platform) {
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'twitter' => 'X (Twitter)',
            'youtube' => 'YouTube',
            default => ucfirst($this->platform),
        };
    }

    public function platformIcon(): string
    {
        return match ($this->platform) {
            'facebook' => 'facebook',
            'linkedin' => 'linkedin',
            'twitter' => 'twitter',
            'youtube' => 'youtube',
            default => 'share-2',
        };
    }
}
