<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ANBController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAccessToken()
    {
        $response = Http::asForm()->post(config('anb.auth_url'), [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('anb.client_id'),   
            'client_secret' => config('anb.client_secret'), 
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'access_token' => $data['access_token'],
                'expires_in'   => $data['expires_in'],
            ]);
        }

        return response()->json(['error' => 'Failed to retrieve access token'], 400);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
