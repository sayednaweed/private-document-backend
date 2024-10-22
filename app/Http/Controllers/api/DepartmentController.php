<?php

namespace App\Http\Controllers\api;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\department\DepartmentStoreRequest;
use App\Models\Department;
use App\Models\Translate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class DepartmentController extends Controller
{
    public function departments(Request $request)
    {
        try {
            $user = $request->user();
            if ($this->isAdminOrSuper($user)) {
                $locale = App::getLocale();
                $tr = [];
                if ($locale === LanguageEnum::default->value)
                    $tr =  Department::select("name", 'id', 'created_at as createdAt')->orderBy('id', 'desc')->get();
                else {
                    $tr = $this->getTableTranslations(Department::class, $locale, 'desc');
                }
                return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.unauthorized'),
                ], 403, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function store(DepartmentStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            // 1. Create
            $department = Department::create([
                "name" => $payload["english"]
            ]);
            if ($department) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $department->id, Department::class);
                $this->TranslatePashto($payload["pashto"], $department->id, Department::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'department' => $department,
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'department' => [
                            "id" => $department->id,
                            "name" => $payload["pashto"],
                            "createdAt" => $department->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'department' => [
                            "id" => $department->id,
                            "name" => $payload["farsi"],
                            "createdAt" => $department->created_at
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
            $department = Department::find($id);
            if ($department) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)->delete();
                $department->delete();
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
    public function department($id)
    {
        try {
            $department = Department::find($id);
            if ($department) {
                $data = [
                    "id" => $department->id,
                    "en" => $department->name,
                ];
                $translations = Translate::where("translable_id", "=", $id)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'department' =>  $data,
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
    public function update(DepartmentStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in DepartmentStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $department = Department::find($request->id);
            if ($department) {
                $locale = App::getLocale();
                // 1. Update
                $department->name = $payload['english'];
                $department->save();
                $translations = Translate::where("translable_id", "=", $department->id)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $department->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $department->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'department' => $department,
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
