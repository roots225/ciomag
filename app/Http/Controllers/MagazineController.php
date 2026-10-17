<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MagazineController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 8)));

        $featuredPosts = Post::query()
            ->articles()
            ->published()
            ->with(['author.role', 'featuredMedia', 'categories'])
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        if ($featuredPosts->isEmpty()) {
            $featuredPosts = Post::query()
                ->articles()
                ->published()
                ->with(['author.role', 'featuredMedia', 'categories'])
                ->orderByDesc('published_at')
                ->limit(2)
                ->get();
        }

        $heroPost = $featuredPosts->first();
        $secondaryFeatured = $featuredPosts->slice(1);

        $latestPosts = Post::query()
            ->articles()
            ->published()
            ->with(['author', 'featuredMedia', 'categories', 'tags'])
            ->when($heroPost, fn ($q) => $q->where('id', '!=', $heroPost->id))
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();

        $mostReadPosts = Post::query()
            ->articles()
            ->published()
            ->with(['categories'])
            ->orderByDesc('view_count')
            ->limit(5)
            ->get();

        $categoriesWithPosts = Category::query()
            ->whereNull('parent_id')
            ->with(['children'])
            ->orderBy('display_order')
            ->get();

        $popularTags = Tag::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderByDesc('posts_count')
            ->limit(10)
            ->get();

        return view('magazine.home', compact(
            'heroPost',
            'secondaryFeatured',
            'latestPosts',
            'mostReadPosts',
            'categoriesWithPosts',
            'popularTags'
        ));
    }

    public function post(Request $request, string $slug): View
    {
        $post = Post::query()
            ->articles()
            ->published()
            ->where('slug', $slug)
            ->with([
                'author.role',
                'featuredMedia',
                'categories.parent',
                'tags',
            ])
            ->firstOrFail();

        // Incrémentation atomique du compteur de vues
        $post->increment('view_count');

        $primaryCategory = $post->primaryCategory();

        $relatedPosts = Post::query()
            ->articles()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($primaryCategory, function ($q) use ($primaryCategory) {
                $q->whereHas('categories', fn ($cq) => $cq->where('categories.id', $primaryCategory->id));
            })
            ->with(['author', 'featuredMedia', 'categories'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $comments = Comment::query()
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->where('status', CommentStatus::Approved->value)
            ->with(['user.role', 'approvedReplies.user.role', 'approvedReplies.approvedReplies.user.role'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.post', compact('post', 'primaryCategory', 'relatedPosts', 'comments'));
    }

    public function page(string $slug): View
    {
        $page = Post::query()
            ->pages()
            ->published()
            ->where('slug', $slug)
            ->with(['author', 'parent', 'children' => fn ($q) => $q->published()])
            ->firstOrFail();

        $page->increment('view_count');

        return view('magazine.page', compact('page'));
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->with(['parent', 'children'])
            ->firstOrFail();

        $categoryIds = collect([$category->id])
            ->merge($category->children->pluck('id'))
            ->all();

        $perPage = min(50, max(1, (int) $request->query('per_page', 9)));

        $posts = Post::query()
            ->articles()
            ->published()
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
            ->with(['author', 'featuredMedia', 'categories', 'tags'])
            ->feedOrder()
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.category', compact('category', 'posts'));
    }

    public function tag(Request $request, string $slug): View
    {
        $tag = Tag::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $perPage = min(50, max(1, (int) $request->query('per_page', 9)));

        $posts = Post::query()
            ->articles()
            ->published()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->with(['author', 'featuredMedia', 'categories', 'tags'])
            ->feedOrder()
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.tag', compact('tag', 'posts'));
    }

    public function author(Request $request, string $username): View
    {
        $author = User::query()
            ->where('username', $username)
            ->with('role')
            ->firstOrFail();

        $perPage = min(50, max(1, (int) $request->query('per_page', 9)));

        $posts = Post::query()
            ->articles()
            ->published()
            ->where('author_id', $author->id)
            ->with(['featuredMedia', 'categories', 'tags'])
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.author', compact('author', 'posts'));
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $posts = collect();

        if ($query !== '') {
            $posts = Post::query()
                ->articles()
                ->published()
                ->fullTextSearch($query)
                ->with(['author', 'featuredMedia', 'categories'])
                ->orderByDesc('published_at')
                ->paginate(10)
                ->withQueryString();
        }

        return view('magazine.search', compact('query', 'posts'));
    }

    public function storeComment(Request $request, string $slug): RedirectResponse
    {
        $post = Post::query()
            ->articles()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $post->allow_comments) {
            return back()->with('error', 'Les commentaires sont fermés sur cet article.');
        }

        $user = $request->user();

        $rules = [
            'content' => ['required', 'string', 'min:5', 'max:3000'],
            'parent_id' => ['nullable', 'uuid', 'exists:comments,id'],
        ];

        if (! $user) {
            $rules['guest_name'] = ['required', 'string', 'max:100'];
            $rules['guest_email'] = ['required', 'email', 'max:255'];
            $rules['guest_website'] = ['nullable', 'url', 'max:255'];
        }

        $validated = $request->validate($rules);

        // Si l'utilisateur est connecté et actif, approbation directe ; sinon en attente de modération
        $status = $user ? CommentStatus::Approved : CommentStatus::Pending;

        Comment::create([
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : $validated['guest_name'],
            'guest_email' => $user ? null : $validated['guest_email'],
            'guest_website' => $user ? null : ($validated['guest_website'] ?? null),
            'content' => $validated['content'],
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $message = $status === CommentStatus::Approved
            ? 'Votre commentaire a été publié.'
            : 'Merci ! Votre commentaire a été transmis à la rédaction pour modération.';

        return redirect()->to(route('magazine.post', $post->slug) . '#comments')
            ->with('success', $message);
    }
}
