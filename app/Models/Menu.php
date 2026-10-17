<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'location',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (Menu $menu) => Cache::forget("cms_menu_tree:{$menu->location}"));
        static::deleted(fn (Menu $menu) => Cache::forget("cms_menu_tree:{$menu->location}"));
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('display_order');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('display_order');
    }
}
