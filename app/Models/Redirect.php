<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'hits',
        'is_active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            $redirect->source_path = '/' . ltrim(trim($redirect->source_path), '/');
        });

        static::saved(fn () => Cache::forget('cms_active_redirects'));
        static::deleted(fn () => Cache::forget('cms_active_redirects'));
    }
}
