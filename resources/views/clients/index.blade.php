@extends('layouts.workspace')

@section('agent-chat')
<div class="flex flex-col h-full relative p-6 bg-slate-50">
    <!-- Header -->
    <header class="pb-4 border-b border-slate-200 mb-6 shrink-0 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Level 0 Architecture</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Client Details</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Create the foundational frame for all AI generations.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Configure Form -->
    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
        <form action="{{ route('clients.store') }}" method="POST" id="clientForm" class="space-y-6">
            @csrf
            <div id="formMethod"></div>

            <!-- Client Identity Segment -->
            <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                    <h3 id="formTitle" class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Business Identity (New Client)</h3>
                    <div id="discoveryLoader" class="hidden flex items-center gap-2 text-[10px] font-bold text-indigo-500 animate-pulse">
                        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        AI Agency is Reseaching...
                    </div>
                </div>
                
                <div class="space-y-4">
                    {{-- Website Discovery --}}
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            Website / URL
                            <span class="text-[10px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded font-bold uppercase tracking-wide italic">Magic Discovery</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="url" name="website_url" id="website_url" placeholder="https://example.com" value="{{ old('website_url') }}"
                                   class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                            <button type="button" id="discoverBtn" class="bg-indigo-50 text-indigo-600 border border-indigo-200 px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-100 transition shadow-sm flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Auto-Fill Framework
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Enter a URL to let the AI Agent automatically extract the strategy framework.</p>
                        @error('website_url')<span class="text-xs text-red-500 font-medium block mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            Company Name <span class="text-[10px] bg-indigo-50 text-indigo-500 px-2 py-0.5 rounded font-bold uppercase tracking-wide">Required</span>
                        </label>
                        <input type="text" name="name" placeholder="e.g. Acme Corp" value="{{ old('name') }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" required>
                        @error('name')<span class="text-xs text-red-500 font-medium block mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Industry / Vertical</label>
                        <input type="text" name="industry" placeholder="e.g. B2B SaaS, Real Estate, E-Commerce" value="{{ old('industry') }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        @error('industry')<span class="text-xs text-red-500 font-medium block mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <!-- Agent Prompt Framing Segment -->
            <div class="bg-white p-5 border border-slate-200 rounded-xl shadow-sm">
                <h3 class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">AI Framing Parameters</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            Brand Voice &amp; Tone
                            <span class="text-slate-400 group relative inline-block cursor-help">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                        </label>
                        <textarea name="brand_voice" rows="3" placeholder="Describe how the AI should sound. E.g. 'Professional, data-driven, witty but never sarcastic.'"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition custom-scrollbar resize-none">{{ old('brand_voice') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            Top Social Media Goals
                            <span class="text-[10px] bg-indigo-50 text-indigo-500 px-2 py-0.5 rounded font-bold uppercase tracking-wide italic">Strategic</span>
                        </label>
                        <textarea name="goals" rows="2" placeholder="e.g. Increase lead generation by 20%, drive traffic to the main site, or boost brand authority."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition mb-4 custom-scrollbar resize-none">{{ old('goals') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Detailed Audience Demographics</label>
                        <textarea name="target_audience_demographics" rows="2" placeholder="e.g. Age 25-45, USA-based, interests in tech/AI, household income $100k+."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition mb-4 custom-scrollbar resize-none">{{ old('target_audience_demographics') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Key Pain Points</label>
                        <textarea name="pain_points" rows="2" placeholder="What are they struggling with? e.g. Lack of time, too much data noise, high operational costs."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition mb-4 custom-scrollbar resize-none">{{ old('pain_points') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5 font-sans">Competitors & Benchmarks</label>
                        <textarea name="competitors" rows="2" placeholder="List 2-4 competitors or brands they look up to for style/strategy."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition custom-scrollbar resize-none">{{ old('competitors') }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Sticky Bottom Actions -->
    <div class="shrink-0 mt-6 pt-4 border-t border-slate-200 bg-slate-50 relative z-10 flex gap-3">
        <button type="button" id="cancelEdit" class="hidden flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-3 px-4 rounded-xl hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            Cancel Edit
        </button>
        <button type="submit" id="submitBtn" form="clientForm" class="flex-[3] bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2 cursor-pointer">
            <span id="btnText">Establish Client Framework</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </button>
    </div>
</div>
@endsection

@section('artifacts')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Artifact Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 rounded-lg text-slate-600 border border-slate-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Managed Workspace Roster</h2>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">Switch contexts to frame the AI workflow</p>
            </div>
        </div>
    </div>

    <!-- Dynamic Clients List -->
    <div class="grid lg:grid-cols-2 gap-4 flex-wrap">
        
        @forelse($clients as $index => $client)
        <!-- Dynamic Client Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative group hover:border-indigo-400 transition cursor-pointer flex flex-col h-full {{ $index > 0 ? 'opacity-80 backdrop-grayscale-[0.1]' : '' }}">
            @if($index === 0)
            <div class="absolute top-3 right-3 z-10">
                 <span class="bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase flex items-center gap-1 shadow-sm">
                     <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></span> Active Context
                 </span>
            </div>
            @endif
            
            <div class="p-5 flex-1 pt-6">
                <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-lg {{ ['bg-indigo-600', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500', 'bg-sky-500'][$index % 5] }} flex items-center justify-center text-white font-bold text-lg shadow-inner shrink-0">
                        {{ strtoupper(substr($client->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-900 text-lg leading-tight truncate">{{ $client->name }}</h3>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition ml-auto shrink-0">
                                <button onclick="editClient({{ json_encode($client) }})" class="p-1 px-1.5 bg-slate-100 text-slate-600 rounded hover:bg-indigo-100 hover:text-indigo-600 transition" title="Edit Profile">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Are you sure? This will permanently delete this client and all associated strategy pillars, plans, and posts.')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1 px-1.5 bg-slate-100 text-red-600 rounded hover:bg-red-100 transition" title="Delete Profile">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m3 3h.01" /></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="text-[11px] font-semibold text-slate-500 uppercase truncate">{{ $client->industry ?: 'Unspecified Industry' }}</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    @if($client->brand_voice)
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Brand Frame</p>
                        <p class="text-xs text-slate-700 bg-slate-50 border border-slate-100 p-2 rounded-lg line-clamp-3 italic">"{{ $client->brand_voice }}"</p>
                    </div>
                    @endif
                    @if($client->goals)
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Strategic Goals</p>
                        <p class="text-xs text-slate-700 font-medium line-clamp-1 truncate">{{ $client->goals }}</p>
                    </div>
                    @endif
                    @if($client->pain_points)
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pain Points</p>
                        <p class="text-xs text-slate-600 line-clamp-1 truncate">{{ $client->pain_points }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="bg-slate-50/80 border-t border-slate-100 px-5 py-3 flex justify-between items-center group-hover:bg-indigo-50/30 transition mt-auto">
                <span class="text-xs font-semibold text-slate-500">Configured Client</span>
                <button class="text-xs font-bold text-slate-500 group-hover:text-indigo-600 flex items-center gap-1 transition">
                    {{ $index === 0 ? 'Manage Settings' : 'Switch Context' }} 
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="lg:col-span-2 bg-slate-50 border border-dashed border-slate-300 rounded-2xl p-12 text-center text-slate-500">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            <p class="text-sm font-bold text-slate-700">No Master Clients Defined</p>
            <p class="text-xs mt-1">Use the configurator on the left to establish your first client framework.</p>
        </div>
        @endforelse

    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discoverBtn = document.getElementById('discoverBtn');
    const urlInput = document.getElementById('website_url');
    const loader = document.getElementById('discoveryLoader');

    if (!discoverBtn) return;

    discoverBtn.addEventListener('click', async function() {
        const url = urlInput.value.trim();
        
        if (!url) {
            alert('Please enter a valid website URL first.');
            return;
        }

        // Show loading state
        discoverBtn.disabled = true;
        discoverBtn.classList.add('opacity-50', 'cursor-not-allowed');
        loader.classList.remove('hidden');

        try {
            const response = await fetch('{{ route('clients.discover') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ url: url })
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'Discovery failed. Please ensure the URL is correct and public.');
            }

            const data = await response.json();

            // Populate form fields with safety checks
            const setVal = (selector, val) => {
                const el = document.querySelector(selector);
                if (el && val) el.value = val;
            };

            setVal('input[name="name"]', data.name);
            setVal('input[name="industry"]', data.industry);
            setVal('textarea[name="brand_voice"]', data.brand_voice);
            setVal('textarea[name="goals"]', data.goals);
            setVal('textarea[name="target_audience_demographics"]', data.target_audience_demographics);
            setVal('textarea[name="pain_points"]', data.pain_points);
            setVal('textarea[name="competitors"]', data.competitors);

            alert('AI Discovery successful! We\'ve populated the framework based on the website content.');

        } catch (error) {
            console.error(error);
            alert(error.message);
        } finally {
            discoverBtn.disabled = false;
            discoverBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            loader.classList.add('hidden');
        }
    const form = document.getElementById('clientForm');
    const formTitle = document.getElementById('formTitle');
    const formMethod = document.getElementById('formMethod');
    const cancelBtn = document.getElementById('cancelEdit');
    const btnText = document.getElementById('btnText');

    window.editClient = function(client) {
        // Toggle UI mode
        formTitle.innerText = `Business Identity (Editing: ${client.name})`;
        btnText.innerText = 'Update Client Framework';
        cancelBtn.classList.remove('hidden');
        
        // Update form state
        form.action = `/clients/${client.id}`;
        formMethod.innerHTML = '<input type="hidden" name="_method" value="PATCH">';

        // Populate fields
        form.querySelector('input[name="name"]').value = client.name || '';
        form.querySelector('input[name="website_url"]').value = client.website_url || '';
        form.querySelector('input[name="industry"]').value = client.industry || '';
        form.querySelector('textarea[name="brand_voice"]').value = client.brand_voice || '';
        form.querySelector('textarea[name="goals"]').value = client.goals || '';
        form.querySelector('textarea[name="target_audience_demographics"]').value = client.target_audience_demographics || '';
        form.querySelector('textarea[name="pain_points"]').value = client.pain_points || '';
        form.querySelector('textarea[name="competitors"]').value = client.competitors || '';

        // Scroll to form
        form.closest('div').scrollTop = 0;
    };

    window.resetForm = function() {
        formTitle.innerText = 'Business Identity (New Client)';
        btnText.innerText = 'Establish Client Framework';
        cancelBtn.classList.add('hidden');
        form.action = '{{ route('clients.store') }}';
        formMethod.innerHTML = '';
        form.reset();
    };

    cancelBtn.addEventListener('click', resetForm);
});
</script>
