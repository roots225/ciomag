<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'shipping_address',
        'total_amount',
        'currency',
        'payment_method',
        'status',
        'items',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'items' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedTotal(): string
    {
        return number_format((float) $this->total_amount, 2, ',', ' ').' '.($this->currency === 'EUR' ? '€' : $this->currency);
    }
}
