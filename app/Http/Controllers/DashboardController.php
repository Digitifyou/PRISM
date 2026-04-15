<?php

namespace App\Http\Controllers;

use App\Models\ContentPlan;
use App\Models\Post;
use App\Models\Insight;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_plans'     => ContentPlan::count(),
            'draft_posts'     => Post::where('status', Post::STATUS_DRAFT)->count(),
            'approved_posts'  => Post::where('status', Post::STATUS_APPROVED)->count(),
            'published_posts' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
            'failed_posts'    => Post::where('status', Post::STATUS_FAILED)->count(),
            'total_reach'     => Insight::sum('reach'),
            'total_likes'     => Insight::sum('likes'),
        ];

        $byPlatform = Post::selectRaw('platform, status, count(*) as total')
            ->groupBy('platform', 'status')
            ->get()
            ->groupBy('platform');

        $recentPlans = ContentPlan::withCount('posts')->latest()->limit(5)->get();

        $recentPosts = Post::with('contentPlan')->latest()->limit(8)->get();

        $pendingJobs = \DB::table('jobs')->count();

        return view('dashboard.index', compact(
            'stats', 'byPlatform', 'recentPlans', 'recentPosts', 'pendingJobs'
        ));
    }
}
