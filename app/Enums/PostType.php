<?php

namespace App\Enums;

enum PostType: string
{
    case Post = 'post';
    case Page = 'page';

    public function label(): string
    {
        return match ($this) {
            self::Post => 'Article',
            self::Page => 'Page',
        };
    }
}
