<?php

namespace App\Http\Controllers\api;

use App\Enums\LanguageEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\role\RoleStoreRequest;
use App\Models\Role;
use App\Models\Translate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class RoleController extends Controller
{
    public function roles()
    {
        try {
            $excludedIds = [RoleEnum::super->value];
            return response()->json(Role::whereNotIn('id', $excludedIds)->select("name", 'id', 'created_at as createdAt')->get());
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function store(RoleStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $role = Role::create([
                "name" => $payload["english"]
            ]);
            if ($role) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $role->id, Role::class);
                $this->TranslatePashto($payload["pashto"], $role->id, Role::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'role' => [
                            "id" => $role->id,
                            "name" => $role->name,
                            "createdAt" => $role->created_at
                        ],
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'role' => [
                            "id" => $role->id,
                            "name" => $payload["pashto"],
                            "createdAt" => $role->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'role' => [
                            "id" => $role->id,
                            "name" => $payload["farsi"],
                            "createdAt" => $role->created_at
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
            $role = Role::find($id);
            if ($role) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)->delete();
                $role->delete();
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
    public function role($id)
    {
        try {
            $role = Role::find($id);
            if ($role) {
                $data = [
                    "id" => $role->id,
                    "en" => $role->name,
                ];
                $translations = Translate::where("translable_id", "=", $id)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'role' =>  $data,
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
    public function update(RoleStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in RoleStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $role = Role::find($request->id);
            if ($role) {
                $locale = App::getLocale();
                // 1. Update
                $role->name = $payload['english'];
                $role->save();
                $translations = Translate::where("translable_id", "=", $role->id)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $role->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $role->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'role' => $role,
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
