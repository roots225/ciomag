<?php

namespace App\Enums;

enum ContentFormat: string
{
    case Html = 'html';
    case Markdown = 'markdown';
    case BlocksJson = 'blocks_json';

    public function label(): string
    {
        return match ($this) {
            self::Html => 'HTML',
            self::Markdown => 'Markdown',
            self::BlocksJson => 'Blocs JSON',
        };
    }
}
