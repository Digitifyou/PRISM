@extends('layouts.app')
@section('title', 'Insights')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Insights</h1>
        <p class="text-sm text-gray-500 mt-1">Engagement analytics across all published posts.</p>
    </div>
</div>

@if(!$hasData)
<div class="bg-white rounded-xl border border-dashed border-gray-300 p-16 text-center">
    <p class="text-4xl mb-4">📊</p>
    <h3 class="font-semibold text-gray-700 mb-2">No insights yet</h3>
    <p class="text-sm text-gray-400 max-w-md mx-auto">
        Insights are collected automatically 30 minutes after a post is published to social media.
        <a href="{{ route('posts.index', ['status' => 'draft']) }}" class="text-brand hover:underline">Approve and publish posts</a> to start seeing data.
    </p>
</div>
@else

{{-- Overall Metric Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @php
    $metricCards = [
        ['label' => 'Total Likes',        'value' => number_format($totals->total_likes),       'color' => 'text-pink-600',    'bg' => 'bg-pink-50'],
        ['label' => 'Total Comments',     'value' => number_format($totals->total_comments),    'color' => 'text-purple-600',  'bg' => 'bg-purple-50'],
        ['label' => 'Total Shares',       'value' => number_format($totals->total_shares),      'color' => 'text-indigo-600',  'bg' => 'bg-indigo-50'],
        ['label' => 'Total Reach',        'value' => number_format($totals->total_reach),       'color' => 'text-blue-600',    'bg' => 'bg-blue-50'],
        ['label' => 'Total Impressions',  'value' => number_format($totals->total_impressions), 'color' => 'text-sky-600',     'bg' => 'bg-sky-50'],
        ['label' => 'Avg Engagement',     'value' => round($totals->avg_engagement, 2) . '%',   'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
    ];
    @endphp
    @foreach($metricCards as $card)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">{{ $card['label'] }}</p>
        <p class="text-3xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Per-Platform Breakdown --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">Platform Breakdown</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-xs text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3 font-medium">Platform</th>
                    <th class="text-right px-5 py-3 font-medium">Likes</th>
                    <th class="text-right px-5 py-3 font-medium">Comments</th>
                    <th class="text-right px-5 py-3 font-medium">Shares</th>
                    <th class="text-right px-5 py-3 font-medium">Reach</th>
                    <th class="text-right px-5 py-3 font-medium">Impressions</th>
                    <th class="text-right px-5 py-3 font-medium">Engagement</th>
                </tr>
            </thead>
            <tbody>
                @php
                $platforms = ['facebook' => ['label' => 'Facebook', 'color' => 'text-blue-600'], 'instagram' => ['label' => 'Instagram', 'color' => 'text-pink-600'], 'linkedin' => ['label' => 'LinkedIn', 'color' => 'text-sky-600']];
                @endphp
                @foreach($platforms as $key => $p)
                @if(isset($byPlatform[$key]))
                @php $row = $byPlatform[$key]; @endphp
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <span class="font-semibold {{ $p['color'] }}">{{ $p['label'] }}</span>
                    </td>
                    <td class="px-5 py-3 text-right font-medium">{{ number_format($row->likes) }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($row->comments) }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($row->shares) }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($row->reach) }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($row->impressions) }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-semibold {{ $row->engagement_rate >= 3 ? 'text-emerald-600' : ($row->engagement_rate >= 1 ? 'text-amber-600' : 'text-gray-500') }}">
                            {{ round($row->engagement_rate, 2) }}%
                        </span>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Top Performing Posts --}}
@if($topPosts->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">Top Performing Posts</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-xs text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3 font-medium">Topic</th>
                    <th class="text-left px-5 py-3 font-medium">Platform</th>
                    <th class="text-right px-5 py-3 font-medium">Likes</th>
                    <th class="text-right px-5 py-3 font-medium">Reach</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($topPosts as $post)
                @php
                $pColors = ['facebook' => 'bg-blue-100 text-blue-700', 'instagram' => 'bg-pink-100 text-pink-700', 'linkedin' => 'bg-sky-100 text-sky-700'];
                @endphp
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800 truncate max-w-xs">{{ $post->topic }}</p>
                        <p class="text-xs text-gray-400">{{ $post->contentPlan->niche ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $pColors[$post->platform] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($post->platform) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-medium text-pink-600">{{ number_format($post->insights_sum_likes ?? 0) }}</td>
                    <td class="px-5 py-3 text-right text-blue-600">{{ number_format($post->insights_sum_reach ?? 0) }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('posts.show', $post) }}" class="text-xs text-brand hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endif
@endsection
