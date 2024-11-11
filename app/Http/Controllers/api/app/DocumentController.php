<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Http\Requests\app\document\DocumentStoreRequest;
use App\Models\Document;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;



class DocumentController extends Controller
{
    public function documents(Request $request, $page)
    {
        $documents = Document::with([
            'status:id,name,color',
            'source:id,name',
            'urgency:id,name',
            'type:id,name',
        ])->select(['id', 'document_number', 'document_date', 'status_id', 'source_id', 'urgency_id', 'type_id'])
            ->get();

        // Append the first deadline from `documentDestination` table
        $documents->each(function ($document) {
            $document->deadline = $document->documentDestination()->orderBy('id')->value('deadline');
        });

        return $documents;
    }


    public function document($id)
    {
        $documentId = $id; // Replace with the specific document ID you want to load
        $documents = Document::with([
            'status:id,name,color',
            'source:id,name',
            'urgency:id,name',
            'type:id,name',
            'documentDestination' => function ($query) {
                $query->orderBy('id'); // Optionally order the related records
            }
        ])
            ->where('id', $documentId) // Filter by specific document ID
            ->select('*') // Load all columns from the documents table
            ->get();

        // Append the first deadline from the `documentDestination` table
        $documents->each(function ($document) {
            $document->deadline = $document->documentDestination->first()->deadline ?? null;
        });

        return $documents;
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
    public function store(DocumentStoreRequest $request)
    {
        $data = $request->validated();

        // 1. If storage not exist create it.
        $path = storage_path() . "/app/private/scans/";
        // Checks directory exist if not will be created.
        !is_dir($path) &&
            mkdir($path, 0777, true);

        // 2. Store image in filesystem
        $filepath = null;
        $fileName = null;
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
            'muqam_scan' => "",
            'final_scan' => ""

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
