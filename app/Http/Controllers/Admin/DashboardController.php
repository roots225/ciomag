<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Tag;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'published_posts' => Post::where('type', PostType::Post)->where('status', PostStatus::Published)->count(),
            'draft_posts' => Post::where('type', PostType::Post)->whereIn('status', [PostStatus::Draft, PostStatus::PendingReview, PostStatus::Scheduled])->count(),
            'pages' => Post::where('type', PostType::Page)->count(),
            'total_views' => (int) Post::sum('view_count'),
            'pending_comments' => Comment::where('status', CommentStatus::Pending)->count(),
            'approved_comments' => Comment::where('status', CommentStatus::Approved)->count(),
            'categories' => Category::count(),
            'tags' => Tag::count(),
            'media' => Media::count(),
            'users' => User::count(),
            'menus' => Menu::count(),
            'redirects' => Redirect::where('is_active', true)->count(),
        ];

        $recentPosts = Post::with(['author', 'categories'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $pendingComments = Comment::with(['post', 'user'])
            ->where('status', CommentStatus::Pending)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $topPosts = Post::articles()
            ->published()
            ->orderByDesc('view_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPosts', 'pendingComments', 'topPosts'));
    }
}
