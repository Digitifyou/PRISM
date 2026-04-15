<?php

namespace App\Services\Level2;

use App\Models\Post;
use Illuminate\Support\Facades\Http;

class LinkedInPublisher
{
    private string $accessToken;
    private string $personId;

    public function __construct()
    {
        $this->accessToken = config('services.linkedin.access_token', '');
        $this->personId    = config('services.linkedin.person_id', '');
    }

    public function publish(Post $post): string
    {
        if (!$this->accessToken || !$this->personId) {
            throw new \RuntimeException('LinkedIn credentials not configured. Add them in Settings.');
        }

        $author = "urn:li:person:{$this->personId}";

        $shareContent = [
            'shareCommentary'    => ['text' => $post->caption],
            'shareMediaCategory' => 'NONE',
        ];

        // If image, upload it first then attach
        if ($post->image_url) {
            $assetUrn = $this->uploadImage($post->image_url, $author);
            $shareContent['shareMediaCategory'] = 'IMAGE';
            $shareContent['media'] = [[
                'status'      => 'READY',
                'description' => ['text' => $post->topic],
                'media'       => $assetUrn,
                'title'       => ['text' => $post->topic],
            ]];
        }

        $payload = [
            'author'          => $author,
            'lifecycleState'  => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => $shareContent,
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'X-Restli-Protocol-Version' => '2.0.0',
                'Content-Type'              => 'application/json',
            ])
            ->post('https://api.linkedin.com/v2/ugcPosts', $payload);

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->body();
            throw new \RuntimeException("LinkedIn API error: {$error}");
        }

        // LinkedIn returns the post URN in the x-restli-id header
        return $response->header('x-restli-id') ?? $response->json('id') ?? 'unknown';
    }

    private function uploadImage(string $imageUrl, string $owner): string
    {
        // Step 1: Register upload
        $registerResponse = Http::withToken($this->accessToken)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
                'registerUploadRequest' => [
                    'recipes'              => ['urn:li:digitalmediaRecipe:feedshare-image'],
                    'owner'                => $owner,
                    'serviceRelationships' => [[
                        'relationshipType' => 'OWNER',
                        'identifier'       => 'urn:li:userGeneratedContent',
                    ]],
                ],
            ]);

        if ($registerResponse->failed()) {
            throw new \RuntimeException('LinkedIn asset registration failed: ' . $registerResponse->body());
        }

        $uploadUrl = data_get(
            $registerResponse->json(),
            'value.uploadMechanism.com\\.linkedin\\.digitalmedia\\.uploading\\.MediaUploadHttpRequest.uploadUrl'
        );
        $assetUrn = $registerResponse->json('value.asset');

        if (!$uploadUrl || !$assetUrn) {
            throw new \RuntimeException('LinkedIn asset registration response missing uploadUrl or asset URN.');
        }

        // Step 2: Upload binary
        $imageContent = Http::timeout(30)->get($imageUrl)->body();

        Http::withToken($this->accessToken)
            ->withBody($imageContent, 'application/octet-stream')
            ->put($uploadUrl);

        return $assetUrn;
    }
}
