<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Type;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function documentTypes()
    {
        //

      return   Type::select('id','name')->get();
        
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

        $validatedData = $request->validate([
        'name' => 'required|string',
    ]);

    // Create a new type with the validated data
    $type = Type::create($validatedData);

    // Return a JSON response with success message
    return response()->json([
        'message' => 'Type created successfully.',
        'data' => $type,
    ], 201);  

}
        //
    

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
