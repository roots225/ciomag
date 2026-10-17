<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use App\Services\MenuResolverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $allCategories = Category::orderBy('display_order')->get();

        $categories = Category::with('parent')
            ->withCount('posts')
            ->orderBy('display_order')
            ->paginate($perPage)
            ->withQueryString();

        $tags = Tag::withCount('posts')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories', 'allCategories', 'tags'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:categories,slug'],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:1000'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'display_order' => (int) ($validated['display_order'] ?? 0),
            'seo_meta' => array_filter([
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'og_image' => $validated['og_image'] ?? null,
            ]),
        ]);

        app(MenuResolverService::class)->clearAllMenuCaches();

        return back()->with('success', 'Catégorie créée avec succès.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('categories', 'slug')->ignore($category->id)],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'parent_id' => $validated['parent_id'] === $category->id ? null : ($validated['parent_id'] ?? null),
            'description' => $validated['description'] ?? null,
            'display_order' => (int) ($validated['display_order'] ?? 0),
        ]);

        app(MenuResolverService::class)->clearAllMenuCaches();

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $category->delete();
        app(MenuResolverService::class)->clearAllMenuCaches();

        return back()->with('success', 'Catégorie supprimée.');
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:tags,slug'],
            'description' => ['nullable', 'string'],
        ]);

        Tag::create([
            'name' => $validated['name'],
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Tag ajouté.');
    }

    public function destroyTag(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Tag supprimé.');
    }
}
