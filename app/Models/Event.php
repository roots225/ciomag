<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'event_type',
        'excerpt',
        'description',
        'venue',
        'city',
        'country',
        'is_online',
        'online_url',
        'starts_at',
        'ends_at',
        'cover_url',
        'capacity',
        'ticket_price',
        'currency',
        'status',
        'is_featured',
        'registration_open',
        'program',
        'committee',
        'partners',
        'seo_meta',
    ];

    protected $attributes = [
        'program' => '[]',
        'committee' => '[]',
        'partners' => '[]',
        'seo_meta' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'ticket_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'registration_open' => 'boolean',
            'program' => 'array',
            'committee' => 'array',
            'partners' => 'array',
            'seo_meta' => 'array',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function confirmedRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class)->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now());
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at && $this->starts_at->isFuture();
    }

    public function formattedPrice(): string
    {
        if ((float) $this->ticket_price <= 0) {
            return 'Gratuit (Sur accréditation)';
        }

        return number_format((float) $this->ticket_price, 2, ',', ' ') . ' ' . ($this->currency === 'EUR' ? '€' : $this->currency);
    }

    public function locationLabel(): string
    {
        if ($this->is_online && ! $this->city) {
            return 'En ligne (Digital Live)';
        }

        $parts = array_filter([$this->venue, $this->city, $this->country]);

        return ! empty($parts) ? implode(', ', $parts) : 'Lieu à confirmer';
    }
}
