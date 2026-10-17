<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PodcastController extends Controller
{
    public function index(Request $request): View
    {
        $source = $request->query('source');
        $perPage = min(50, max(1, (int) $request->query('per_page', 6)));

        $featuredPodcast = Podcast::query()
            ->where('status', 'published')
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->first();

        $podcasts = Podcast::query()
            ->where('status', 'published')
            ->with('category')
            ->when($source, fn ($q) => $q->where('source_type', $source))
            ->orderByDesc('season_number')
            ->orderByDesc('episode_number')
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.podcasts.index', compact('podcasts', 'featuredPodcast', 'source'));
    }

    public function show(string $slug): View
    {
        $podcast = Podcast::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->with('category')
            ->firstOrFail();

        $podcast->increment('view_count');

        $relatedPodcasts = Podcast::query()
            ->where('status', 'published')
            ->where('id', '!=', $podcast->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('magazine.podcasts.show', compact('podcast', 'relatedPodcasts'));
    }
}
