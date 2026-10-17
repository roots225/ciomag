<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'type',
        'issue_number',
        'author_name',
        'excerpt',
        'description',
        'price',
        'compare_at_price',
        'currency',
        'stock',
        'is_digital',
        'digital_file_url',
        'cover_url',
        'pages_count',
        'publication_date',
        'status',
        'is_featured',
        'seo_meta',
    ];

    protected $attributes = [
        'seo_meta' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_digital' => 'boolean',
            'is_featured' => 'boolean',
            'publication_date' => 'date',
            'seo_meta' => 'array',
        ];
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'magazine' => 'Magazine CIOMAG',
            'book' => 'Livre & Ouvrage',
            'report' => 'Livre Blanc & Étude',
            'bundle' => 'Pack & Collection',
            default => ucfirst($this->type),
        };
    }

    public function formattedPrice(): string
    {
        return number_format((float) $this->price, 2, ',', ' ') . ' ' . ($this->currency === 'EUR' ? '€' : $this->currency);
    }

    public function formattedComparePrice(): ?string
    {
        if (! $this->compare_at_price || $this->compare_at_price <= $this->price) {
            return null;
        }

        return number_format((float) $this->compare_at_price, 2, ',', ' ') . ' ' . ($this->currency === 'EUR' ? '€' : $this->currency);
    }
}
