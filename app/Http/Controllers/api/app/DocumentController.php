<?php

namespace App\Http\Controllers\api\app;

use App\Enums\AdverbEnum;
use App\Enums\DestinationTypeEnum;
use App\Enums\LanguageEnum;
use App\Enums\ScanTypeEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\document\DocumentStoreRequest;
use App\Http\Requests\app\document\DocumentUpdateRequest;
use App\Http\Requests\app\document\RecievedFromDeputyRequest;
use App\Http\Requests\app\document\RecievedFromDirectorateRequest;
use App\Models\Destination;
use App\Models\Document;
use App\Models\DocumentAdverb;
use App\Models\DocumentDestination;
use App\Models\DocumentDestinationNoFeed;
use App\Models\DocumentDestinationNoFeedBack;
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
use Illuminate\Support\Facades\Validator;


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
    public function updateInformation(Request $request)
    {
        $request->validated();



        return response()->json([
            'information' => ""
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function recievedFromDeputy(RecievedFromDeputyRequest $request)
    {
        $request->validated();
        $references = json_decode($request->reference, true);
        $keep = $request->keep == "true";
        if (!$keep) {
            if (empty($references)) {
                // Return a response if the reference array is empty
                return response()->json([
                    'errors' => [
                        "reference" => __('validation.required', ['attribute' => __('validation.attributes.reference')])
                    ]
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
        }

        // 1. check document
        $document = Document::with(['type:id,name'])
            ->find($request->id);
        if ($document) {
            // 2. Find Document Destination
            $documentDestination = Auditable::whereAndDecrypt(DocumentDestination::class, 'document_id', $document->id);
            if ($documentDestination) {
                // 3. Update deputy data
                Auditable::updateEncryptedData(DocumentDestination::class, [
                    'id' => $documentDestination['id'],
                    'feedback' => $request->feedback,
                    'feedback_date' => $request->feedback_date,
                ], $documentDestination["id"]);
                Auditable::insertAuditByArray(DocumentDestination::class, $documentDestination, $documentDestination->id);
                // 4. Store scan file
                $uploadedDoc = $this->storeDocument($request, $document->type->name);
                if ($uploadedDoc == null) {
                    return response()->json([
                        'message' => __('app_translation.file_not_found')
                    ], 404, [], JSON_UNESCAPED_UNICODE);
                }
                Scan::create([
                    "path" => $uploadedDoc['path'],
                    "name" => $uploadedDoc['name'],
                    "scan_type_id" => ScanTypeEnum::after_muqam_scan->value,
                    "document_id" => $document->id,
                    "document_destination_id" => $documentDestination->id
                ]);
                // 5. Check If It is keep change status_id to keep
                if ($keep) {
                    $document->status_id = StatusEnum::keep->value;
                    $document->save();
                    return response()->json([
                        'message' => __('app_translation.success')
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                }
                // 6. Else it is under progress
                // Store references
                if ($request->hasFeedback == "true") {
                    $nextStep = $documentDestination->step + 1;
                    foreach ($references as $reference) {
                        $destinationId = $reference['id'];
                        $insertedId = Auditable::insertEncryptedData(DocumentDestination::class, [
                            'step' => $nextStep,
                            "document_id" => $document->id,
                            "deadline" => $request->deadline,
                            "destination_id" => $destinationId,
                            "reciever_user_id" => request()->user()->id
                        ]);
                        $newDestination = DocumentDestination::find($insertedId);
                        Auditable::insertAudit($newDestination, $insertedId);
                        $nextStep = $nextStep + 1;
                    }
                } else {
                    // 1. Store references
                    foreach ($references as $reference) {
                        $destinationId = $reference['id'];
                        DocumentDestinationNoFeedBack::create([
                            'document_id' => $document->id,
                            'destination_id' => $destinationId,
                            "reciever_user_id" => request()->user()->id
                        ]);
                    }
                    // Store qaid sadira
                    $insertedId = Auditable::insertEncryptedData(DocumentAdverb::class, [
                        "date" => $request->qaidSadiraDate,
                        "number" => $request->qaidSadiraNumber,
                        "document_id" => $document->id,
                        "adverb_type_id" => AdverbEnum::qaidSadira->value
                    ]);
                    $cocumentAdverb = DocumentAdverb::find($insertedId);
                    Auditable::insertAudit($cocumentAdverb, $insertedId);
                    $documentToUpdate = Auditable::selectAndDecrypt(Document::class, $document->id);
                    // 2. Change document status_id
                    // 3. Lock the document that no furthers change can be applied
                    // 4. Encrypt savedFile
                    $documentToUpdate['status_id'] = StatusEnum::complete->value;
                    $documentToUpdate['locked'] = 1;
                    $documentToUpdate['saved_file'] = $request->savedFile;
                    Auditable::updateEncryptedData(Document::class, $documentToUpdate, $document->id);
                    Auditable::insertAuditByArray(Document::class, $documentToUpdate, $document->id);
                }
            } else {
                return response()->json([
                    'message' => __('app_translation.document_destination_not_found')
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json([
                'message' => __('app_translation.document_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json([
            'message' => __('app_translation.success')
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function recievedFromDirectorate(RecievedFromDirectorateRequest $request)
    {
        $request->validated();
        // 1. Find Document Destination
        $documentDestination = Auditable::whereAndDecrypt(DocumentDestination::class, 'id', $request->destination_id);
        if ($documentDestination) {
            $document = Document::with(['type:id,name'])
                ->find($documentDestination->document_id);
            // 2. Update Document Destination
            Auditable::updateEncryptedData(DocumentDestination::class, [
                'id' => $documentDestination['id'],
                'feedback' => $request->feedback,
                'feedback_date' => $request->feedback_date,
            ], $documentDestination["id"]);
            Auditable::insertAudit($documentDestination, $documentDestination->id);

            if ($request->last == "true") {
                // 4. Insert DocumentAdverb
                $insertedId = Auditable::insertEncryptedData(DocumentAdverb::class, [
                    "date" => $request->qaidSadiraDate,
                    "number" => $request->qaidSadiraNumber,
                    "document_id" => $document->id,
                    "adverb_type_id" => AdverbEnum::qaidSadira->value
                ]);
                $cocumentAdverb = DocumentAdverb::find($insertedId);
                Auditable::insertAudit($cocumentAdverb, $insertedId);
                $documentToUpdate = Auditable::selectAndDecrypt(Document::class, $document->id);
                // 2. Change document status_id
                // 3. Lock the document that no furthers change can be applied
                // 4. Encrypt savedFile
                $documentToUpdate['status_id'] = StatusEnum::complete->value;
                $documentToUpdate['locked'] = 1;
                $documentToUpdate['saved_file'] = $request->savedFile;
                Auditable::updateEncryptedData(Document::class, $documentToUpdate, $document->id);
                Auditable::insertAuditByArray(Document::class, $documentToUpdate, $document->id);
            }

            // 3. Store scan file
            $uploadedDoc = $this->storeDocument($request, $document->type->name);
            if ($uploadedDoc == null) {
                return response()->json([
                    'message' => __('app_translation.file_not_found')
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
            Scan::create([
                "path" => $uploadedDoc['path'],
                "name" => $uploadedDoc['name'],
                "scan_type_id" => ScanTypeEnum::final_scan->value,
                "document_id" => $document->id,
                "document_destination_id" => $documentDestination->id
            ]);
        } else {
            return response()->json([
                'message' => __('app_translation.document_destination_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json([
            'message' => __('app_translation.success')
        ], 200, [], JSON_UNESCAPED_UNICODE);
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
    }

    public function update(DocumentUpdateRequest $request)
    {
        // 1. Validate
        $request->validated();

        // 2. Check records exist
        $document = Auditable::selectAndDecrypt(Document::class, $request->id);
        if (!$document) {
            return response()->json([
                'message' => __('app_translation.document_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $documentType = DocumentType::find($request->documentTypeId);
        if (!$documentType) {
            return response()->json([
                'message' => __('app_translation.destination_type_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $source = Source::find($request->sourceId);
        if (!$source) {
            return response()->json([
                'message' => __('app_translation.source_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $urgency = Urgency::find($request->urgencyId);
        if (!$urgency) {
            return response()->json([
                'message' => __('app_translation.urgency_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        // 3. Update
        $document['document_date'] = $request->documentDate;
        $document['document_number'] = $request->documentNumber;
        $document['summary'] = $request->subject;
        $document['urgency_id'] = $urgency->id;
        $document['source_id'] = $source->id;
        $document['document_type_id'] = $documentType->id;
        // 4. Update Adverbs
        $adverb = DocumentAdverb::where('document_id', '=', $request->id)
            ->where('adverb_type_id', '=', AdverbEnum::qaidWarida->value)
            ->first();
        $adverb->date = $request->qaidWaridaDate;
        $adverb->number = $request->qaidWarida;
        // 4. Store
        $adverb->save();
        Auditable::updateEncryptedData(Document::class, $document, $request->id);
        Auditable::insertAuditByArray(Document::class, $document, $request->id);

        return response()->json([
            'message' => __('app_translation.success')
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function progress($id)
    {
        $locale = App::getLocale();
        $encryption_key = config('encryption.aes_key'); // The encryption key used for AES_DECRYPT (replace with your actual key)
        $progress =  DB::select('CALL GetDocProgress(:doc_id, :encryption_key,:lang)', [
            'doc_id' => $id,
            'encryption_key' => $encryption_key,
            'lang' => $locale
        ]);

        return response()->json([
            'progress' => $progress
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function scans($id)
    {
        $locale = App::getLocale();
        $progress =  DB::select('CALL GetDocScans(:doc_id, :lang)', [
            'doc_id' => $id,
            'lang' => $locale,
        ]);

        return response()->json([
            'scans' => $progress
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function destination($id)
    {
        $locale = App::getLocale();

        if ($locale === LanguageEnum::default->value) {
            $record = DocumentDestination::where("document_id", '=', $id)
                ->join('destinations', 'document_destinations.destination_id', '=', 'destinations.id')
                ->whereNull('document_destinations.feedback_date')  // Check if feedback_date is not null
                ->select("destinations.name", 'destinations.destination_type_id', "document_destinations.id")
                ->orderBy('document_destinations.step', 'desc') // Order by step to get the latest
                ->get();
            return response()->json(
                [
                    "destination" => $record
                ],
                200,
                [],
                JSON_UNESCAPED_UNICODE
            );
        } else {
            $record = DocumentDestination::where("document_id", '=', $id)
                ->join('destinations', 'document_destinations.destination_id', '=', 'destinations.id')
                ->whereNull('document_destinations.feedback_date')  // Check if feedback_date is not null
                ->join('translates', function ($join) use ($locale) {
                    $join->on('translates.translable_id', '=', 'destinations.id')
                        ->where('translates.translable_type', '=', Destination::class)
                        ->where('translates.language_name', '=', $locale); // Fixed: Removed trailing space
                })
                ->select("translates.value AS name", "document_destinations.id", 'destinations.destination_type_id')
                ->orderBy('document_destinations.step', 'desc') // Order by step to get the latest
                ->groupBy('destinations.id') // Group by these fields to avoid duplicates
                ->get();  // Retrieve the first (and only) result
            return response()->json(
                [
                    "destination" => $record
                ],
                200,
                [],
                JSON_UNESCAPED_UNICODE
            );
        }
    }
    public function changeDeputy(Request $request)
    {
        $request->validate([
            "document_id" => "required",
            "destination" => "required",
        ]);
        // 1. Find destination 
        $destination = Destination::find($request->destination);
        if ($destination) {
            // Find document destination for now only one record exist 
            // Front-end is handled in a way which allows like this
            $documentDestination = DocumentDestination::where('document_id', '=', $request->document_id)->get();
            if ($documentDestination->count() === 1) {
                // 2. Update Document Destination
                $item = $documentDestination->first();
                $item->destination_id = $request->destination;
                // 3. Save
                $item->save();
            } else {
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json([
                'message' => __('app_translation.destination_not_found'),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json([
            'message' => __('app_translation.success')
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(DocumentStoreRequest $request)
    {
        $request->validated();
        // 1. validate
        $source = Source::find($request->source);
        if (!$source) {
            return response()->json([
                'message' => __('app_translation.source_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $reference = Destination::find($request->reference);
        if (!$reference) {
            return response()->json([
                'message' => __('app_translation.reference_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $urgency = Urgency::find($request->urgency);
        if (!$urgency) {
            return response()->json([
                'message' => __('app_translation.urgency_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $status = Status::find(StatusEnum::inProgres->value);
        if (!$status) {
            return response()->json([
                'message' => __('app_translation.status_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        $documentType = DocumentType::find($request->documentType);
        if (!$documentType) {
            return response()->json([
                'message' => __('app_translation.destination_type_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        // 2. store document
        $uploadedDoc = $this->storeDocument($request, $documentType->name);
        if ($uploadedDoc == null) {
            return response()->json([
                'message' => __('app_translation.file_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
        // 1. Insert document
        $insertedId = Auditable::insertEncryptedData(Document::class, [
            "document_number" => $request->documentNumber,
            "summary" => $request->subject,  // The value to be encrypted
            "document_date" => $request->documentDate,
            "locked" => false,
            "old_doc" => false,  // "old" column
            "document_type_id" => $documentType->id,
            "status_id" => StatusEnum::inProgres->value,
            "urgency_id" => $request->urgency,
            "source_id" => $request->source,
            "reciever_user_id " => request()->user()->id,
        ]);
        $document = Document::find($insertedId);
        if ($document) {
            Auditable::insertAudit($document, $insertedId);
            // 2. Insert destination
            $insertedId = Auditable::insertEncryptedData(DocumentDestination::class, [
                "step" => 1,
                "document_id" => $document->id,
                "destination_id" => $reference->id,
                "reciever_user_id" => request()->user()->id
            ]);
            $documentDestination = DocumentDestination::find($insertedId);
            Auditable::insertAudit($documentDestination, $insertedId);
            // 3. Insert scan
            Scan::create([
                "path" => $uploadedDoc['path'],
                "name" => $uploadedDoc['name'],
                "scan_type_id" => ScanTypeEnum::initail_scan->value,
                "document_id" => $document->id,
                "document_destination_id" => $documentDestination->id
            ]);

            // 4. Insert DocumentAdverb
            $insertedId = Auditable::insertEncryptedData(DocumentAdverb::class, [
                "date" => $request->qaidWaridaDate,
                "number" => $request->qaidWarida,
                "document_id" => $document->id,
                "adverb_type_id" => AdverbEnum::qaidWarida->value
            ]);
            $cocumentAdverb = DocumentAdverb::find($insertedId);
            Auditable::insertAudit($cocumentAdverb, $insertedId);
        } else {
            return response()->json([
                'message' => __('app_translation.failed'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
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
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy($id)
    {
        $document = Document::find($id);
        if ($document) {
            if ($document->locked == "1" || $document->status_id == StatusEnum::complete->value) {
                return response()->json([
                    'message' => __('app_translation.document_locked_error'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
            $scans = Scan::where("document_id", '=', $document->id)->get();
            if ($scans) {
                // 1. Delete scans
                $scans = Scan::where("document_id", '=', $id)->get();
                foreach ($scans as $scan) {
                    $path = storage_path('app/' . "{$scan->path}");
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
                // 2. Delete destinations
                DocumentDestination::where("document_id", $document->id)->delete();
                DocumentDestinationNoFeedBack::where("document_id", $document->id)->delete();
                // 2. Delete Document Adverb
                DocumentAdverb::where("document_id", $document->id)->delete();
                // 3. Delete actual document
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
