<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SMM Agent') }} - Workspace</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|roboto-mono:400,500" rel="stylesheet" />

    <!-- Scripts / Styles -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    
    <!-- Alpine.js (if not bundled in app.js yet) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 overflow-hidden h-screen w-screen flex selection:bg-indigo-100 selection:text-indigo-900">

    <!-- Sidebar Navigation -->
    <aside class="w-16 hover:w-64 transition-all duration-300 ease-in-out border-r border-slate-200 bg-white/80 backdrop-blur-xl flex flex-col justify-between py-4 group z-50 relative shrink-0 shadow-sm shadow-slate-200/50">
        <div class="px-3">
            <div class="flex items-center gap-3 px-2 mb-8 text-indigo-600">
                <svg class="w-8 h-8 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="font-bold text-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">SMM Agent</span>
            </div>

            <nav class="space-y-6 pt-2">
                
                <!-- PHASE 1: STRATEGY -->
                <div>
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center group-hover:text-left group-hover:px-2">
                        <span class="group-hover:hidden">1</span>
                        <span class="hidden group-hover:inline whitespace-nowrap">1. Strategy</span>
                    </h3>
                    <div class="space-y-1">
                        <a href="{{ route('clients.index') }}" class="flex items-center justify-between px-2 py-2 rounded-lg {{ request()->routeIs('clients.*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} transition-colors group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Client Identity</span>
                            </div>
                            @if(request()->routeIs('clients.*')) <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full hidden sm:block"></div> @endif
                        </a>
                        <a href="{{ route('pillars.index') }}" class="flex items-center justify-between px-2 py-2 rounded-lg {{ request()->routeIs('pillars.*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} transition-colors group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Content Pillars</span>
                            </div>
                            @if(request()->routeIs('pillars.*')) <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full hidden sm:block"></div> @endif
                        </a>
                    </div>
                </div>

                <!-- PHASE 2: CONTENT STUDIO -->
                <div>
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center group-hover:text-left group-hover:px-2">
                        <span class="group-hover:hidden">2</span>
                        <span class="hidden group-hover:inline whitespace-nowrap">2. Content Studio</span>
                    </h3>
                    <div class="space-y-1">
                        <a href="{{ route('plans.index') }}" class="flex items-center justify-between px-2 py-2 rounded-lg {{ request()->routeIs('plans.*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} transition-colors group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Ideation Plans</span>
                            </div>
                            @if(request()->routeIs('plans.*')) <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full hidden sm:block"></div> @endif
                        </a>
                        <a href="{{ route('posts.index') }}" class="flex items-center justify-between px-2 py-2 rounded-lg {{ request()->routeIs('posts.*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} transition-colors group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Draft Approval</span>
                            </div>
                            @if(request()->routeIs('posts.*')) <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full hidden sm:block"></div> @endif
                        </a>
                    </div>
                </div>

                <!-- PHASE 3: ASSETS -->
                <div>
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center group-hover:text-left group-hover:px-2">
                        <span class="group-hover:hidden">3</span>
                        <span class="hidden group-hover:inline whitespace-nowrap">3. Asset Engine</span>
                    </h3>
                    <div class="space-y-1">
                        <a href="#" class="flex items-center justify-between px-2 py-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors font-medium group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Poster Generator</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- PHASE 4: DISTRIBUTION -->
                <div>
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center group-hover:text-left group-hover:px-2">
                        <span class="group-hover:hidden">4</span>
                        <span class="hidden group-hover:inline whitespace-nowrap">4. Distribution</span>
                    </h3>
                    <div class="space-y-1">
                        <a href="{{ route('calendar') }}" class="flex items-center justify-between px-2 py-2 rounded-lg {{ request()->routeIs('calendar') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} transition-colors group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Content Calendar</span>
                            </div>
                            @if(request()->routeIs('calendar')) <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full hidden sm:block"></div> @endif
                        </a>
                        <a href="{{ route('insights.index') }}" class="flex items-center justify-between px-2 py-2 rounded-lg {{ request()->routeIs('insights.*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} transition-colors group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap hidden sm:block">Analytics &amp; Insights</span>
                            </div>
                            @if(request()->routeIs('insights.*')) <div class="w-1.5 h-1.5 bg-indigo-600 rounded-full hidden sm:block"></div> @endif
                        </a>
                    </div>
                </div>

            </nav>
        </div>
        
        <div class="px-3">
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-2 py-2.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                <img src="https://ui-avatars.com/api/?name=Agent+User&background=6366f1&color=fff" class="w-6 h-6 rounded-full shrink-0" alt="Avatar">
                <span class="opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap font-medium text-sm hidden sm:block">Workspace Settings</span>
            </a>
        </div>
    </aside>

    <!-- Main Split Workspace -->
    <main class="flex-1 flex flex-col lg:flex-row min-w-0 bg-white" x-data="{ artifactOpen: true }" x-cloak>
        
        <!-- Left Pane: Agent Chat -->
        <section class="flex-1 flex flex-col bg-slate-50 min-w-0 border-r border-slate-200 transition-all duration-300 relative z-10" :class="artifactOpen ? 'lg:max-w-[45%] shadow-xl shadow-slate-200/40' : 'w-full'">
            @yield('agent-chat')
        </section>

        <!-- Right Pane: Artifact Preview -->
        <section class="flex-1 flex flex-col bg-white min-w-0 relative" x-show="artifactOpen" x-transition.opacity>
            <div class="absolute top-4 right-4 z-10">
                <button @click="artifactOpen = false" class="p-2 text-slate-500 hover:text-slate-800 bg-white/80 rounded-md backdrop-blur border border-slate-200 shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 lg:p-6 custom-scrollbar bg-slate-100/50">
                @yield('artifacts')
            </div>
        </section>

        <!-- Toggle Button when Artifacts are closed -->
        <div class="fixed top-4 right-4 z-20" x-show="!artifactOpen" x-transition.opacity>
            <button @click="artifactOpen = true" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-500 rounded-lg shadow-lg shadow-indigo-200 transition">
                <svg class="w-4 h-4" x-show="!artifactOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                Show Workspace
            </button>
        </div>

    </main>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
