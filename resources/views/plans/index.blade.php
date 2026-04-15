@extends('layouts.workspace')

@section('agent-chat')
<div class="flex flex-col h-full relative p-6 bg-slate-50">
    <!-- Header -->
    <header class="pb-4 border-b border-slate-200 mb-6 shrink-0">
        <h1 class="text-2xl font-bold text-slate-900">Content Plans</h1>
        <p class="text-sm text-slate-500 mt-1 font-medium">Create a strategy & let the AI generate posts.</p>
    </header>

    @if(!$activeClient || $activePillars->isEmpty())
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl shadow-sm text-sm font-bold flex flex-col gap-2">
            <p class="flex items-center gap-2"><svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Missing Phase 1 Strategy Context.</p>
            <p class="text-xs font-medium">You must establish a Client Identity and at least one Content Pillar in Phase 1 before the AI can generate a Content Plan.</p>
        </div>
    @else
        <!-- Configure Form -->
        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 pb-4">
            <form action="{{ route('plans.store') }}" method="POST" id="planForm" class="space-y-6">
                @csrf
                <input type="hidden" name="client_id" value="{{ $activeClient->id }}">

                {{-- Client Locked Context (Read-Only) --}}
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-3 opacity-20">
                        <svg class="w-16 h-16 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <label class="block text-xs font-bold text-emerald-700 mb-1 uppercase tracking-wider relative z-10">Active Target Context</label>
                    <p class="text-sm font-bold text-slate-800 relative z-10">{{ $activeClient->name }}</p>
                    <p class="text-[11px] font-medium text-slate-500 relative z-10">The AI will use this client's specific Brand Voice and pain points.</p>
                </div>

                {{-- Strategic Pillar Selector --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        Strategic Content Pillar
                        <span class="text-[10px] text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Required</span>
                    </label>
                    <select name="client_pillar_id" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm appearance-none cursor-pointer" required>
                        @foreach($activePillars as $pillar)
                            <option value="{{ $pillar->id }}">{{ $pillar->title }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400" style="margin-top: 28px;">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">The AI will strictly follow the instructions defined in this Phase 1 pillar.</p>
                    @error('client_pillar_id') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Frequency --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Posting Frequency Target</label>
                    <div class="relative">
                        <select name="frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm appearance-none cursor-pointer">
                            <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Weekly Schedule (4 posts)</option>
                            <option value="daily"  {{ old('frequency') == 'daily'  ? 'selected' : '' }}>Daily Schedule (7 posts)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                </div>

                {{-- Platforms Grid --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Publishing Platforms</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['linkedin' => 'LinkedIn', 'twitter' => 'Twitter / X', 'facebook' => 'Facebook', 'instagram' => 'Instagram'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer bg-white border border-slate-200 p-3 rounded-xl shadow-sm hover:border-indigo-300 transition">
                            <input type="checkbox" name="platforms[]" value="{{ $value }}"
                                   {{ in_array($value, old('platforms', ['linkedin'])) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('platforms') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>

                {{-- AI Provider Selector --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">AI Agent Provider</label>
                    <div class="space-y-3">
                        @php $providers = ['openai' => 'OpenAI (GPT-4)', 'anthropic' => 'Anthropic (Claude 3)', 'gemini' => 'Google Gemini']; @endphp
                        @foreach($providers as $value => $label)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 cursor-pointer shadow-sm hover:bg-slate-50 transition relative group
                                      {{ old('ai_provider', config('services.ai_provider')) == $value ? 'border-indigo-500 bg-indigo-50/50' : 'bg-white' }}">
                            <input type="radio" name="ai_provider" value="{{ $value }}"
                                   {{ old('ai_provider', config('services.ai_provider')) == $value ? 'checked' : '' }}
                                   class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $label }}</p>
                                <p class="text-[11px] text-slate-500 font-medium">Default generation model</p>
                            </div>
                            @if(old('ai_provider', config('services.ai_provider')) == $value)
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    @error('ai_provider') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </form>
        </div>

        <!-- Sticky Bottom Actions -->
        <div class="shrink-0 mt-2 pt-4 border-t border-slate-200 bg-slate-50 relative z-10">
            <button type="submit" form="planForm" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2 cursor-pointer">
                Generate Targeted Strategy
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
        </div>
    @endif
</div>
@endsection

@section('artifacts')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Artifact Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600 border border-emerald-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Active Content Strategies</h2>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">{{ $plans->count() }} Plans Generating Posts</p>
            </div>
        </div>
    </div>

    <!-- Generated Plans List (Right Pane Artifacts) -->
    <div class="grid gap-4">
        @forelse($plans as $plan)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative group transition hover:shadow-md hover:border-indigo-200">
            <div class="p-5 flex flex-col md:flex-row md:items-start justify-between gap-4">
                
                {{-- Plan Details --}}
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition">{{ $plan->niche }}</h3>
                        <span class="bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Active</span>
                    </div>
                    
                    <p class="text-xs font-medium text-slate-500 mb-3">
                        <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ ucfirst($plan->frequency) }}</span>
                        <span class="mx-1.5 text-slate-300">&bull;</span>
                        <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg> {{ implode(', ', array_map('ucfirst', $plan->platforms)) }}</span>
                        <span class="mx-1.5 text-slate-300">&bull;</span>
                        <span class="capitalize">{{ $plan->ai_provider }} Engine</span>
                    </p>

                    @if(count($plan->topics ?? []))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($plan->topics as $topic)
                        <span class="bg-slate-100 text-slate-600 text-[11px] font-medium px-2 py-1 rounded-md border border-slate-200">{{ $topic }}</span>
                        @endforeach
                    </div>
                    @else
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs px-3 py-2 rounded-lg inline-flex items-center gap-2 font-medium">
                        <svg class="animate-spin w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Agent is researching topics...
                    </div>
                    @endif
                </div>

                {{-- Plan Actions --}}
                <div class="flex flex-row md:flex-col items-center md:items-end justify-between gap-3 shrink-0 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-4">
                    <a href="{{ route('posts.index') }}?plan={{ $plan->id }}" class="text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl border border-indigo-200 transition shadow-sm whitespace-nowrap">
                        Review {{ $plan->posts_count }} Drafts &rarr;
                    </a>
                    <form action="{{ route('plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Delete this strategy and purge all its drafts?')">
                        @csrf @method('DELETE')
                        <button class="text-xs font-semibold text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition">Archive Plan</button>
                    </form>
                </div>

            </div>
        </div>
        @empty
        <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-slate-400 mb-4 shadow-sm border border-slate-200">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">No Active Strategies</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">Use the left pane to configure a new Content Strategy and the Agent will begin drafting immediately.</p>
        </div>
        @endforelse

    </div>
</div>
@endsection
