<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Comment extends Model
{
    use HasUuids;

    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_website',
        'content',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Comment $comment) {
            if (empty($comment->user_id) && (empty($comment->guest_name) || empty($comment->guest_email))) {
                throw new InvalidArgumentException(
                    'Un commentaire doit être associé à un utilisateur connecté ou comporter un nom et un email d\'invité.'
                );
            }
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function approvedReplies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', CommentStatus::Approved->value)
            ->orderBy('created_at', 'asc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorName(): string
    {
        return $this->user?->display_name ?? $this->guest_name ?? 'Lecteur anonyme';
    }
}
