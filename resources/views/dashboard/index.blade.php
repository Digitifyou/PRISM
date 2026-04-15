@extends('layouts.workspace')

@section('agent-chat')
<div class="flex flex-col h-full relative p-4 bg-slate-50">
    <!-- Header -->
    <header class="flex items-center justify-between pb-4 border-b border-slate-200 mb-4 shrink-0">
        <div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Level 1: Content Studio</h1>
            <p class="text-xs text-slate-500 font-medium">Plan, Research, Strategy & Writing</p>
        </div>
        
        <!-- Brand Selector (Context Injection) -->
        <select class="bg-white text-slate-700 text-sm rounded-lg border border-slate-200 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-1.5 outline-none font-medium shadow-sm">
            <option>Acme Corp (Tech)</option>
            <option>FitLife (Health)</option>
        </select>
    </header>

    <!-- Chat History -->
    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-6 pr-2">
        <!-- User Message -->
        <div class="flex items-start gap-4">
            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0 border border-slate-300 shadow-sm">
                <span class="text-xs font-bold text-slate-600">U</span>
            </div>
            <div class="flex-1">
                <p class="font-medium text-slate-700 text-sm mb-1">You</p>
                <div class="text-slate-700 text-sm bg-white p-3 rounded-2xl rounded-tl-sm border border-slate-200 inline-block shadow-sm">
                    Draft 3 LinkedIn hooks about our new AI scheduling feature according to our content plan.
                </div>
            </div>
        </div>

        <!-- Agent Response -->
        <div class="flex items-start gap-4">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/30">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div class="flex-1">
                <p class="font-medium text-indigo-700 text-sm mb-1">Agent</p>
                <div class="text-slate-800 text-sm space-y-3">
                    <p>I've reviewed your content plan and generated 3 hook variations for LinkedIn designed to match Acme Corp's professional tone.</p>
                    <p class="text-indigo-600 text-xs flex items-center gap-1 font-medium bg-indigo-50 inline-flex px-2 py-1 rounded-md border border-indigo-100">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        Previewing Variations in Right Pane
                    </p>
                    <div class="flex gap-2 mt-2">
                        <button class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer shadow-sm">
                            Mark as Approved
                        </button>
                        <button class="bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer shadow-sm">
                            Regenerate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Suggestions -->
        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-slate-200">
            <button class="text-left bg-white hover:bg-slate-50 border border-slate-200 p-3 rounded-xl shadow-sm transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-700 mb-1">🔍 Level 1: Research</p>
                <p class="text-[10px] text-slate-500">Analyze top performing posts</p>
            </button>
            <button class="text-left bg-white hover:bg-slate-50 border border-slate-200 p-3 rounded-xl shadow-sm transition cursor-pointer">
                <p class="text-xs font-semibold text-slate-700 mb-1">📅 Level 2: Schedule</p>
                <p class="text-[10px] text-slate-500">View upcoming content cal</p>
            </button>
        </div>
    </div>

    <!-- Input Area -->
    <div class="shrink-0 mt-4 relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
        </div>
        <textarea 
            rows="2" 
            class="w-full bg-white border border-slate-300 rounded-2xl pl-10 pr-12 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent custom-scrollbar resize-none shadow-sm"
            placeholder="Ask Agent to draft, analyze, or schedule... (/ for commands)"></textarea>
        
        <button class="absolute bottom-2.5 right-2 bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-xl transition shadow-md shadow-indigo-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
        </button>
    </div>
</div>
@endsection

@section('artifacts')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Artifact Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 border border-indigo-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">LinkedIn Content Studio (Level 1)</h2>
                <p class="text-[11px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">Content Plan: Tech Launch Q3</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button class="text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg border border-indigo-200 transition">Copy All</button>
        </div>
    </div>

    <!-- Artifact Content: Mock LinkedIn Posts -->
    <div class="space-y-6">
        
        <!-- Post Variation 1 -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative group transition hover:shadow-md">
            <!-- LinkedIn Mock Header -->
            <div class="flex items-center justify-between p-4 pb-2 border-b border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-full overflow-hidden shrink-0 border border-slate-200 shadow-sm">
                         <img src="https://ui-avatars.com/api/?name=Acme+Corp&background=f8fafc&color=475569" alt="Acme">
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm leading-tight hover:text-blue-600 hover:underline cursor-pointer">Acme Corp</h3>
                        <p class="text-xs text-slate-500">10,482 followers</p>
                        <p class="text-[10px] text-slate-400 flex items-center gap-1">Variation A &bull; <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/></svg></p>
                    </div>
                </div>
                 <button class="bg-white border border-slate-200 text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm hover:bg-slate-50 cursor-pointer">Send to Level 3 (Image Engine)</button>
            </div>
            
            <!-- LinkedIn Content -->
            <div class="px-4 py-3">
                <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed selection:bg-indigo-100 selection:text-indigo-900">Stop scheduling posts manually. Start building strategies automatically. 

Our new AI scheduling engine doesn't just post when you tell it to—it analyzes your audience's unique hibernation patterns and posts when they are actually awake. 

Are you still guessing? Let the data decide. 👇

#SaaS #SocialMediaStrategy #MarketingAutomation</p>
            </div>
            
            <div class="border-t border-slate-100 bg-slate-50/50 p-2 flex justify-between px-4 items-center">
                 <button class="text-xs font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-4 py-1.5 rounded-lg border border-emerald-200 transition shadow-sm">Approve for Level 2 (Schedule)</button>
                 <button class="text-xs font-medium text-slate-500 hover:text-slate-700 underline transition">Edit Text</button>
            </div>
        </div>

        <!-- Post Variation 2 (Agent Pick) -->
        <div class="bg-white rounded-xl shadow-md border-2 border-indigo-400 overflow-hidden relative group">
            <div class="absolute top-0 right-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider shadow-sm">
                Agent Top Pick ✨
            </div>
            
            <div class="flex items-center justify-between p-4 pb-2 border-b border-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-full overflow-hidden shrink-0 border border-slate-200 shadow-sm">
                         <img src="https://ui-avatars.com/api/?name=Acme+Corp&background=f8fafc&color=475569" alt="Acme">
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm leading-tight">Acme Corp</h3>
                        <p class="text-xs text-slate-500">Variation B</p>
                    </div>
                </div>
                <div class="pr-24"></div> <!-- space for badge -->
            </div>
            
            <!-- LinkedIn Content -->
            <div class="px-4 py-3">
                <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed selection:bg-indigo-100 selection:text-indigo-900 font-medium">73% of social posts are scheduled at the wrong time. 

Today we're changing that.

Introducing our Predictive Scheduling Engine. We trained an AI on 10M+ posts to map audience activity heatmaps uniquely to your account. 

No more posting into the void. Read the full release here: [link]

#ProductLaunch #SocialMediaMarketing #AI</p>
            </div>
            <div class="border-t border-indigo-50 bg-indigo-50/30 p-2 flex justify-between px-4 items-center">
                <button class="text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 px-4 py-1.5 rounded-lg border border-emerald-300 transition shadow-sm border-b-2">Approve for Level 2 (Schedule)</button>
                <button class="text-xs font-medium text-indigo-500 hover:text-indigo-700 underline transition">Generate Image</button>
           </div>
        </div>

    </div>
</div>
@endsection
