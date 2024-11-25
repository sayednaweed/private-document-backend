<?php

namespace App\Http\Controllers\api\app;

use App\Enums\DestinationTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\document\DocumentStoreRequest;
use App\Models\Destination;
use App\Models\Document;
use App\Models\DocumentDestination;
use App\Models\DocumentDestinationNoFeed;
use App\Models\DocumentType;
use App\Models\Scan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;


class DocumentController extends Controller
{
    public function documents(Request $request, $page)
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value) {
                $tr = Document::with([
                    'status:id,name,color',
                    'source:id,name',
                    'urgency:id,name',
                    'type:id,name',
                    'documentDestination'
                ])->select(['id', 'summary', 'document_number', 'document_number', 'document_date', 'status_id', 'source_id', 'urgency_id', 'document_type_id'])
                    ->get();

                // Append the first deadline from `documentDestination` table
                $tr->each(function ($document) {
                    $document->deadline = $document->documentDestination()->orderBy('id')->value('deadline');
                });
            } else {
                // $tr = $this->translations($locale);
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('statuses error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
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
        $request->validated();
        try {
            // 1. store document
            $documentType = DocumentType::find($request->documentType);
            $path = $this->storeDocument($request, $documentType->name);
            $scan = Scan::create([
                "initail_scan" => $path
            ]);
            $document = Document::create([
                'document_number' => $request->documentNumber,
                'summary' => $request->subject,
                'qaid_warida_number' => $request->qaidWarida,
                'document_date' => $request->documentDate,
                'qaid_warida_date' => $request->qaidWaridaDate,
                'document_type_id' => $documentType->id,
                'status_id' => StatusEnum::inProgres->value,
                'urgency_id' => $request->urgency,
                'source_id' => $request->source,
                'scan_id' => $scan->id,
                'reciever_user_id' => $request->user()->id,
                "send_to_muqam" => true
            ]);

            // 2. Store destinations
            // $references = json_decode($request->reference, true);
            // $length = count($references);
            // $step = 1;
            // if ($length >= 2) {
            //     $step = 0;
            // }
            // if ($request->feedback == "true") {
            //     foreach ($references as $reference) {
            //         $destinationId = $reference['id'];
            //         DocumentDestination::create([
            //             'step' => $step,
            //             'document_id' => $document->id,
            //             'destination_id' => $destinationId,
            //         ]);
            //     }
            // } else {
            //     foreach ($references as $reference) {
            //         $destinationId = $reference['id'];
            //         DocumentDestinationNoFeed::create([
            //             'document_id' => $document->id,
            //             'destination_id' => $destinationId,
            //         ]);
            //     }
            // }
            return response()->json([
                'message' => __('app_translation.success'),
                'document' => $document
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Urgencies error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
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
    public function destroy(string $id) {}
    // Utils
    private function translations($locale)
    {
        // Fetch destinations with their translations and the related destination type translations
        $query = Destination::whereHas('translations', function ($query) use ($locale) {
            // Filter the translations for each destination by locale
            $query->where('language_name', '=', $locale);
        })
            ->with([
                'translations' => function ($query) use ($locale) {
                    // Eager load only the 'value' column for translations filtered by locale
                    $query->select('id', 'value', 'created_at', 'translable_id')
                        // Eager load the translations for Destination filtered by locale
                        ->where('language_name', '=', $locale);
                },
                'type.translations' => function ($query) use ($locale) {
                    // Eager load only the 'value' column for translations filtered by locale
                    $query->select('id', 'value', 'created_at', 'translable_id')
                        // Eager load the translations for DestinationType filtered by locale
                        ->where('language_name', '=', $locale);
                }
            ])
            ->select('id', 'color', 'destination_type_id', 'created_at')
            ->get();

        // Process results and include the translations of DestinationType within each Destination
        // Transform the collection
        $query->each(function ($destination) {
            // Get the translated values for the destination
            $destinationTranslation = $destination->translations->first();

            // Set the transformed values for the destination
            $destination->id = $destination->id;
            $destination->name = $destinationTranslation->value;  // Translated name
            $destination->color = $destination->color;  // Translated color
            $destination->createdAt = $destination->created_at;

            // Get the translated values for the related DestinationType
            $destinationTypeTranslation = $destination->type->translations->first();

            // Add the related DestinationType translation
            $type = [
                "id" => $destination->destination_type_id,
                "name" => $destinationTypeTranslation->value,  // Translated name of the type
                "createdAt" => $destinationTypeTranslation->created_at
            ];
            unset($destination->type);  // Remove destinationType relation
            $destination->type = $type;

            // Remove unnecessary data from the destination object
            unset($destination->translations);  // Remove translations relation
            unset($destination->created_at);  // Remove translations relation
            unset($destination->destination_type_id);  // Remove translations relation
        });
        return $query;
    }
}
