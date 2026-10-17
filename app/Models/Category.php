<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'seo_meta',
        'display_order',
        'created_at',
    ];

    protected $attributes = [
        'seo_meta' => '{}',
        'display_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'seo_meta' => 'array',
            'display_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('display_order');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_categories')
            ->withPivot('is_primary');
    }
}
