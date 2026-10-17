<?php

namespace App\Services;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialShare;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialPublisherService
{
    public const SETTING_KEY = 'social_api_config';

    public function getConfig(): array
    {
        $defaults = [
            'mode' => 'sandbox', // 'sandbox' ou 'live'
            'auto_publish_on_post' => false,
            'default_template' => "📰 {title}\n\n{excerpt}\n\n👉 Lire l'intégralité sur CIOMAG : {url}\n{hashtags}",
            'facebook' => [
                'enabled' => true,
                'page_name' => 'CIOMAG Officiel',
                'page_id' => '',
                'access_token' => '',
                'api_version' => 'v21.0',
            ],
            'linkedin' => [
                'enabled' => true,
                'organization_name' => 'CIOMAG - Le Magazine des DSI',
                'organization_urn' => 'urn:li:organization:ciomag',
                'access_token' => '',
            ],
            'twitter' => [
                'enabled' => true,
                'handle' => '@CIOMAG_Officiel',
                'bearer_token' => '',
                'api_key' => '',
                'api_secret' => '',
                'access_token' => '',
            ],
            'youtube' => [
                'enabled' => true,
                'channel_name' => 'CIOMAG WebTV & Podcasts',
                'channel_id' => '',
                'api_key' => '',
                'client_id' => '',
                'client_secret' => '',
            ],
        ];

        $saved = Setting::getValue(self::SETTING_KEY, []);

        return array_replace_recursive($defaults, is_array($saved) ? $saved : []);
    }

    public function saveConfig(array $newConfig, ?string $userId = null): array
    {
        $merged = array_replace_recursive($this->getConfig(), $newConfig);

        Setting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => $merged,
                'description' => 'Paramétrage des APIs Réseaux Sociaux (Facebook, LinkedIn, X/Twitter, YouTube)',
                'updated_by' => $userId,
                'updated_at' => now(),
            ]
        );

        return $merged;
    }

    public function formatPostMessage(Post $post, ?string $customMessage = null): string
    {
        if ($customMessage !== null && trim($customMessage) !== '') {
            return trim($customMessage);
        }

        $config = $this->getConfig();
        $template = $config['default_template'] ?? "{title} - {url}";

        $post->loadMissing('tags');
        $hashtags = $post->tags
            ->take(4)
            ->map(fn ($tag) => '#' . Str::studly(Str::ascii($tag->name)))
            ->implode(' ');

        if ($hashtags === '') {
            $hashtags = '#CIOMAG #DSI #TransformationDigitale #Tech';
        }

        return strtr($template, [
            '{title}' => $post->title,
            '{excerpt}' => Str::limit((string) ($post->excerpt ?: strip_tags($post->content)), 180),
            '{url}' => $post->url(),
            '{hashtags}' => $hashtags,
        ]);
    }

    /**
     * Publie un article sur les plateformes sélectionnées (Facebook, LinkedIn, Twitter/X).
     *
     * @param  array<int, string>  $platforms
     * @return array<int, SocialShare>
     */
    public function publishPost(Post $post, array $platforms, ?string $customMessage = null, ?string $userId = null): array
    {
        $config = $this->getConfig();
        $message = $this->formatPostMessage($post, $customMessage);
        $targetUrl = $post->url();
        $results = [];

        foreach ($platforms as $platform) {
            if (! in_array($platform, ['facebook', 'linkedin', 'twitter'], true)) {
                continue;
            }

            $results[] = $this->dispatchToPlatform(
                platform: $platform,
                message: $message,
                targetUrl: $targetUrl,
                config: $config,
                postId: $post->id,
                podcastId: null,
                userId: $userId
            );
        }

        return $results;
    }

    /**
     * Prépare ou publie un épisode de podcast sur les réseaux sociaux (YouTube, Facebook, LinkedIn, Twitter/X).
     *
     * @param  array<int, string>  $platforms
     * @return array<int, SocialShare>
     */
    public function publishPodcast(Podcast $podcast, array $platforms, ?string $customMessage = null, ?string $userId = null): array
    {
        $config = $this->getConfig();
        $targetUrl = route('magazine.podcasts.show', $podcast->slug);
        $message = $customMessage ?: "🎙️ Nouveau Podcast CIOMAG : {$podcast->title}\nAvec {$podcast->guest_name} ({$podcast->guest_role})\n👉 Écouter & Voir : {$targetUrl} #CIOMAG #Podcast #DSI";
        $results = [];

        foreach ($platforms as $platform) {
            if (! in_array($platform, ['facebook', 'linkedin', 'twitter', 'youtube'], true)) {
                continue;
            }

            $results[] = $this->dispatchToPlatform(
                platform: $platform,
                message: $message,
                targetUrl: $targetUrl,
                config: $config,
                postId: null,
                podcastId: $podcast->id,
                userId: $userId
            );
        }

        return $results;
    }

    private function dispatchToPlatform(
        string $platform,
        string $message,
        string $targetUrl,
        array $config,
        ?string $postId,
        ?string $podcastId,
        ?string $userId
    ): SocialShare {
        $isLive = ($config['mode'] ?? 'sandbox') === 'live';
        $platformConfig = $config[$platform] ?? [];

        if ($isLive && ! empty($platformConfig['access_token'] ?? $platformConfig['bearer_token'] ?? null)) {
            try {
                $apiResult = match ($platform) {
                    'facebook' => $this->callFacebookApi($platformConfig, $message, $targetUrl),
                    'linkedin' => $this->callLinkedinApi($platformConfig, $message, $targetUrl),
                    'twitter' => $this->callTwitterApi($platformConfig, $message, $targetUrl),
                    default => $this->simulateApiCall($platform, $message, $targetUrl),
                };

                return SocialShare::create([
                    'post_id' => $postId,
                    'podcast_id' => $podcastId,
                    'platform' => $platform,
                    'status' => $apiResult['success'] ? 'published' : 'failed',
                    'message' => $message,
                    'target_url' => $targetUrl,
                    'external_post_id' => $apiResult['external_id'] ?? null,
                    'external_url' => $apiResult['external_url'] ?? null,
                    'response_payload' => $apiResult['payload'] ?? [],
                    'shared_by' => $userId,
                    'shared_at' => now(),
                ]);
            } catch (\Throwable $e) {
                return SocialShare::create([
                    'post_id' => $postId,
                    'podcast_id' => $podcastId,
                    'platform' => $platform,
                    'status' => 'failed',
                    'message' => $message,
                    'target_url' => $targetUrl,
                    'response_payload' => ['error' => $e->getMessage()],
                    'shared_by' => $userId,
                    'shared_at' => now(),
                ]);
            }
        }

        // Mode Sandbox / Simulation avec génération d'ID et URL de vérification
        $sim = $this->simulateApiCall($platform, $message, $targetUrl);

        return SocialShare::create([
            'post_id' => $postId,
            'podcast_id' => $podcastId,
            'platform' => $platform,
            'status' => 'simulated',
            'message' => $message,
            'target_url' => $targetUrl,
            'external_post_id' => $sim['external_id'],
            'external_url' => $sim['external_url'],
            'response_payload' => $sim['payload'],
            'shared_by' => $userId,
            'shared_at' => now(),
        ]);
    }

    private function callFacebookApi(array $cfg, string $message, string $targetUrl): array
    {
        $version = $cfg['api_version'] ?? 'v21.0';
        $pageId = $cfg['page_id'] ?? 'me';
        $response = Http::timeout(10)->post("https://graph.facebook.com/{$version}/{$pageId}/feed", [
            'message' => $message,
            'link' => $targetUrl,
            'access_token' => $cfg['access_token'],
        ]);

        $json = $response->json() ?? [];
        $extId = $json['id'] ?? null;

        return [
            'success' => $response->successful(),
            'external_id' => $extId,
            'external_url' => $extId ? "https://www.facebook.com/{$extId}" : null,
            'payload' => $json,
        ];
    }

    private function callLinkedinApi(array $cfg, string $message, string $targetUrl): array
    {
        $authorUrn = $cfg['organization_urn'] ?: 'urn:li:organization:0';
        $response = Http::timeout(10)
            ->withToken($cfg['access_token'])
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->post('https://api.linkedin.com/v2/ugcPosts', [
                'author' => $authorUrn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => ['text' => $message],
                        'shareMediaCategory' => 'ARTICLE',
                        'media' => [[
                            'status' => 'READY',
                            'originalUrl' => $targetUrl,
                        ]],
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ]);

        $json = $response->json() ?? [];
        $extId = $json['id'] ?? null;

        return [
            'success' => $response->successful(),
            'external_id' => $extId,
            'external_url' => $extId ? "https://www.linkedin.com/feed/update/{$extId}" : null,
            'payload' => $json,
        ];
    }

    private function callTwitterApi(array $cfg, string $message, string $targetUrl): array
    {
        $token = $cfg['access_token'] ?: ($cfg['bearer_token'] ?? '');
        $tweetText = Str::limit($message, 250) . "\n" . $targetUrl;

        $response = Http::timeout(10)
            ->withToken($token)
            ->post('https://api.twitter.com/2/tweets', [
                'text' => $tweetText,
            ]);

        $json = $response->json() ?? [];
        $extId = $json['data']['id'] ?? null;

        return [
            'success' => $response->successful(),
            'external_id' => $extId,
            'external_url' => $extId ? "https://x.com/i/web/status/{$extId}" : null,
            'payload' => $json,
        ];
    }

    private function simulateApiCall(string $platform, string $message, string $targetUrl): array
    {
        $shortId = strtoupper(Str::random(10));

        $externalUrl = match ($platform) {
            'facebook' => "https://www.facebook.com/ciomag/posts/{$shortId}",
            'linkedin' => "https://www.linkedin.com/feed/update/urn:li:share:{$shortId}",
            'twitter' => "https://x.com/CIOMAG_Officiel/status/{$shortId}",
            'youtube' => "https://www.youtube.com/watch?v={$shortId}",
            default => $targetUrl,
        };

        return [
            'success' => true,
            'external_id' => "{$platform}_{$shortId}",
            'external_url' => $externalUrl,
            'payload' => [
                'mode' => 'sandbox_simulation',
                'platform' => $platform,
                'timestamp' => now()->toIso8601String(),
                'characters' => mb_strlen($message),
            ],
        ];
    }
}
