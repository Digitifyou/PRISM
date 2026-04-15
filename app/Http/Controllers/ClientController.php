<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\Level1\ClientDiscoveryService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function discover(Request $request, ClientDiscoveryService $discovery)
    {
        $url = $request->input('url');
        if ($url && !preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "https://" . $url;
            $request->merge(['url' => $url]);
        }

        $request->validate([
            'url' => 'required|url',
        ]);

        $data = $discovery->discover($url);

        if (empty($data)) {
            return response()->json(['error' => 'Could not extract details from the provided URL.'], 422);
        }

        return response()->json($data);
    }

    public function index()
    {
        $clients = Client::latest()->get();
        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateClient($request);
        Client::create($validated);
        return redirect()->route('clients.index')->with('success', 'Client profile established successfully.');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $this->validateClient($request);
        $client->update($validated);
        return redirect()->route('clients.index')->with('success', 'Client profile updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client profile removed.');
    }

    protected function validateClient(Request $request)
    {
        return $request->validate([
            'name'            => 'required|string|max:255',
            'website_url'     => 'nullable|url|max:255',
            'industry'        => 'nullable|string|max:255',
            'brand_voice'                  => 'nullable|string',
            'target_audience_demographics' => 'nullable|string',
            'goals'                        => 'nullable|string',
            'pain_points'                  => 'nullable|string',
            'competitors'                  => 'nullable|string',
        ]);
    }
}
