<?php

namespace App\Http\Controllers\api\app;


use App\Http\Requests\app\document\DocumentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Scan;



class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

    
        // Eager load the related models and select the required fields
        return Document::with(['status:id,name,color','source:id,name', 'urgency:id,name', 'type:id,name'])
        ->get();


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
     public function store(DocumentRequest $request)
    {

    $data = $request->validated();

           // 1. If storage not exist create it.
           $path = storage_path() . "/app/private/scans/";
            // Checks directory exist if not will be created.
            !is_dir($path) &&
                mkdir($path, 0777, true);
       
            // 2. Store image in filesystem
            $filepath;
            $fileName =null;
            if ($request->hasFile('scan_file')) {
                $file = $request->file('scan_file');
                if ($file != null) {
                    $fileName = Str::uuid() . '.' . $file->extension();
                    $file->move($path, $fileName);

                    $filepath = "private/scans/" . $fileName;
                }
            }
     
    $scan = Scan::create([
        'initail_scan' => $filepath,
        'muqam_scan' =>"",
        'final_scan' =>""

    ]);



    // Prepare document data and include scan_id
    
    $data['scan_id'] = $scan->id; // Set the scan_id in the data array

    $document = Document::create($data);


        return response()->json([
            'message' => 'Document created successfully.'
         
        ], 201);
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
