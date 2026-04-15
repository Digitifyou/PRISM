<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Automation – @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#6366f1', dark: '#4f46e5' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-brand rounded-lg flex items-center justify-center shadow">
                <span class="text-white font-bold text-sm">S</span>
            </div>
            <span class="font-bold text-lg text-gray-900 tracking-tight">SMM Automation</span>
        </div>

        <div class="flex items-center gap-1 text-sm font-medium">
            @php
            $navLinks = [
                'dashboard'    => ['label' => 'Dashboard',      'route' => 'dashboard',      'pattern' => 'dashboard'],
                'plans'        => ['label' => 'Content Plans',  'route' => 'plans.index',    'pattern' => 'plans.*'],
                'posts'        => ['label' => 'Posts',          'route' => 'posts.index',    'pattern' => 'posts.*'],
                'insights'     => ['label' => 'Insights',       'route' => 'insights.index', 'pattern' => 'insights.*'],
                'settings'     => ['label' => 'Settings',       'route' => 'settings.index', 'pattern' => 'settings.*'],
            ];
            @endphp
            @foreach($navLinks as $key => $link)
            <a href="{{ route($link['route']) }}"
               class="px-3 py-1.5 rounded-lg transition-colors
                      {{ request()->routeIs($link['pattern'])
                           ? 'bg-indigo-50 text-brand font-semibold'
                           : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                {{ $link['label'] }}
            </a>
            @endforeach
        </div>

        {{-- Queue badge --}}
        @php $pendingJobs = \DB::table('jobs')->count(); @endphp
        @if($pendingJobs > 0)
        <div class="flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-3 py-1">
            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
            {{ $pendingJobs }} job{{ $pendingJobs > 1 ? 's' : '' }} running
        </div>
        @else
        <div class="w-24"></div>
        @endif
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="max-w-6xl mx-auto mt-4 px-6">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <span class="text-green-500">✓</span> {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="max-w-6xl mx-auto mt-4 px-6">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <span class="text-red-500">✕</span> {{ session('error') }}
        </div>
    </div>
    @endif

    {{-- Page Content --}}
    <main class="max-w-6xl mx-auto px-6 py-8">
        @yield('content')
    </main>

</body>
</html>
