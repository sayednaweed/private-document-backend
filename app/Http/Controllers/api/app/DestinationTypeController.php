<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DestinationType;

class DestinationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function destinationTypes()
    {
        //
    return     DestinationType::select('id','name')->get();
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
        $request->validate([
            'name' => 'required|string'
        ]);
        DestinationType::create([
            'name' => $request->name
        ]);

        return response()->json('Successfully add Destination Type', 200);

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