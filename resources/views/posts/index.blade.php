@extends('layouts.workspace')

@section('agent-chat')
<div class="flex flex-col h-full relative p-6 bg-slate-50">
    <!-- Header -->
    <header class="pb-4 border-b border-slate-200 mb-6 shrink-0 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Approval Inbox</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Review &amp; approve Agent drafts (Level 1)</p>
        </div>
        <div class="flex gap-2">
            <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-indigo-200">{{ $posts->total() }} Pending</span>
        </div>
    </header>

    <!-- Filters -->
    <div class="flex items-center gap-2 mb-6 shrink-0 overflow-x-auto custom-scrollbar pb-2">
        <a href="#" class="px-4 py-1.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-200">Needs Review</a>
        <a href="#" class="px-4 py-1.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-sm font-semibold shadow-sm transition">Approved</a>
        <a href="#" class="px-4 py-1.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-sm font-semibold shadow-sm transition">Failed</a>
    </div>

    <!-- Inbox List (Left Pane) -->
    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3">
        
        @forelse($posts as $post)
        <!-- Inbox Item Example -->
        <a href="{{ route('posts.show', $post) }}" class="block bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-indigo-300 transition group cursor-pointer relative">
            @if($loop->first)
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 rounded-l-xl"></div>
            @endif
            
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    @if($post->platform === 'linkedin')
                        <span class="bg-[#0A66C2]/10 text-[#0A66C2] text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">LinkedIn</span>
                    @elseif($post->platform === 'twitter')
                        <span class="bg-black/10 text-slate-900 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">X / Twitter</span>
                    @else
                        <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ $post->platform }}</span>
                    @endif
                    <span class="text-xs text-slate-400 font-medium">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                <div class="w-2 h-2 rounded-full {{ $post->status === 'draft' ? 'bg-amber-400' : 'bg-emerald-400' }}"></div>
            </div>
            
            <h3 class="font-bold text-slate-900 text-sm mb-1 truncate group-hover:text-indigo-600 transition">{{ $post->topic }}</h3>
            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed font-medium">{{ $post->caption }}</p>
        </a>
        @empty
        <div class="p-8 text-center bg-white border border-slate-200 border-dashed rounded-xl">
            <span class="text-4xl">🎉</span>
            <p class="text-sm font-bold text-slate-800 mt-3">Inbox Zero</p>
            <p class="text-xs text-slate-500 mt-1">All posts have been reviewed!</p>
        </div>
        @endforelse

    </div>
    
    <!-- Bulk Actions -->
    @if($posts->total() > 0)
    <div class="shrink-0 mt-6 pt-4 border-t border-slate-200 bg-slate-50 relative z-10 flex gap-3">
        <button class="flex-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all text-sm">
            Select All
        </button>
        <form action="{{ route('posts.bulk-approve') }}" method="POST" class="flex-1">
            @csrf
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-emerald-200 transition-all text-sm">
                Approve All
            </button>
        </form>
    </div>
    @endif
    
    <!-- Pagination -->
    <div class="mt-4 shrink-0">
        {{ $posts->links('pagination::tailwind') }}
    </div>
</div>
@endsection

@section('artifacts')
<div class="max-w-3xl mx-auto h-full flex flex-col justify-center items-center">
    <!-- Placeholder for when a post isn't actively selected in a true SPA -->
    <div class="bg-white border text-center border-slate-200 shadow-sm rounded-2xl p-12 max-w-sm w-full mx-auto">
        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-inner border border-indigo-100">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Preview Mode</h3>
        <p class="text-sm text-slate-500 font-medium mb-6">Select a draft from your Approval Inbox on the left to see exactly how it will look natively before authorizing it.</p>
        
        <div class="inline-flex gap-2">
            <div class="w-2 h-2 rounded-full bg-slate-300 animate-pulse"></div>
            <div class="w-2 h-2 rounded-full bg-slate-300 animate-pulse delay-75"></div>
            <div class="w-2 h-2 rounded-full bg-slate-300 animate-pulse delay-150"></div>
        </div>
    </div>
</div>
@endsection
