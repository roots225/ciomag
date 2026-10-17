<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuResolverService
{
    /**
     * Exécute la requête CTE récursive (MySQL 8.0) pour résoudre un menu par son emplacement (location)
     * en excluant automatiquement les liens vers des articles dépubliés ou supprimés.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolveFlat(string $location): array
    {
        $sql = <<<'SQL'
        WITH RECURSIVE menu_tree AS (
            -- 1. Niveau racine (parent_id IS NULL)
            SELECT 
                mi.id,
                mi.parent_id,
                mi.label,
                mi.title_attr,
                mi.type,
                mi.target,
                mi.icon,
                mi.css_classes,
                mi.badge_text,
                mi.display_order,
                1 AS depth,
                CAST(CONCAT(LPAD(mi.display_order, 6, '0'), ':', mi.id) AS CHAR(500)) AS sort_path,
                CASE 
                    WHEN mi.type = 'custom_url' THEN mi.custom_url
                    WHEN mi.type = 'container'  THEN '#'
                    WHEN mi.type = 'category' AND c.id IS NOT NULL THEN CONCAT('/category/', c.slug)
                    WHEN mi.type = 'post' AND p.id IS NOT NULL AND p.type = 'page' THEN CONCAT('/page/', p.slug)
                    WHEN mi.type = 'post' AND p.id IS NOT NULL AND p.type = 'post' THEN CONCAT('/blog/', p.slug)
                    ELSE NULL
                END AS resolved_url
            FROM menu_items mi
            JOIN menus m ON m.id = mi.menu_id
            LEFT JOIN posts p ON p.id = mi.post_id AND p.status = 'published' AND p.deleted_at IS NULL
            LEFT JOIN categories c ON c.id = mi.category_id
            WHERE m.location = ?
              AND m.is_active = 1
              AND mi.is_active = 1
              AND mi.parent_id IS NULL

            UNION ALL

            -- 2. Niveaux enfants (Sous-menus récursifs)
            SELECT 
                child.id,
                child.parent_id,
                child.label,
                child.title_attr,
                child.type,
                child.target,
                child.icon,
                child.css_classes,
                child.badge_text,
                child.display_order,
                parent.depth + 1 AS depth,
                CAST(CONCAT(parent.sort_path, '/', LPAD(child.display_order, 6, '0'), ':', child.id) AS CHAR(500)) AS sort_path,
                CASE 
                    WHEN child.type = 'custom_url' THEN child.custom_url
                    WHEN child.type = 'container'  THEN '#'
                    WHEN child.type = 'category' AND c.id IS NOT NULL THEN CONCAT('/category/', c.slug)
                    WHEN child.type = 'post' AND p.id IS NOT NULL AND p.type = 'page' THEN CONCAT('/page/', p.slug)
                    WHEN child.type = 'post' AND p.id IS NOT NULL AND p.type = 'post' THEN CONCAT('/blog/', p.slug)
                    ELSE NULL
                END AS resolved_url
            FROM menu_items child
            JOIN menu_tree parent ON child.parent_id = parent.id
            LEFT JOIN posts p ON p.id = child.post_id AND p.status = 'published' AND p.deleted_at IS NULL
            LEFT JOIN categories c ON c.id = child.category_id
            WHERE child.is_active = 1
        )
        SELECT 
            id, parent_id, label, title_attr, type, resolved_url, target, icon, css_classes, badge_text, display_order, depth
        FROM menu_tree
        WHERE resolved_url IS NOT NULL
        ORDER BY sort_path ASC
        SQL;

        $rows = DB::select($sql, [$location]);

        return array_map(fn ($row) => (array) $row, $rows);
    }

    /**
     * Retourne l'arborescence hiérarchique complète (avec sous-éléments 'children') mise en cache dans Redis.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolveTree(string $location): array
    {
        return Cache::remember("cms_menu_tree:{$location}", 3600, function () use ($location) {
            $flat = $this->resolveFlat($location);

            return $this->buildTree($flat);
        });
    }

    /**
     * Vide le cache de tous les emplacements de menus.
     */
    public function clearAllMenuCaches(): void
    {
        $locations = ['header_main', 'footer_col_1', 'footer_col_2', 'mobile_drawer'];
        foreach ($locations as $loc) {
            Cache::forget("cms_menu_tree:{$loc}");
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(array $items, ?string $parentId = null): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $item['children'] = $this->buildTree($items, $item['id']);
                $branch[] = $item;
            }
        }

        return $branch;
    }
}
