<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'badge',
        'description',
        'billing_interval',
        'price',
        'currency',
        'features',
        'includes_print',
        'is_featured',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'includes_print' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function intervalLabel(): string
    {
        return match ($this->billing_interval) {
            'monthly' => 'par mois',
            'yearly' => 'par an',
            'lifetime' => 'accès illimité',
            default => $this->billing_interval,
        };
    }

    public function formattedPrice(): string
    {
        return number_format((float) $this->price, 2, ',', ' ').' '.($this->currency === 'EUR' ? '€' : $this->currency);
    }
}
