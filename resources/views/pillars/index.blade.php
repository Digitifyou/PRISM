@extends('layouts.workspace')

@section('agent-chat')
<div class="flex flex-col h-full relative p-6 bg-slate-50">
    <!-- Header -->
    <header class="pb-4 border-b border-slate-200 mb-6 shrink-0 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Phase 1: Strategy</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Content Pillars</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Define exactly what topics the AI is allowed to write about.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(!$activeClient)
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl shadow-sm text-sm font-bold">
            ⚠️ No Client Context Found. Please go to Client Identity to create a client first.
        </div>
    @else
        <!-- Configure Form -->
        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
            <form action="{{ route('pillars.store') }}" method="POST" id="pillarForm" class="space-y-6">
                @csrf
                <input type="hidden" name="client_id" value="{{ $activeClient->id }}">

                <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Add Architectural Pillar</h3>
                        <div class="flex items-center gap-3">
                            <button type="button" id="generateBtn" class="text-[10px] font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-full uppercase tracking-tight flex items-center gap-1.5 transition border border-indigo-100">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                ✨ Auto-Generate
                            </button>
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-wide">Context: {{ $activeClient->name }}</span>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                Pillar Title
                            </label>
                            <input type="text" name="title" placeholder="e.g. Educational / How-To guides" value="{{ old('title') }}"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" required>
                            @error('title')<span class="text-xs text-red-500 font-medium block mt-1">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                AI Instructions (Description)
                            </label>
                            <textarea name="description" rows="4" placeholder="Instruct the AI exactly how this pillar works. E.g. 'This pillar focuses only on actionable SEO tips for local businesses. Never use jargon.'"
                                      class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition custom-scrollbar resize-none" required>{{ old('description') }}</textarea>
                            @error('description')<span class="text-xs text-red-500 font-medium block mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </form>

            {{-- AI Suggestions Section --}}
            <div id="suggestionsWrapper" class="hidden mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">AI Strategic Suggestions</h3>
                    <button type="button" id="addAllBtn" class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700 uppercase tracking-tighter transition">Add All to Board</button>
                </div>
                <div id="suggestionsContainer" class="space-y-3">
                    <!-- Suggested Pillars Injection -->
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Actions -->
        <div class="shrink-0 mt-6 pt-4 border-t border-slate-200 bg-slate-50 relative z-10">
            <button type="submit" form="pillarForm" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2 cursor-pointer">
                Establish Content Pillar
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
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
            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 border border-indigo-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Master Board</h2>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">{{ $activeClient ? $activeClient->name : 'No Client' }} &bull; Active Core Themes</p>
            </div>
        </div>
    </div>

    @if(!$activeClient)
        <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-12 text-center text-slate-500">
            <p class="text-sm font-bold text-slate-700">No Target Found</p>
            <p class="text-xs mt-1">Please set your Client Identity first.</p>
        </div>
    @else
        <!-- Dynamic Pillars List -->
        <div class="grid gap-4 flex-wrap">
            @forelse($pillars as $pillar)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative group hover:border-indigo-400 transition cursor-pointer flex flex-col h-full">
                <div class="p-5 flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 mt-1 shrink-0 self-start"></div>
                        <h3 class="font-bold text-slate-900 text-lg leading-tight">{{ $pillar->title }}</h3>
                    </div>
                    <div class="pl-5">
                        <p class="text-xs text-slate-700 bg-slate-50 border border-slate-100 p-3 rounded-lg leading-relaxed italic border-l-4 border-l-indigo-400">
                            "{{ $pillar->description }}"
                        </p>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-slate-50 border border-dashed border-slate-300 rounded-2xl p-12 text-center text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                <p class="text-sm font-bold text-slate-700">No Content Pillars Defined</p>
                <p class="text-xs mt-1">Add your 3-5 core themes to the left to constrain the AI's generation strategies.</p>
            </div>
            @endforelse
        </div>
    @endif
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generateBtn');
    const suggestionsWrapper = document.getElementById('suggestionsWrapper');
    const suggestionsContainer = document.getElementById('suggestionsContainer');
    const addAllBtn = document.getElementById('addAllBtn');
    let suggestedPillars = [];

    if (!generateBtn) return;

    generateBtn.addEventListener('click', async function() {
        generateBtn.disabled = true;
        generateBtn.classList.add('opacity-50', 'cursor-not-allowed');
        const originalText = generateBtn.innerHTML;
        generateBtn.innerHTML = '✨ Generating...';

        try {
            const response = await fetch('{{ route('pillars.generate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ client_id: '{{ $activeClient->id ?? '' }}' })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Generation failed');
            }

            suggestedPillars = await response.json();
            renderSuggestions(suggestedPillars);
            suggestionsWrapper.classList.remove('hidden');

        } catch (error) {
            alert(error.message);
        } finally {
            generateBtn.disabled = false;
            generateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            generateBtn.innerHTML = originalText;
        }
    });

    function renderSuggestions(pillars) {
        suggestionsContainer.innerHTML = '';
        pillars.forEach((pillar, index) => {
            const div = document.createElement('div');
            div.className = 'bg-white p-4 border border-indigo-100 rounded-xl shadow-sm hover:border-indigo-300 transition group flex flex-col gap-2 cursor-pointer';
            div.innerHTML = `
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-900">${pillar.title}</h4>
                    <button type="button" class="add-single-btn text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded uppercase hover:bg-indigo-600 hover:text-white transition" data-index="${index}">Add Pillar</button>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed italic border-l-2 border-indigo-200 pl-3">${pillar.description}</p>
            `;
            
            // Add single click handler
            div.querySelector('.add-single-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                savePillars([pillar]);
            });

            suggestionsContainer.appendChild(div);
        });
    }

    async function savePillars(pillarsToSave) {
        try {
            const response = await fetch('{{ route('pillars.bulk-store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    client_id: '{{ $activeClient->id ?? '' }}',
                    pillars: pillarsToSave
                })
            });

            if (response.ok) {
                window.location.reload();
            } else {
                alert('Failed to save pillars.');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred while saving.');
        }
    }

    addAllBtn.addEventListener('click', () => savePillars(suggestedPillars));
});
</script>
