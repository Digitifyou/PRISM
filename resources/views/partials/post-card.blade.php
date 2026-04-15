@php
$platformColors = [
    'facebook'  => 'bg-blue-100 text-blue-700',
    'instagram' => 'bg-pink-100 text-pink-700',
    'linkedin'  => 'bg-sky-100 text-sky-700',
];
$statusColors = [
    'draft'     => 'bg-yellow-100 text-yellow-700',
    'approved'  => 'bg-emerald-100 text-emerald-700',
    'published' => 'bg-blue-100 text-blue-700',
    'failed'    => 'bg-red-100 text-red-700',
];
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 mb-4">
    <div class="flex items-start gap-4">

        {{-- Checkbox (drafts only) --}}
        @if(isset($showCheckbox) && $showCheckbox)
        <input type="checkbox" name="ids[]" value="{{ $post->id }}"
               class="mt-2 post-checkbox rounded border-gray-300 text-brand focus:ring-brand flex-shrink-0">
        @endif

        {{-- Image thumbnail --}}
        <div class="flex-shrink-0">
            @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="{{ $post->topic }}"
                 class="w-20 h-20 object-cover rounded-lg border border-gray-100">
            @else
            <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center border border-dashed border-gray-300">
                <span class="text-gray-300 text-2xl">🖼</span>
            </div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            {{-- Header row --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $platformColors[$post->platform] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($post->platform) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$post->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($post->status) }}
                    </span>
                    @if($post->scheduled_at)
                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                        ⏰ {{ $post->scheduled_at->format('M j, g:i A') }}
                    </span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $post->contentPlan->niche ?? '—' }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 capitalize">{{ $post->contentPlan->ai_provider ?? '' }}</span>

                    {{-- View button --}}
                    <a href="{{ route('posts.show', $post) }}"
                       class="text-xs text-brand hover:underline font-medium">View</a>

                    {{-- Approve / Revoke --}}
                    @if($post->status === 'draft')
                    <form action="{{ route('posts.approve', $post) }}" method="POST" class="flex items-center gap-1">
                        @csrf @method('PATCH')
                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium px-3 py-1 rounded-lg transition-colors">
                            Approve
                        </button>
                    </form>
                    @elseif($post->status === 'approved')
                    <form action="{{ route('posts.reject', $post) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 px-2 py-1 rounded-lg">
                            Revoke
                        </button>
                    </form>
                    @elseif($post->status === 'failed')
                    <form action="{{ route('posts.approve', $post) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="text-xs text-orange-500 hover:text-orange-700 border border-orange-200 px-2 py-1 rounded-lg">
                            Retry
                        </button>
                    </form>
                    @endif

                    {{-- Delete --}}
                    <form action="{{ route('posts.destroy', $post) }}" method="POST"
                          onsubmit="return confirm('Delete this post?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600 px-1">✕</button>
                    </form>
                </div>
            </div>

            {{-- Topic --}}
            <p class="text-xs font-semibold text-indigo-600 mb-1">{{ $post->topic }}</p>

            {{-- Caption --}}
            <p class="text-sm text-gray-700 leading-relaxed line-clamp-3">{{ $post->caption }}</p>

            {{-- Failure reason --}}
            @if($post->status === 'failed' && $post->failure_reason)
            <p class="text-xs text-red-500 mt-2 bg-red-50 rounded p-2">
                ⚠ {{ Str::limit($post->failure_reason, 120) }}
            </p>
            @endif
        </div>
    </div>
</div>
