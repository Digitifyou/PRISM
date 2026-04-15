<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientPillar;
use App\Services\Level1\PillarGeneratorService;
use Illuminate\Http\Request;

class ClientPillarController extends Controller
{
    public function index()
    {
        // For standard UI mockup, we will pull all clients to populate the dropdown 
        // and pull all pillars just for visual review.
        $clients = Client::with('pillars')->latest()->get();
        // Fallback to empty context if no clients exist
        $activeClient = $clients->first(); 
        $pillars = $activeClient ? $activeClient->pillars : collect([]);

        return view('pillars.index', compact('clients', 'activeClient', 'pillars'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        ClientPillar::create($validated);

        return redirect()->route('pillars.index')->with('success', 'Content Pillar successfully established.');
    }

    public function generate(Request $request, PillarGeneratorService $generator)
    {
        $request->validate(['client_id' => 'required|exists:clients,id']);
        $client = Client::findOrFail($request->client_id);
        
        $pillars = $generator->generate($client);

        if (empty($pillars)) {
            return response()->json(['error' => 'Could not generate strategic pillars. Please ensure client strategy is filled.'], 422);
        }

        return response()->json($pillars);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'pillars'   => 'required|array',
            'pillars.*.title' => 'required|string|max:255',
            'pillars.*.description' => 'required|string',
        ]);

        foreach ($request->pillars as $pillarData) {
            ClientPillar::create([
                'client_id'   => $request->client_id,
                'title'       => $pillarData['title'],
                'description' => $pillarData['description'],
            ]);
        }

        return response()->json(['success' => true]);
    }
}
