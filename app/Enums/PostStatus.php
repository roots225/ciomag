<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::PendingReview => 'En relecture',
            self::Scheduled => 'Planifié',
            self::Published => 'Publié',
            self::Archived => 'Archivé',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700',
            self::PendingReview => 'bg-amber-100 text-amber-800',
            self::Scheduled => 'bg-blue-100 text-blue-800',
            self::Published => 'bg-emerald-100 text-emerald-800',
            self::Archived => 'bg-rose-100 text-rose-800',
        };
    }
}
