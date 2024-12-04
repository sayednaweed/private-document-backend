<?php

namespace App\Http\Controllers\api\app;

use App\Enums\LanguageEnum;
use App\Enums\ScanTypeEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\document\DocumentStoreRequest;
use App\Models\Document;
use App\Models\DocumentsEnView;
use App\Models\DocumentsFaView;
use App\Models\DocumentsPsView;
use App\Models\DocumentType;
use App\Models\Scan;
use App\Models\Source;
use App\Models\Status;
use App\Models\Urgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Traits\template\Auditable;


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
        $document = Document::find($id)
            ->with([
                'status:id,name,color',
                'source:id,name',
                'urgency:id,name',
                'type:id,name',
                'documentDestination' => function ($query) {
                    $query->orderBy('id'); // Optionally order the related records
                }
            ])
            ->select('*') // Load all columns from the documents table
            ->get();

        // Append the first deadline from the `documentDestination` table
        // $documents->each(function ($document) {
        //     $document->deadline = $document->documentDestination->first()->deadline ?? null;
        // });

        return response()->json([
            'document' => $document
        ], 200, [], JSON_UNESCAPED_UNICODE);
        return;
    }
    public function information($id)
    {
        $locale = App::getLocale();
        $encryption_key = config('encryption.aes_key'); // The encryption key used for AES_DECRYPT (replace with your actual key)
        $information =  DB::selectOne('CALL GetDocInfo(:doc_id, :encryption_key,:lang)', [
            'doc_id' => $id,
            'encryption_key' => $encryption_key,
            'lang' => $locale,
        ]);

        return response()->json([
            'information' => $information
        ], 200, [], JSON_UNESCAPED_UNICODE);
        return;
    }


    public function store(DocumentStoreRequest $request)
    {
        $request->validated();
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
        $uploadedDoc = $this->storeDocument($request, $documentType->name);
        if ($uploadedDoc == null) {
            return response()->json([
                'message' => __('app_translation.file_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $insertedId = Auditable::insertEncryptedData(Document::class, [
            "document_number" => $request->documentNumber,
            "summary" => $request->subject,  // The value to be encrypted
            "muqam_statement" => "Hello Naweed",  // The value to be encrypted
            "qaid_warida_number" => $request->qaidWarida,
            "document_date" => $request->documentDate,
            "qaid_warida_date" => $request->qaidWaridaDate,
            "document_type_id" => $documentType->id,
            "status_id" => StatusEnum::inProgres->value,
            "urgency_id" => $request->urgency,
            "source_id" => $request->source,
            "reciever_user_id " => request()->user()->id,
            "old" => false,  // "old" column
        ]);
        $document = Document::find($insertedId);
        if ($document)
            Auditable::insertAudit($document, $insertedId);
        Scan::create([
            "path" => $uploadedDoc['path'],
            "name" => $uploadedDoc['name'],
            "scan_type_id" => ScanTypeEnum::initail_scan->value,
            "document_id" => $document->id,
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
    }

    public function destroy($id)
    {
        $document = Document::find($id);
        if ($document) {
            // 1. Delete documents
            $scans = Scan::where("document_id", '=', $document->id)->get();
            if ($scans) {
                $scans = Scan::where("document_id", '=', 1)->get();
                foreach ($scans as $scan) {
                    $path = storage_path('app/' . "{$scan->path}");
                    if (file_exists($path)) {
                        unlink($path);
                    }
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
