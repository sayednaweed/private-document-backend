<?php

namespace App\Http\Controllers\api\app;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\urgency\UrgencyStoreRequest;
use App\Models\Urgency;
use App\Models\Translate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class UrgencyController extends Controller
{
    public function urgencies()
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value)
                $tr =  Urgency::select("name", 'id', 'created_at as createdAt')->orderBy('id', 'desc')->get();
            else {
                $tr = $this->getTableTranslations(Urgency::class, $locale, 'desc');
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Urgencies error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function urgency($id)
    {
        try {
            $urgency = Urgency::find($id);
            if ($urgency) {
                $data = [
                    "id" => $urgency->id,
                    "en" => $urgency->name,
                ];
                $translations = Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Urgency::class)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'urgency' =>  $data,
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Urgency error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(UrgencyStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $urgency = Urgency::create([
                "name" => $payload["english"]
            ]);
            if ($urgency) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $urgency->id, Urgency::class);
                $this->TranslatePashto($payload["pashto"], $urgency->id, Urgency::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'urgency' => [
                            "id" => $urgency->id,
                            "name" => $urgency->name,
                            "createdAt" => $urgency->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'urgency' => [
                            "id" => $urgency->id,
                            "name" => $payload["pashto"],
                            "createdAt" => $urgency->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'urgency' => [
                            "id" => $urgency->id,
                            "name" => $payload["farsi"],
                            "createdAt" => $urgency->created_at
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

    public function update(UrgencyStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in UrgencyStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $urgency = Urgency::find($request->id);
            if ($urgency) {
                $locale = App::getLocale();
                // 1. Update
                $urgency->name = $payload['english'];
                $urgency->save();
                $translations = Translate::where("translable_id", "=", $urgency->id)
                    ->where('translable_type', '=', Urgency::class)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $urgency->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $urgency->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'urgency' => $urgency,
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
            $urgency = Urgency::find($id);
            if ($urgency) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Urgency::class)->delete();
                $urgency->delete();
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
}
