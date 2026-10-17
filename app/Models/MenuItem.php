<?php

namespace App\Models;

use App\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class MenuItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'title_attr',
        'type',
        'post_id',
        'category_id',
        'custom_url',
        'target',
        'icon',
        'css_classes',
        'badge_text',
        'display_order',
        'is_active',
    ];

    protected $attributes = [
        'type' => 'custom_url',
        'target' => '_self',
        'display_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MenuItem $item) {
            $typeValue = $item->type instanceof MenuItemType ? $item->type->value : (string) $item->type;

            // Normalisation automatique des colonnes exclusives selon le type déclaré
            if ($typeValue === MenuItemType::Post->value) {
                $item->category_id = null;
                $item->custom_url = null;
                if (empty($item->post_id)) {
                    throw new InvalidArgumentException('Un élément de menu de type "post" requiert un post_id.');
                }
            } elseif ($typeValue === MenuItemType::Category->value) {
                $item->post_id = null;
                $item->custom_url = null;
                if (empty($item->category_id)) {
                    throw new InvalidArgumentException('Un élément de menu de type "category" requiert un category_id.');
                }
            } elseif ($typeValue === MenuItemType::CustomUrl->value) {
                $item->post_id = null;
                $item->category_id = null;
                if (empty($item->custom_url)) {
                    throw new InvalidArgumentException('Un élément de menu de type "custom_url" requiert une custom_url.');
                }
            } elseif ($typeValue === MenuItemType::Container->value) {
                $item->post_id = null;
                $item->category_id = null;
                $item->custom_url = null;
            }
        });

        static::saved(function (MenuItem $item) {
            if ($item->menu) {
                Cache::forget("cms_menu_tree:{$item->menu->location}");
            }
        });

        static::deleted(function (MenuItem $item) {
            if ($item->menu) {
                Cache::forget("cms_menu_tree:{$item->menu->location}");
            }
        });
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('display_order');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
