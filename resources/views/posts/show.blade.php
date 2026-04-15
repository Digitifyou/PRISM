@extends('layouts.app')
@section('title', 'Post Detail')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('posts.index', ['status' => $post->status]) }}"
       class="text-gray-400 hover:text-gray-600 text-sm">← Back to Posts</a>
    <span class="text-gray-300">/</span>
    <span class="text-sm text-gray-600 font-medium">{{ $post->topic }}</span>
</div>

@php
$platformColors = ['facebook' => 'bg-blue-100 text-blue-700', 'instagram' => 'bg-pink-100 text-pink-700', 'linkedin' => 'bg-sky-100 text-sky-700'];
$statusColors   = ['draft' => 'bg-yellow-100 text-yellow-700', 'approved' => 'bg-emerald-100 text-emerald-700', 'published' => 'bg-blue-100 text-blue-700', 'failed' => 'bg-red-100 text-red-700'];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Image + Actions --}}
    <div class="space-y-4">
        {{-- Image Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Post Image</h3>
            @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="{{ $post->topic }}"
                 class="w-full rounded-lg border border-gray-100 object-cover aspect-square mb-3">
            @else
            <div class="w-full aspect-square bg-gray-100 rounded-lg flex flex-col items-center justify-center border border-dashed border-gray-300 mb-3">
                <span class="text-4xl mb-2">🖼</span>
                <p class="text-xs text-gray-400">No image yet</p>
                @if($post->status === 'draft')
                <p class="text-xs text-gray-400">Image may still be generating…</p>
                @endif
            </div>
            @endif

            @if($post->image_prompt)
            <p class="text-xs text-gray-400 italic mb-3">{{ Str::limit($post->image_prompt, 100) }}</p>
            @endif

            <form action="{{ route('posts.regenerate-image', $post) }}" method="POST">
                @csrf
                <button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium px-4 py-2 rounded-lg transition-colors">
                    ↻ Regenerate Image
                </button>
            </form>
        </div>

        {{-- Status + Actions Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $platformColors[$post->platform] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($post->platform) }}
                </span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$post->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($post->status) }}
                </span>
                <span class="text-xs text-gray-400 capitalize">{{ $post->contentPlan->ai_provider ?? '' }}</span>
            </div>

            @if($post->published_at)
            <p class="text-xs text-gray-500">Published: {{ $post->published_at->format('M j, Y g:i A') }}</p>
            @endif
            @if($post->platform_post_id)
            <p class="text-xs text-gray-400">Platform ID: <code class="bg-gray-100 px-1 rounded">{{ $post->platform_post_id }}</code></p>
            @endif

            @if($post->status === 'draft' || $post->status === 'failed')
            <form action="{{ route('posts.approve', $post) }}" method="POST" class="space-y-2">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Schedule (optional)</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : '' }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                    Approve & Publish
                </button>
            </form>
            @elseif($post->status === 'approved')
            <form action="{{ route('posts.reject', $post) }}" method="POST">
                @csrf @method('PATCH')
                <button class="w-full border border-gray-200 text-gray-500 hover:text-gray-700 text-sm font-medium py-2 rounded-lg transition-colors">
                    Revoke Approval
                </button>
            </form>
            @endif

            <form action="{{ route('posts.destroy', $post) }}" method="POST"
                  onsubmit="return confirm('Delete this post permanently?')">
                @csrf @method('DELETE')
                <button class="w-full text-red-400 hover:text-red-600 text-xs py-1">Delete Post</button>
            </form>
        </div>

        {{-- Insights (if any) --}}
        @if($post->insights->isNotEmpty())
        @php $insight = $post->insights->sortByDesc('fetched_at')->first(); @endphp
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Performance</h3>
            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="bg-pink-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-pink-600">{{ number_format($insight->likes) }}</p>
                    <p class="text-xs text-gray-500">Likes</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-blue-600">{{ number_format($insight->reach) }}</p>
                    <p class="text-xs text-gray-500">Reach</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-purple-600">{{ number_format($insight->impressions) }}</p>
                    <p class="text-xs text-gray-500">Impressions</p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-3">
                    <p class="text-xl font-bold text-emerald-600">{{ $insight->engagement_rate }}%</p>
                    <p class="text-xs text-gray-500">Engagement</p>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2 text-center">Updated {{ $insight->fetched_at->diffForHumans() }}</p>
        </div>
        @endif
    </div>

    {{-- Center: Edit Caption --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Edit Post</h3>
        <form action="{{ route('posts.update', $post) }}" method="POST" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Topic</label>
                <input type="text" name="topic" value="{{ old('topic', $post->topic) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                @error('topic') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-indigo-700 mb-1 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Poster Text (On Image)
                    </span>
                    <button type="button" id="generatePosterBtn" class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-bold uppercase hover:bg-indigo-200 transition-colors flex items-center gap-1.5">
                        <span id="posterLoader" class="hidden animate-spin h-2 w-2 border-2 border-indigo-700 border-t-transparent rounded-full"></span>
                        ✨ Magic Write
                    </button>
                </label>
                <textarea name="poster_copy" rows="2"
                          class="w-full border border-indigo-200 bg-indigo-50/30 rounded-lg px-3 py-2 text-sm font-bold text-indigo-900 focus:outline-none focus:ring-2 focus:ring-brand resize-none placeholder-indigo-300">{{ old('poster_copy', $post->poster_copy) }}</textarea>
                <div class="flex justify-between items-center mt-1">
                    <p class="text-[10px] text-indigo-400 italic">Short, punchy text intended for the graphic itself.</p>
                    <span id="charCountPoster" class="text-[10px] text-indigo-300 font-medium">0 chars</span>
                </div>
                @error('poster_copy') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    Social Caption
                </label>
                <textarea name="caption" rows="10"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none leading-relaxed">{{ old('caption', $post->caption) }}</textarea>
                @error('caption') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-gray-400">
                <span id="charCount">0 characters</span>
                <span>{{ $post->platform === 'instagram' ? 'Max 2,200' : ($post->platform === 'linkedin' ? 'Max 3,000' : 'Max 63,206') }}</span>
            </div>

            <button type="submit"
                    class="w-full bg-brand hover:bg-indigo-600 text-white font-medium py-2 rounded-lg text-sm transition-colors">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Right: Research & Strategy --}}
    <div class="space-y-4">
        {{-- Strategy Notes --}}
        @if(!empty($strategyNotes))
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Strategy</h3>
            <dl class="space-y-2">
                @foreach($strategyNotes as $key => $value)
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">{{ str_replace('_', ' ', $key) }}</dt>
                    <dd class="text-sm text-gray-700">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
        @endif

        {{-- Research Data --}}
        @if($post->research_data)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Research Data</h3>
            <div class="bg-gray-50 rounded-lg p-3 max-h-80 overflow-y-auto">
                <p class="text-xs text-gray-600 whitespace-pre-wrap leading-relaxed">{{ $post->research_data }}</p>
            </div>
        </div>
        @endif

        {{-- Failure reason --}}
        @if($post->status === 'failed' && $post->failure_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
            <h3 class="font-semibold text-red-700 mb-2">Publish Error</h3>
            <p class="text-sm text-red-600">{{ $post->failure_reason }}</p>
        </div>
        @endif

        {{-- Meta --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Plan</dt>
                    <dd class="text-gray-700 font-medium">{{ $post->contentPlan->niche ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">AI Provider</dt>
                    <dd class="text-gray-700 capitalize">{{ $post->contentPlan->ai_provider ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Created</dt>
                    <dd class="text-gray-700">{{ $post->created_at->format('M j, Y') }}</dd>
                </div>
                @if($post->scheduled_at)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Scheduled</dt>
                    <dd class="text-amber-600">{{ $post->scheduled_at->format('M j, Y g:i A') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>

<script>
    const setupCounter = (selector, counterId) => {
        const textarea = document.querySelector(`textarea[name="${selector}"]`);
        const charCount = document.getElementById(counterId);
        if (!textarea || !charCount) return;
        const update = () => charCount.textContent = textarea.value.length + ' chars';
        textarea.addEventListener('input', update);
        update();
    };

    setupCounter('caption', 'charCount');
    setupCounter('poster_copy', 'charCountPoster');

    // Magic Poster Copy Generator
    const generateBtn = document.getElementById('generatePosterBtn');
    const posterTextarea = document.querySelector('textarea[name="poster_copy"]');
    const loader = document.getElementById('posterLoader');

    if (generateBtn && posterTextarea) {
        generateBtn.addEventListener('click', async () => {
            generateBtn.disabled = true;
            loader.classList.remove('hidden');

            try {
                const response = await fetch('{{ route('posts.generate-poster-copy', $post) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    posterTextarea.value = data.poster_copy;
                    // Trigger character count update
                    posterTextarea.dispatchEvent(new Event('input'));
                } else {
                    alert('Failed to generate poster copy.');
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred.');
            } finally {
                generateBtn.disabled = false;
                loader.classList.add('hidden');
            }
        });
    }
</script>
@endsection
