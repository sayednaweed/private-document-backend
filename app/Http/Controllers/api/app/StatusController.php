<?php

namespace App\Http\Controllers\api\app;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\status\StatusStoreRequest;
use App\Models\Status;
use App\Models\Translate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class StatusController extends Controller
{
    public function statuses()
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value)
                $tr =  Status::select("name", 'id', 'created_at as createdAt', 'color')->orderBy('id', 'desc')->get();
            else {
                $tr = $this->getTableTranslationsWithJoin(Status::class, $locale, 'desc', [
                    'value as name',
                    'translable_id as id',
                    'Statuses.created_at as createdAt',
                    'color',
                ]);
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('statuses error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function status($id)
    {
        try {
            $status = Status::find($id);
            if ($status) {
                $data = [
                    "id" => $status->id,
                    "en" => $status->name,
                    "color" => $status->color,
                ];
                $translations = Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Status::class)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'status' =>  $data,
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

    public function store(StatusStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $status = Status::create([
                "name" => $payload["english"],
                "color" => $payload["color"],
            ]);
            if ($status) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $status->id, Status::class);
                $this->TranslatePashto($payload["pashto"], $status->id, Status::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'status' => [
                            "id" => $status->id,
                            "name" => $status->name,
                            "color" => $status->color,
                            "createdAt" => $status->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'status' => [
                            "id" => $status->id,
                            "name" => $payload["pashto"],
                            "color" => $status->color,
                            "createdAt" => $status->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'status' => [
                            "id" => $status->id,
                            "name" => $payload["farsi"],
                            "color" => $status->color,
                            "createdAt" => $status->created_at
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

    public function update(StatusStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in UrgencyStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $status = Status::find($request->id);
            if ($status) {
                $locale = App::getLocale();
                // 1. Update
                $status->name = $payload['english'];
                $status->color = $payload['color'];
                $status->save();
                $translations = Translate::where("translable_id", "=", $status->id)
                    ->where('translable_type', '=', Status::class)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $status->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $status->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'status' => $status,
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
            $status = Status::find($id);
            if ($status) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Status::class)->delete();
                $status->delete();
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
