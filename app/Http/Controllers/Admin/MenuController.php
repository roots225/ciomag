<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Services\MenuResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request, MenuResolverService $menuResolver): View
    {
        $menus = Menu::with(['items.parent', 'items.post', 'items.category'])
            ->orderBy('name')
            ->get();

        $activeMenuId = $request->query('menu_id', $menus->first()?->id);
        $activeMenu = $menus->firstWhere('id', $activeMenuId) ?? $menus->first();

        $resolvedPreview = $activeMenu
            ? $menuResolver->resolveFlat($activeMenu->location)
            : [];

        $itemsMap = [];
        if ($activeMenu) {
            foreach ($activeMenu->items as $item) {
                $itemsMap[$item->id] = [
                    'id' => $item->id,
                    'label' => $item->label,
                    'parent_id' => $item->parent_id,
                    'type' => $item->type instanceof \BackedEnum ? $item->type->value : (string) $item->type,
                    'post_id' => $item->post_id,
                    'category_id' => $item->category_id,
                    'custom_url' => $item->custom_url,
                    'target' => $item->target,
                    'badge_text' => $item->badge_text,
                    'display_order' => (int) $item->display_order,
                ];
            }
        }

        $publishedPosts = Post::published()->orderBy('type')->orderBy('title')->get(['id', 'title', 'type', 'slug']);
        $categories = Category::orderBy('display_order')->get(['id', 'name', 'slug']);

        return view('admin.menus.index', compact(
            'menus',
            'activeMenu',
            'resolvedPreview',
            'itemsMap',
            'publishedPosts',
            'categories'
        ));
    }

    public function storeMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:50', 'unique:menus,location'],
            'description' => ['nullable', 'string'],
        ]);

        $menu = Menu::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.menus.index', ['menu_id' => $menu->id])
            ->with('success', 'Emplacement de menu créé.');
    }

    public function updateMenu(Request $request, Menu $menu, MenuResolverService $menuResolver): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $menu->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $menuResolver->clearAllMenuCaches();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Menu « {$menu->name} » mis à jour avec succès.",
                'menu' => $menu,
            ]);
        }

        return redirect()->route('admin.menus.index', ['menu_id' => $menu->id])
            ->with('success', "Menu « {$menu->name} » mis à jour avec succès.");
    }

    public function storeItem(Request $request, Menu $menu, MenuResolverService $menuResolver): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'title_attr' => ['nullable', 'string', 'max:150'],
            'parent_id' => ['nullable', 'uuid', 'exists:menu_items,id'],
            'type' => ['required', Rule::in(['post', 'category', 'custom_url', 'container'])],
            'post_id' => ['nullable', 'required_if:type,post', 'uuid', 'exists:posts,id'],
            'category_id' => ['nullable', 'required_if:type,category', 'uuid', 'exists:categories,id'],
            'custom_url' => ['nullable', 'required_if:type,custom_url', 'string', 'max:500'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'icon' => ['nullable', 'string', 'max:50'],
            'css_classes' => ['nullable', 'string', 'max:150'],
            'badge_text' => ['nullable', 'string', 'max:30'],
            'display_order' => ['nullable', 'integer'],
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'label' => $validated['label'],
            'title_attr' => $validated['title_attr'] ?? null,
            'type' => $validated['type'],
            'post_id' => $validated['type'] === 'post' ? ($validated['post_id'] ?? null) : null,
            'category_id' => $validated['type'] === 'category' ? ($validated['category_id'] ?? null) : null,
            'custom_url' => $validated['type'] === 'custom_url' ? ($validated['custom_url'] ?? null) : null,
            'target' => $validated['target'],
            'icon' => $validated['icon'] ?? null,
            'css_classes' => $validated['css_classes'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'display_order' => (int) ($validated['display_order'] ?? 0),
            'is_active' => true,
        ]);

        $menuResolver->clearAllMenuCaches();

        return redirect()->route('admin.menus.index', ['menu_id' => $menu->id])
            ->with('success', 'Élément ajouté au menu.');
    }

    public function updateItem(Request $request, MenuItem $menuItem, MenuResolverService $menuResolver): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'title_attr' => ['nullable', 'string', 'max:150'],
            'parent_id' => ['nullable', 'uuid', 'exists:menu_items,id'],
            'type' => ['required', Rule::in(['post', 'category', 'custom_url', 'container'])],
            'post_id' => ['nullable', 'required_if:type,post', 'uuid', 'exists:posts,id'],
            'category_id' => ['nullable', 'required_if:type,category', 'uuid', 'exists:categories,id'],
            'custom_url' => ['nullable', 'required_if:type,custom_url', 'string', 'max:500'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'icon' => ['nullable', 'string', 'max:50'],
            'css_classes' => ['nullable', 'string', 'max:150'],
            'badge_text' => ['nullable', 'string', 'max:30'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $newParentId = $validated['parent_id'] ?? null;

        if ($newParentId) {
            if ($newParentId === $menuItem->id) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un élément ne peut pas être son propre parent.',
                    ], 422);
                }

                return back()->with('error', 'Un élément ne peut pas être son propre parent.');
            }

            // Check circular relationship
            $curr = MenuItem::find($newParentId);
            while ($curr && $curr->parent_id) {
                if ($curr->parent_id === $menuItem->id) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Action impossible : cet élément est un descendant de l\'élément modifié.',
                        ], 422);
                    }

                    return back()->with('error', 'Action impossible : cet élément est un descendant de l\'élément modifié.');
                }
                $curr = MenuItem::find($curr->parent_id);
            }
        }

        $menuItem->update([
            'parent_id' => $newParentId ?: null,
            'label' => $validated['label'],
            'title_attr' => $validated['title_attr'] ?? null,
            'type' => $validated['type'],
            'post_id' => $validated['type'] === 'post' ? ($validated['post_id'] ?? null) : null,
            'category_id' => $validated['type'] === 'category' ? ($validated['category_id'] ?? null) : null,
            'custom_url' => $validated['type'] === 'custom_url' ? ($validated['custom_url'] ?? null) : null,
            'target' => $validated['target'],
            'icon' => $validated['icon'] ?? null,
            'css_classes' => $validated['css_classes'] ?? null,
            'badge_text' => $validated['badge_text'] ?? null,
            'display_order' => (int) ($validated['display_order'] ?? 0),
        ]);

        $menuResolver->clearAllMenuCaches();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Élément « {$menuItem->label} » mis à jour avec succès.",
                'item' => $menuItem,
                'tree' => $menuResolver->resolveFlat($menuItem->menu->location),
            ]);
        }

        return redirect()->route('admin.menus.index', ['menu_id' => $menuItem->menu_id])
            ->with('success', "Élément « {$menuItem->label} » mis à jour.");
    }

    public function reparentItem(Request $request, MenuItem $menuItem, MenuResolverService $menuResolver): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'uuid', 'exists:menu_items,id'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $newParentId = $validated['parent_id'] ?? null;

        if ($newParentId) {
            if ($newParentId === $menuItem->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un élément ne peut pas être son propre parent.',
                ], 422);
            }

            $parent = MenuItem::find($newParentId);
            if (! $parent || $parent->menu_id !== $menuItem->menu_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'élément parent doit appartenir au même menu.',
                ], 422);
            }

            // Check circular relationship
            $curr = $parent;
            while ($curr && $curr->parent_id) {
                if ($curr->parent_id === $menuItem->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Action impossible : cet élément est un sous-élément (descendant) de l\'élément déplacé.',
                    ], 422);
                }
                $curr = MenuItem::find($curr->parent_id);
            }
        }

        $menuItem->parent_id = $newParentId ?: null;
        if (isset($validated['display_order'])) {
            $menuItem->display_order = (int) $validated['display_order'];
        }
        $menuItem->save();

        $menuResolver->clearAllMenuCaches();
        $tree = $menuResolver->resolveFlat($menuItem->menu->location);

        if ($menuItem->parent_id) {
            $parentName = MenuItem::find($menuItem->parent_id)?->label ?? 'parent';
            $message = "« {$menuItem->label} » est maintenant rattaché à « {$parentName} ».";
        } else {
            $message = "Le parent a été retiré : « {$menuItem->label} » est désormais au Niveau 0 (Racine).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'item' => $menuItem,
            'tree' => $tree,
        ]);
    }

    public function destroyItem(MenuItem $menuItem, MenuResolverService $menuResolver): RedirectResponse
    {
        $menuId = $menuItem->menu_id;
        $menuItem->delete();
        $menuResolver->clearAllMenuCaches();

        return redirect()->route('admin.menus.index', ['menu_id' => $menuId])
            ->with('success', 'Élément de menu supprimé.');
    }
}
