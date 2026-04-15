<?php

namespace App\Services\Level2;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class InsightService
{
    public function fetch(Post $post): array
    {
        if (!$post->platform_post_id) {
            throw new \RuntimeException("Post #{$post->id} has no platform_post_id. Publish it first.");
        }

        return match ($post->platform) {
            'facebook'  => $this->fetchFacebook($post),
            'instagram' => $this->fetchInstagram($post),
            'linkedin'  => $this->fetchLinkedIn($post),
            default     => throw new \InvalidArgumentException("Unknown platform: {$post->platform}"),
        };
    }

    private function fetchFacebook(Post $post): array
    {
        $token    = config('services.facebook.access_token');
        $postId   = $post->platform_post_id;

        $response = Http::get("https://graph.facebook.com/v19.0/{$postId}/insights", [
            'metric'       => 'post_impressions,post_reach,post_engaged_users',
            'access_token' => $token,
        ]);

        $data = collect($response->json('data') ?? []);

        $impressions = $data->firstWhere('name', 'post_impressions')['values'][0]['value'] ?? 0;
        $reach       = $data->firstWhere('name', 'post_reach')['values'][0]['value'] ?? 0;
        $engaged     = $data->firstWhere('name', 'post_engaged_users')['values'][0]['value'] ?? 0;

        // Fetch likes/comments/shares separately
        $reactResponse = Http::get("https://graph.facebook.com/v19.0/{$postId}", [
            'fields'       => 'likes.summary(true),comments.summary(true),shares',
            'access_token' => $token,
        ]);

        $likes    = $reactResponse->json('likes.summary.total_count') ?? 0;
        $comments = $reactResponse->json('comments.summary.total_count') ?? 0;
        $shares   = $reactResponse->json('shares.count') ?? 0;

        return [
            'likes'           => $likes,
            'comments'        => $comments,
            'shares'          => $shares,
            'reach'           => $reach,
            'impressions'     => $impressions,
            'engagement_rate' => $reach > 0 ? round(($engaged / $reach) * 100, 2) : 0,
        ];
    }

    private function fetchInstagram(Post $post): array
    {
        $token  = config('services.instagram.access_token');
        $postId = $post->platform_post_id;

        $response = Http::get("https://graph.facebook.com/v19.0/{$postId}/insights", [
            'metric'       => 'impressions,reach,likes,comments,shares,saved',
            'access_token' => $token,
        ]);

        $data = collect($response->json('data') ?? []);

        $impressions = $data->firstWhere('name', 'impressions')['values'][0]['value'] ?? 0;
        $reach       = $data->firstWhere('name', 'reach')['values'][0]['value'] ?? 0;
        $likes       = $data->firstWhere('name', 'likes')['values'][0]['value'] ?? 0;
        $comments    = $data->firstWhere('name', 'comments')['values'][0]['value'] ?? 0;
        $shares      = $data->firstWhere('name', 'shares')['values'][0]['value'] ?? 0;

        return [
            'likes'           => $likes,
            'comments'        => $comments,
            'shares'          => $shares,
            'reach'           => $reach,
            'impressions'     => $impressions,
            'engagement_rate' => $reach > 0 ? round((($likes + $comments + $shares) / $reach) * 100, 2) : 0,
        ];
    }

    private function fetchLinkedIn(Post $post): array
    {
        $token  = config('services.linkedin.access_token');
        $postId = $post->platform_post_id;

        $response = Http::withToken($token)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->get("https://api.linkedin.com/v2/socialActions/{$postId}");

        $likes    = $response->json('likesSummary.totalLikes') ?? 0;
        $comments = $response->json('commentsSummary.totalFirstLevelComments') ?? 0;
        $shares   = $response->json('shareStatistics.shareCount') ?? 0;

        // Fetch impressions from analytics API
        $statsResponse = Http::withToken($token)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->get('https://api.linkedin.com/v2/organizationalEntityShareStatistics', [
                'q'    => 'organizationalEntity',
                'ugcPosts' => "List({$postId})",
            ]);

        $impressions = $statsResponse->json('elements.0.totalShareStatistics.impressionCount') ?? 0;
        $reach       = $statsResponse->json('elements.0.totalShareStatistics.uniqueImpressionsCount') ?? 0;

        return [
            'likes'           => $likes,
            'comments'        => $comments,
            'shares'          => $shares,
            'reach'           => $reach,
            'impressions'     => $impressions,
            'engagement_rate' => $impressions > 0 ? round((($likes + $comments + $shares) / $impressions) * 100, 2) : 0,
        ];
    }
}
