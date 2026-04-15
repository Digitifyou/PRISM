<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\Post;

class InsightController extends Controller
{
    public function index()
    {
        $totals = Insight::selectRaw('
            COALESCE(SUM(likes), 0)           as total_likes,
            COALESCE(SUM(comments), 0)        as total_comments,
            COALESCE(SUM(shares), 0)          as total_shares,
            COALESCE(SUM(reach), 0)           as total_reach,
            COALESCE(SUM(impressions), 0)     as total_impressions,
            COALESCE(AVG(engagement_rate), 0) as avg_engagement
        ')->first();

        $byPlatform = Insight::selectRaw('
            platform,
            COALESCE(SUM(likes), 0)           as likes,
            COALESCE(SUM(comments), 0)        as comments,
            COALESCE(SUM(shares), 0)          as shares,
            COALESCE(SUM(reach), 0)           as reach,
            COALESCE(SUM(impressions), 0)     as impressions,
            COALESCE(AVG(engagement_rate), 0) as engagement_rate
        ')->groupBy('platform')->get()->keyBy('platform');

        $topPosts = Post::whereHas('insights')
            ->with(['insights' => fn ($q) => $q->latest('fetched_at'), 'contentPlan'])
            ->where('status', Post::STATUS_PUBLISHED)
            ->withSum('insights', 'likes')
            ->withSum('insights', 'reach')
            ->orderByDesc('insights_sum_likes')
            ->limit(10)
            ->get();

        $hasData = Insight::count() > 0;

        return view('insights.index', compact('totals', 'byPlatform', 'topPosts', 'hasData'));
    }
}
