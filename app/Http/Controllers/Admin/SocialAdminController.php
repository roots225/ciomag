<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\SocialShare;
use App\Services\SocialPublisherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialAdminController extends Controller
{
    public function index(Request $request, SocialPublisherService $socialService): View
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));
        $platform = $request->query('platform');

        $config = $socialService->getConfig();

        $publishedPosts = Post::query()
            ->articles()
            ->published()
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        $publishedPodcasts = Podcast::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(25)
            ->get();

        $shares = SocialShare::query()
            ->with(['post', 'podcast', 'author'])
            ->when($platform, fn ($q) => $q->where('platform', $platform))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.social.index', compact(
            'config',
            'publishedPosts',
            'publishedPodcasts',
            'shares',
            'platform'
        ));
    }

    public function updateConfig(Request $request, SocialPublisherService $socialService): RedirectResponse
    {
        $newConfig = [
            'mode' => $request->input('mode', 'sandbox') === 'live' ? 'live' : 'sandbox',
            'auto_publish_on_post' => $request->boolean('auto_publish_on_post'),
            'default_template' => (string) $request->input('default_template', ''),
            'facebook' => [
                'enabled' => $request->boolean('fb_enabled'),
                'page_name' => (string) $request->input('fb_page_name', ''),
                'page_id' => (string) $request->input('fb_page_id', ''),
                'access_token' => (string) $request->input('fb_access_token', ''),
                'api_version' => (string) $request->input('fb_api_version', 'v21.0'),
            ],
            'linkedin' => [
                'enabled' => $request->boolean('li_enabled'),
                'organization_name' => (string) $request->input('li_organization_name', ''),
                'organization_urn' => (string) $request->input('li_organization_urn', ''),
                'access_token' => (string) $request->input('li_access_token', ''),
            ],
            'twitter' => [
                'enabled' => $request->boolean('tw_enabled'),
                'handle' => (string) $request->input('tw_handle', ''),
                'bearer_token' => (string) $request->input('tw_bearer_token', ''),
                'api_key' => (string) $request->input('tw_api_key', ''),
                'api_secret' => (string) $request->input('tw_api_secret', ''),
                'access_token' => (string) $request->input('tw_access_token', ''),
            ],
            'youtube' => [
                'enabled' => $request->boolean('yt_enabled'),
                'channel_name' => (string) $request->input('yt_channel_name', ''),
                'channel_id' => (string) $request->input('yt_channel_id', ''),
                'api_key' => (string) $request->input('yt_api_key', ''),
                'client_id' => (string) $request->input('yt_client_id', ''),
                'client_secret' => (string) $request->input('yt_client_secret', ''),
            ],
        ];

        $socialService->saveConfig($newConfig, $request->user()?->id);

        return back()->with('success', 'Configuration des APIs Réseaux Sociaux (Facebook, LinkedIn, X/Twitter, YouTube) enregistrée.');
    }

    public function publish(Request $request, SocialPublisherService $socialService): RedirectResponse
    {
        $validated = $request->validate([
            'content_type' => ['required', 'in:post,podcast'],
            'post_id' => ['nullable', 'uuid', 'exists:posts,id'],
            'podcast_id' => ['nullable', 'uuid', 'exists:podcasts,id'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:facebook,linkedin,twitter,youtube'],
            'custom_message' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['content_type'] === 'podcast' && ! empty($validated['podcast_id'])) {
            $podcast = Podcast::findOrFail($validated['podcast_id']);
            $shares = $socialService->publishPodcast(
                $podcast,
                $validated['platforms'],
                $validated['custom_message'] ?? null,
                $request->user()?->id
            );

            return back()->with('success', count($shares)." publication(s) diffusée(s) pour le podcast « {$podcast->title} ».");
        }

        $request->validate(['post_id' => ['required', 'uuid', 'exists:posts,id']]);
        $post = Post::findOrFail($validated['post_id']);

        $shares = $socialService->publishPost(
            $post,
            $validated['platforms'],
            $validated['custom_message'] ?? null,
            $request->user()?->id
        );

        $platformsList = implode(', ', array_map('ucfirst', $validated['platforms']));

        return back()->with('success', "Article « {$post->title} » diffusé avec succès sur : {$platformsList}.");
    }
}
