<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentFormat;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Tag;
use App\Services\MenuResolverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $posts = Post::query()
            ->with(['author', 'categories', 'featuredMedia'])
            ->withCount(['revisions', 'comments'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->fullTextSearch($search))
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.posts.index', compact('posts', 'type', 'status', 'search'));
    }

    public function create(Request $request): View
    {
        $post = new Post([
            'type' => $request->query('type', 'post'),
            'status' => 'draft',
            'content_format' => 'html',
            'allow_comments' => true,
            'is_featured' => false,
            'is_premium' => false,
            'seo_meta' => [
                'meta_title' => '',
                'meta_description' => '',
                'canonical_url' => '',
                'og_image' => '',
                'noindex' => false,
            ],
        ]);

        return $this->formView($post);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $post = Post::create([
            'author_id' => $request->user()->id,
            'featured_media_id' => $validated['featured_media_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'type' => $validated['type'],
            'status' => $validated['status'],
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'content_format' => $validated['content_format'] ?? ContentFormat::Html->value,
            'is_featured' => $request->boolean('is_featured'),
            'is_premium' => $request->boolean('is_premium'),
            'allow_comments' => $request->boolean('allow_comments'),
            'published_at' => $validated['published_at'] ?? ($validated['status'] === PostStatus::Published->value ? now() : null),
            'seo_meta' => $this->extractSeoMeta($request),
        ]);

        $this->syncTaxonomies($post, $request);

        PostRevision::create([
            'post_id' => $post->id,
            'editor_id' => $request->user()->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'change_summary' => $request->input('change_summary') ?: 'Création initiale',
        ]);

        $this->handleSocialPublishing($post, $request);

        app(MenuResolverService::class)->clearAllMenuCaches();

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Contenu créé et révision initiale enregistrée.');
    }

    public function edit(Post $post): View
    {
        $post->load(['categories', 'tags', 'revisions.editor', 'featuredMedia', 'socialShares']);

        return $this->formView($post);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $this->validatePost($request, $post);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $contentChanged = $post->title !== $validated['title']
            || $post->content !== $validated['content']
            || $post->excerpt !== ($validated['excerpt'] ?? null);

        $post->update([
            'featured_media_id' => $validated['featured_media_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'type' => $validated['type'],
            'status' => $validated['status'],
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'content_format' => $validated['content_format'] ?? ContentFormat::Html->value,
            'is_featured' => $request->boolean('is_featured'),
            'is_premium' => $request->boolean('is_premium'),
            'allow_comments' => $request->boolean('allow_comments'),
            'published_at' => $validated['published_at'] ?? $post->published_at,
            'seo_meta' => $this->extractSeoMeta($request),
        ]);

        $this->syncTaxonomies($post, $request);

        if ($contentChanged || $request->filled('change_summary')) {
            PostRevision::create([
                'post_id' => $post->id,
                'editor_id' => $request->user()->id,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'change_summary' => $request->input('change_summary') ?: 'Mise à jour éditoriale',
            ]);
        }

        $this->handleSocialPublishing($post, $request);

        app(MenuResolverService::class)->clearAllMenuCaches();

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Modifications enregistrées avec succès.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();
        app(MenuResolverService::class)->clearAllMenuCaches();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Contenu déplacé dans la corbeille (Soft Delete).');
    }

    public function restoreRevision(Request $request, Post $post, PostRevision $revision): RedirectResponse
    {
        abort_unless($revision->post_id === $post->id, 404);

        $post->update([
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'content' => $revision->content,
        ]);

        PostRevision::create([
            'post_id' => $post->id,
            'editor_id' => $request->user()->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'change_summary' => 'Restauration de la révision du ' . $revision->created_at->format('d/m/Y H:i'),
        ]);

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Révision restaurée avec succès.');
    }

    private function formView(Post $post): View
    {
        $categories = Category::with('parent')->orderBy('display_order')->get();
        $tags = Tag::orderBy('name')->get();
        $mediaItems = Media::orderByDesc('created_at')->limit(50)->get();
        $parentPages = Post::pages()
            ->when($post->exists, fn ($q) => $q->where('id', '!=', $post->id))
            ->orderBy('title')
            ->get();

        return view('admin.posts.form', compact('post', 'categories', 'tags', 'mediaItems', 'parentPages'));
    }

    private function validatePost(Request $request, ?Post $post = null): array
    {
        $type = $request->input('type', 'post');

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')
                    ->where('type', $type)
                    ->ignore($post?->id),
            ],
            'type' => ['required', Rule::in(['post', 'page'])],
            'status' => ['required', Rule::in(['draft', 'pending_review', 'scheduled', 'published', 'archived'])],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'content_format' => ['nullable', Rule::in(['html', 'markdown', 'blocks_json'])],
            'featured_media_id' => ['nullable', 'uuid', 'exists:media,id'],
            'parent_id' => ['nullable', 'uuid', 'exists:posts,id'],
            'published_at' => ['nullable', 'date'],
            'primary_category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['uuid', 'exists:tags,id'],
            'change_summary' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function extractSeoMeta(Request $request): array
    {
        return array_filter([
            'meta_title' => trim((string) $request->input('seo_meta_title', '')),
            'meta_description' => trim((string) $request->input('seo_meta_description', '')),
            'canonical_url' => trim((string) $request->input('seo_canonical_url', '')),
            'og_image' => trim((string) $request->input('seo_og_image', '')),
            'noindex' => $request->boolean('seo_noindex'),
        ], fn ($v) => $v !== '');
    }

    private function syncTaxonomies(Post $post, Request $request): void
    {
        $categoryIds = collect($request->input('category_ids', []))->filter()->unique()->values();
        $primaryId = $request->input('primary_category_id');

        if ($primaryId && ! $categoryIds->contains($primaryId)) {
            $categoryIds->prepend($primaryId);
        }

        $syncCategories = [];
        foreach ($categoryIds as $index => $catId) {
            $syncCategories[$catId] = [
                'is_primary' => $primaryId ? ($catId === $primaryId) : ($index === 0),
            ];
        }

        $post->categories()->sync($syncCategories);
        $post->tags()->sync($request->input('tag_ids', []));
    }

    private function handleSocialPublishing(Post $post, Request $request): void
    {
        $platforms = array_values(array_filter((array) $request->input('social_platforms', [])));
        $socialService = app(\App\Services\SocialPublisherService::class);
        $config = $socialService->getConfig();

        if (empty($platforms) && ! empty($config['auto_publish_on_post']) && $post->status === PostStatus::Published && $post->type === PostType::Post) {
            foreach (['facebook', 'linkedin', 'twitter'] as $p) {
                if (! empty($config[$p]['enabled'])) {
                    $platforms[] = $p;
                }
            }
        }

        if (! empty($platforms)) {
            $socialService->publishPost(
                $post,
                $platforms,
                $request->input('social_custom_message'),
                $request->user()?->id
            );
        }
    }
}
