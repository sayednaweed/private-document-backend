<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Destination;

class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function destinations()
    {
        //

        $destinations = Destination::with('destinationType:id,name')
        ->select(['id', 'name', 'color', 'destination_type_id'])
        ->get();

        return $destinations;

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
            'name' => 'required|string',
            'color' => 'required|string',
            'destination_type_id' => 'required|integer'

        ]);

        Destination::create([
            'name' => $request->name,
            'color' => $request->color,
            'destination_type_id' => $request->destination_type_id
        ]);
        

        return response()->json('Successfully Add the destination', 200);


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
