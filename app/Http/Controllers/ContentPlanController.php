<?php

namespace App\Http\Controllers;

use App\Jobs\Level1\RunContentPlanJob;
use App\Models\ContentPlan;
use App\Models\Client;
use App\Models\ClientPillar;
use App\Services\Ai\AiProviderFactory;
use Illuminate\Http\Request;

class ContentPlanController extends Controller
{
    public function index()
    {
        $plans = ContentPlan::with(['client', 'pillar'])->withCount('posts')->latest()->get();
        
        $clients = Client::with('pillars')->get();
        $activeClient = $clients->first();
        $activePillars = $activeClient ? $activeClient->pillars : collect();

        return view('plans.index', [
            'plans'         => $plans,
            'providers'     => AiProviderFactory::labels(),
            'clients'       => $clients,
            'activeClient'  => $activeClient,
            'activePillars' => $activePillars,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'client_pillar_id' => 'required|exists:client_pillars,id',
            'frequency'        => 'required|in:daily,weekly',
            'platforms'        => 'required|array|min:1',
            'platforms.*'      => 'in:facebook,instagram,linkedin,twitter',
            'ai_provider'      => 'required|in:openai,gemini,claude',
        ]);

        $pillar = ClientPillar::findOrFail($data['client_pillar_id']);
        $client = Client::findOrFail($data['client_id']);

        // Formulate niche dynamically for backwards compatibility with the right pane cards
        $data['niche'] = $client->name . ' | ' . $pillar->title;

        $plan = ContentPlan::create($data + ['topics' => []]);

        RunContentPlanJob::dispatch($plan);

        return redirect()->route('posts.index')
            ->with('success', "Content plan for {$client->name} created via {$pillar->title}! Generating drafts with {$data['ai_provider']}...");
    }

    public function destroy(ContentPlan $plan)
    {
        $plan->delete();

        return back()->with('success', 'Plan deleted.');
    }
}
