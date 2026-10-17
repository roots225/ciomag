<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $comments = Comment::with(['post', 'user', 'parent'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'status'));
    }

    public function updateStatus(Request $request, Comment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'spam', 'trash'])],
        ]);

        $comment->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut du commentaire mis à jour ('.CommentStatus::from($validated['status'])->label().').');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Commentaire supprimé définitivement.');
    }
}
