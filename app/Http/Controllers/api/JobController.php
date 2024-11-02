<?php

namespace App\Http\Controllers\api;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\job\JobStoreRequest;
use App\Models\ModelJob;
use App\Models\Translate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class JobController extends Controller
{
    public function jobs()
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value)
                $tr =  ModelJob::select("name", 'id', 'created_at as createdAt')->orderBy('id', 'desc')->get();
            else {
                $tr = $this->getTableTranslations(ModelJob::class, $locale, 'desc');
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function store(JobStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $job = ModelJob::create([
                "name" => $payload["english"]
            ]);
            if ($job) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $job->id, ModelJob::class);
                $this->TranslatePashto($payload["pashto"], $job->id, ModelJob::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'job' => $job,
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'job' => [
                            "id" => $job->id,
                            "name" => $payload["pashto"],
                            "createdAt" => $job->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'job' => [
                            "id" => $job->id,
                            "name" => $payload["farsi"],
                            "createdAt" => $job->created_at
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
            $job = ModelJob::find($id);
            if ($job) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)->delete();
                $job->delete();
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
    public function job($id)
    {
        try {
            $job = ModelJob::find($id);
            if ($job) {
                $data = [
                    "id" => $job->id,
                    "en" => $job->name,
                ];
                $translations = Translate::where("translable_id", "=", $id)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'job' =>  $data,
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
    public function update(JobStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in JobStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $job = ModelJob::find($request->id);
            if ($job) {
                $locale = App::getLocale();
                // 1. Update
                $job->name = $payload['english'];
                $job->save();
                $translations = Translate::where("translable_id", "=", $job->id)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $job->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $job->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'job' => $job,
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
