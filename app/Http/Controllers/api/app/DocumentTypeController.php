<?php

namespace App\Http\Controllers\api\app;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\document\DocumentTypeStoreRequest;
use App\Models\DocumentType;
use App\Models\Translate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class DocumentTypeController extends Controller
{
    public function documentTypes()
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value)
                $tr =  DocumentType::select("name", 'id', 'created_at as createdAt')->orderBy('id', 'desc')->get();
            else {
                $tr = $this->getTableTranslations(DocumentType::class, $locale, 'desc');
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function store(DocumentTypeStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $documentType = DocumentType::create([
                "name" => $payload["english"]
            ]);
            if ($documentType) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $documentType->id, DocumentType::class);
                $this->TranslatePashto($payload["pashto"], $documentType->id, DocumentType::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'documentType' => [
                            "id" => $documentType->id,
                            "name" => $documentType->name,
                            "createdAt" => $documentType->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'documentType' => [
                            "id" => $documentType->id,
                            "name" => $payload["pashto"],
                            "createdAt" => $documentType->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'documentType' => [
                            "id" => $documentType->id,
                            "name" => $payload["farsi"],
                            "createdAt" => $documentType->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                }

                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function destroy($id)
    {
        try {
            $documentType = DocumentType::find($id);
            if ($documentType) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', DocumentType::class)->delete();
                $documentType->delete();
                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function documentType($id)
    {
        try {
            $documentType = DocumentType::find($id);
            if ($documentType) {
                $data = [
                    "id" => $documentType->id,
                    "en" => $documentType->name,
                ];
                $translations = Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', DocumentType::class)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'documentType' =>  $data,
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function update(DocumentTypeStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in SourceStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $documentType = DocumentType::find($request->id);
            if ($documentType) {
                $locale = App::getLocale();
                // 1. Update
                $documentType->name = $payload['english'];
                $documentType->save();
                $translations = Translate::where("translable_id", "=", $documentType->id)
                    ->where('translable_type', '=', DocumentType::class)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $documentType->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $documentType->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'documentType' => [
                        "id" => $documentType->id,
                        "name" => $payload["farsi"],
                        "createdAt" => $documentType->created_at
                    ],
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
