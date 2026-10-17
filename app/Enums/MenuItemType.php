<?php

namespace App\Enums;

enum MenuItemType: string
{
    case Post = 'post';
    case Category = 'category';
    case CustomUrl = 'custom_url';
    case Container = 'container';

    public function label(): string
    {
        return match ($this) {
            self::Post => 'Article / Page',
            self::Category => 'Catégorie',
            self::CustomUrl => 'Lien personnalisé',
            self::Container => 'Conteneur (Sous-menu)',
        };
    }
}
