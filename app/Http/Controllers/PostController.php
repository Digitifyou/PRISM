<?php

namespace App\Http\Controllers;

use App\Jobs\Level2\GenerateImageJob;
use App\Jobs\Level2\PublishPostJob;
use App\Models\Post;
use App\Services\Ai\AiProviderFactory;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function calendar()
    {
        $posts = Post::whereNotNull('scheduled_at')
            ->orWhereNotNull('published_at')
            ->with('contentPlan.client')
            ->get();

        return view('posts.calendar', compact('posts'));
    }

    public function index(Request $request)
    {
        $status   = $request->get('status', 'draft');
        $platform = $request->get('platform');

        $query = Post::with('contentPlan')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($platform) {
            $query->where('platform', $platform);
        }

        $posts = $query->paginate(12);

        return view('posts.index', [
            'posts'    => $posts,
            'status'   => $status,
            'platform' => $platform,
        ]);
    }

    public function show(Post $post)
    {
        $post->load('contentPlan', 'insights');
        $strategyNotes = json_decode($post->strategy_notes ?? '{}', true) ?? [];

        return view('posts.show', compact('post', 'strategyNotes'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'topic'        => 'required|string|max:255',
            'poster_copy'  => 'nullable|string',
            'caption'      => 'required|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $post->update($data);

        return back()->with('success', 'Post updated successfully.');
    }

    public function approve(Request $request, Post $post)
    {
        $scheduledAt = $request->input('scheduled_at');

        $post->update([
            'status'       => Post::STATUS_APPROVED,
            'scheduled_at' => $scheduledAt ?: null,
            'failure_reason' => null,
        ]);

        if (!$scheduledAt) {
            // Publish immediately
            PublishPostJob::dispatch($post);
            return back()->with('success', 'Post approved and queued for publishing.');
        }

        return back()->with('success', 'Post approved and scheduled for ' . $scheduledAt . '.');
    }

    public function reject(Post $post)
    {
        $post->update(['status' => Post::STATUS_DRAFT, 'scheduled_at' => null]);

        return back()->with('success', 'Post moved back to draft.');
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];

        $posts = Post::whereIn('id', $ids)->where('status', Post::STATUS_DRAFT)->get();

        foreach ($posts as $post) {
            $post->update(['status' => Post::STATUS_APPROVED]);
            PublishPostJob::dispatch($post);
        }

        return back()->with('success', $posts->count() . ' posts approved and queued for publishing.');
    }

    public function regenerateImage(Post $post)
    {
        GenerateImageJob::dispatch($post);

        return back()->with('success', 'Image regeneration queued. Refresh in a moment.');
    }

    public function generatePosterCopy(Post $post)
    {
        $post->load('contentPlan.client');
        $ai = AiProviderFactory::make($post->contentPlan->ai_provider);
        $client = $post->contentPlan->client;

        $prompt = <<<PROMPT
You are an elite, direct-response copywriter and creative director. Your specialty is crafting scroll-stopping, psychological creatives for top-tier brands.

Your task is to write the 'Poster Copy' (the actual text that goes ON the visual graphic) for this post.

### STRATEGIC CONTEXT
- Topic: {$post->topic}
- Audience: {$client->target_audience_demographics}
- Core Goal: {$client->goals}
- Brand Voice: {$client->brand_voice}

### THE PSYCHOLOGY OF THE CREATIVE
A winning graphic has two parts: The 'Thumb-Stopper' and The 'Bridge'. 
1. The MAIN HEADLINE must attack a pain point, challenge a common belief, or invoke intense curiosity. It should be aggressive, visceral, or deeply intriguing. Maximum 2 to 5 words.
2. The SUBHEADLINE must pay off the headline by offering a measurable, tangible outcome or a highly specific value proposition. Maximum 1 short sentence.

### EXAMPLES OF MEDIOCRE VS. ELITE
- Mediocre: "Get Better SEO" / "Learn how our strategies can help you."
- Elite: "YOUR SEO IS DEAD." / "The exact 4-step framework we use to dominate local search."
- Mediocre: "Time Management" / "Use your time to grow your business."
- Elite: "THE TIME IS NOW." / "Achieve measurable growth with Precision Digital Marketing."

### STRICT RULES
1. DO NOT use emojis.
2. DO NOT use corporate jargon.
3. Output exactly two lines, exactly like this:
MAIN HEADLINE: [Your 2-5 word thumb-stopping headline]
SUBHEADLINE: [Your 1-sentence tangible payoff]
PROMPT;

        $posterCopy = $ai->complete($prompt);
        $posterCopy = trim(preg_replace('/^Poster Copy:\s*/i', '', $posterCopy));

        $post->update(['poster_copy' => $posterCopy]);

        return response()->json(['poster_copy' => $posterCopy]);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
}
