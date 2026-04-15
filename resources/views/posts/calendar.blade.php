@extends('layouts.workspace')

@section('agent-chat')
<div class="flex flex-col h-full bg-slate-50">
    <div class="px-6 py-4 border-b border-slate-200 bg-white">
        <h2 class="text-xl font-bold text-slate-900">Distribution Strategy</h2>
        <p class="text-sm text-slate-500 mt-1">Manage your content calendar and optimal publishing times.</p>
    </div>

    <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Scheduled</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $posts->where('status', 'approved')->count() }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Published</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $posts->where('status', 'published')->count() }}</p>
            </div>
        </div>

        <!-- Strategy Nudge -->
        <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200">
            <h3 class="font-bold flex items-center gap-2 mb-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Optimal Timing Analysis
            </h3>
            <p class="text-indigo-100 text-sm leading-relaxed mb-4">
                Based on your audience in the <strong>{{ $posts->first()?->contentPlan?->client?->industry ?? 'Niche' }}</strong> sector, the best time to post today is:
            </p>
            <div class="bg-white/10 rounded-xl p-3 border border-white/20 backdrop-blur-sm">
                <p class="text-center font-mono font-bold text-lg">6:45 PM – 7:30 PM</p>
            </div>
        </div>

        <!-- Automation Rules -->
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <h4 class="text-xs font-bold text-slate-800 uppercase mb-4">Calendar Rules</h4>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-2 h-2 rounded-full bg-emerald-400 shrink-0"></div>
                    <p class="text-xs text-slate-600">Auto-publish is active for all <strong>approved</strong> posts.</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-2 h-2 rounded-full bg-amber-400 shrink-0"></div>
                    <p class="text-xs text-slate-600">Posts without times wait for manual approval.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('artifacts')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-full flex flex-col">
    <!-- Calendar Header -->
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white flex-none">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Content Calendar</h2>
            <p class="text-xs text-slate-500 font-medium">Visualizing your distribution funnel</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400 transition-colors border border-slate-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <span class="text-sm font-bold text-slate-700 px-2">{{ now()->format('F Y') }}</span>
            <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400 transition-colors border border-slate-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="flex-1 overflow-auto bg-slate-50/30">
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="py-2 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest border-r last:border-0 border-slate-200">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 auto-rows-[160px] bg-slate-200/40 gap-px">
            @php
                $startOfMonth = now()->startOfMonth();
                $endOfMonth = now()->endOfMonth();
                $daysInMonth = now()->daysInMonth;
                $startDay = $startOfMonth->dayOfWeek; // 0 (Sun) - 6 (Sat)
            @endphp

            {{-- Empty cells before month starts --}}
            @for ($i = 0; $i < $startDay; $i++)
                <div class="bg-white/50 backdrop-blur-[2px]"></div>
            @endfor

            {{-- Actual Days --}}
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = now()->setDay($day)->format('Y-m-d');
                    $dayPosts = $posts->filter(function($p) use ($date) {
                        return ($p->scheduled_at?->format('Y-m-d') === $date) || ($p->published_at?->format('Y-m-d') === $date);
                    });
                @endphp
                <div class="bg-white p-2 flex flex-col gap-2 hover:bg-slate-50 transition-colors group relative">
                    <span class="text-xs font-bold {{ now()->day == $day ? 'text-indigo-600 h-6 w-6 flex items-center justify-center bg-indigo-50 rounded-full' : 'text-slate-400' }}">
                        {{ $day }}
                    </span>

                    <div class="flex-1 flex flex-col gap-1.5 overflow-hidden">
                        @foreach($dayPosts as $post)
                            <a href="{{ route('posts.show', $post) }}" class="block px-2 py-1.5 rounded-md border border-slate-200 bg-white shadow-sm hover:border-indigo-300 transition-all overflow-hidden group/item">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $post->isPublished() ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                    <span class="text-[9px] font-bold truncate text-slate-700 uppercase">{{ $post->platform }}</span>
                                </div>
                                <p class="text-[10px] text-slate-500 leading-tight line-clamp-2 italic font-serif">"{{ $post->topic }}"</p>
                            </a>
                        @endforeach
                    </div>
                    
                    @if(now()->day == $day)
                        <div class="absolute inset-x-0 top-0 h-0.5 bg-indigo-500"></div>
                    @endif
                </div>
            @endfor

            {{-- Empty cells after month ends --}}
            @php $remainingCells = (7 - (($startDay + $daysInMonth) % 7)) % 7; @endphp
            @for ($i = 0; $i < $remainingCells; $i++)
                <div class="bg-white/50 backdrop-blur-[2px]"></div>
            @endfor
        </div>
    </div>
</div>
@endsection
