<?php

namespace App\Http\Controllers\api\app;

use App\Enums\LanguageEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\document\DocumentStoreRequest;
use App\Models\Destination;
use App\Models\Document;
use App\Models\DocumentsEnView;
use App\Models\DocumentsFaView;
use App\Models\DocumentsPsView;
use App\Models\DocumentType;
use App\Models\Scan;
use App\Models\Source;
use App\Models\Status;
use App\Models\Urgency;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class DocumentController extends Controller
{
    public function documents(Request $request, $page)
    {
        $locale = App::getLocale();
        $tr = [];
        $perPage = $request->input('per_page', 10); // Number of records per page
        $page = $request->input('page', 1); // Current page

        // Start building the query
        $query = [];
        if ($locale === LanguageEnum::default->value) {
            $query = DocumentsEnView::query();
        } else if ($locale === LanguageEnum::farsi->value) {
            $query = DocumentsFaView::query();
        } else {
            $query = DocumentsPsView::query();
        }
        // Apply date filtering conditionally if provided
        $startDate = $request->input('filters.date.startDate');
        $endDate = $request->input('filters.date.endDate');

        if ($startDate || $endDate) {
            // Apply date range filtering
            if ($startDate && $endDate) {
                $query->whereBetween('documentDate', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('documentDate', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('documentDate', '<=', $endDate);
            }
        }

        // Apply search filter if present
        $searchColumn = $request->input('filters.search.column');
        $searchValue = $request->input('filters.search.value');

        if ($searchColumn && $searchValue) {
            $allowedColumns = ['documentNumber', 'id'];

            // Ensure that the search column is allowed
            if (in_array($searchColumn, $allowedColumns)) {
                $query->where($searchColumn, 'like', '%' . $searchValue . '%');
            }
        }

        // Apply sorting if present
        $sort = $request->input('filters.sort'); // Sorting column
        $order = $request->input('filters.order', 'asc'); // Sorting order (default is 'asc')

        // Apply sorting by provided column or default to 'created_at'
        if ($sort && in_array($sort, ['documentNumber', 'status', 'urgency', 'type', 'source', 'deadline', "documentDate "])) {
            $query->orderBy($sort, $order);
        } else {
            // Default sorting if no sort is provided
            $query->orderBy("documentDate", $order);
        }

        // Apply pagination (ensure you're paginating after sorting and filtering)
        $tr = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json(
            [
                "documents" => $tr,
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
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
     * Store a newly created resource in storage.
     */
    public function store(DocumentStoreRequest $request)
    {
        $request->validated();
        try {
            // 1. validate
            $source = Source::find($request->source);
            if (!$source) {
                return response()->json([
                    'message' => __('app_translation.source_not_found')
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
            $urgency = Urgency::find($request->urgency);
            if (!$urgency) {
                return response()->json([
                    'message' => __('app_translation.urgency_not_found')
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
            $status = Status::find(StatusEnum::inProgres->value);
            if (!$status) {
                return response()->json([
                    'message' => __('app_translation.status_not_found')
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
            $documentType = DocumentType::find($request->documentType);
            if (!$documentType) {
                return response()->json([
                    'message' => __('app_translation.destination_type_not_found')
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
            // 2. store document
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
                'document' => [
                    "id" => $document->id,
                    "documentDate" => $document->document_date,
                    "documentNumber" => $document->document_number,
                    "createdAt" => $document->created_at,
                    "status" => $this->getTranslationWithNameColumn($status, Status::class),
                    "statusColor" => $status->color,
                    "urgency" => $this->getTranslationWithNameColumn($urgency, Urgency::class),
                    "type" => $this->getTranslationWithNameColumn($documentType, DocumentType::class),
                    "source" =>  $this->getTranslationWithNameColumn($source, Source::class),
                    "deadline" => null
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Urgencies error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy($id)
    {
        $document = Document::find($id);
        if ($document) {
            // 1. Delete documents
            $scan = Scan::find($document->scan_id);
            if ($scan) {
                $initailScan = storage_path('app/' . "{$scan->initail_scan}");
                if ($scan->initail_scan && file_exists($initailScan)) {
                    unlink($initailScan);
                }
                $muqamScan = storage_path('app/' . "{$scan->muqam_scan}");
                if ($scan->muqam_scan && file_exists($muqamScan)) {
                    unlink($muqamScan);
                }
                $finalScan = storage_path('app/' . "{$scan->final_scan}");
                if ($scan->final_scan && file_exists($finalScan)) {
                    unlink($finalScan);
                }
                $document->delete();
            } else {
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
            return response()->json([
                'message' => __('app_translation.success'),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => __('app_translation.failed'),
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }
    }
    // Utils
    // private function translations($locale)
    // {
    //     // Fetch destinations with their translations and the related destination type translations
    //     $query = Destination::whereHas('translations', function ($query) use ($locale) {
    //         // Filter the translations for each destination by locale
    //         $query->where('language_name', '=', $locale);
    //     })
    //         ->with([
    //             'translations' => function ($query) use ($locale) {
    //                 // Eager load only the 'value' column for translations filtered by locale
    //                 $query->select('id', 'value', 'created_at', 'translable_id')
    //                     // Eager load the translations for Destination filtered by locale
    //                     ->where('language_name', '=', $locale);
    //             },
    //             'type.translations' => function ($query) use ($locale) {
    //                 // Eager load only the 'value' column for translations filtered by locale
    //                 $query->select('id', 'value', 'created_at', 'translable_id')
    //                     // Eager load the translations for DestinationType filtered by locale
    //                     ->where('language_name', '=', $locale);
    //             }
    //         ])
    //         ->select('id', 'color', 'destination_type_id', 'created_at')
    //         ->get();

    //     // Process results and include the translations of DestinationType within each Destination
    //     // Transform the collection
    //     $query->each(function ($destination) {
    //         // Get the translated values for the destination
    //         $destinationTranslation = $destination->translations->first();

    //         // Set the transformed values for the destination
    //         $destination->id = $destination->id;
    //         $destination->name = $destinationTranslation->value;  // Translated name
    //         $destination->color = $destination->color;  // Translated color
    //         $destination->createdAt = $destination->created_at;

    //         // Get the translated values for the related DestinationType
    //         $destinationTypeTranslation = $destination->type->translations->first();

    //         // Add the related DestinationType translation
    //         $type = [
    //             "id" => $destination->destination_type_id,
    //             "name" => $destinationTypeTranslation->value,  // Translated name of the type
    //             "createdAt" => $destinationTypeTranslation->created_at
    //         ];
    //         unset($destination->type);  // Remove destinationType relation
    //         $destination->type = $type;

    //         // Remove unnecessary data from the destination object
    //         unset($destination->translations);  // Remove translations relation
    //         unset($destination->created_at);  // Remove translations relation
    //         unset($destination->destination_type_id);  // Remove translations relation
    //     });
    //     return $query;
    // }
    public function documentCount()
    {
        return response()->json([
            'counts' => [
                "total" => Document::count(),
                "completed" => Document::where('status_id', '=', StatusEnum::complete->value)->count(),
                "inProgress" => Document::where('status_id', '=', StatusEnum::inProgres->value)->count(),
                "keep" => Document::where('status_id', '=', StatusEnum::keep->value)->count(),
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
