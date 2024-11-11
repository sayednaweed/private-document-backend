<?php

namespace App\Http\Controllers\api\app;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\source\SourceStoreRequest;
use App\Models\Source;
use App\Models\Translate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class SourceController extends Controller
{
    public function sources()
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value)
                $tr =  Source::select("name", 'id', 'created_at as createdAt')->orderBy('id', 'desc')->get();
            else {
                $tr = $this->getTableTranslations(Source::class, $locale, 'desc');
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function store(SourceStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $source = Source::create([
                "name" => $payload["english"]
            ]);
            if ($source) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $source->id, Source::class);
                $this->TranslatePashto($payload["pashto"], $source->id, Source::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'source' => [
                            "id" => $source->id,
                            "name" => $source->name,
                            "createdAt" => $source->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'source' => [
                            "id" => $source->id,
                            "name" => $payload["pashto"],
                            "createdAt" => $source->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'source' => [
                            "id" => $source->id,
                            "name" => $payload["farsi"],
                            "createdAt" => $source->created_at
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
            $source = Source::find($id);
            if ($source) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Source::class)->delete();
                $source->delete();
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
    public function source($id)
    {
        try {
            $source = Source::find($id);
            if ($source) {
                $data = [
                    "id" => $source->id,
                    "en" => $source->name,
                ];
                $translations = Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Source::class)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'source' =>  $data,
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
    public function update(SourceStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in SourceStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $source = Source::find($request->id);
            if ($source) {
                $locale = App::getLocale();
                // 1. Update
                $source->name = $payload['english'];
                $source->save();
                $translations = Translate::where("translable_id", "=", $source->id)
                    ->where('translable_type', '=', Source::class)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $source->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $source->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'source' => $source,
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
