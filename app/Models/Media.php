<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasUuids;

    protected $table = 'media';

    public $timestamps = false;

    protected $fillable = [
        'uploader_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'width',
        'height',
        'alt_text',
        'caption',
        'variants',
        'created_at',
    ];

    protected $attributes = [
        'variants' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'variants' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function url(string $variant = 'original'): string
    {
        if ($variant !== 'original' && ! empty($this->variants[$variant])) {
            return $this->variants[$variant];
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://') || str_starts_with($this->file_path, '/')) {
            return $this->file_path;
        }

        return Storage::url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function humanFileSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' Mo';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' Ko';
        }

        return $bytes . ' octets';
    }
}
