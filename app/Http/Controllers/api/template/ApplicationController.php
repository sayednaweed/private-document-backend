<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    public function changeLocale($locale)
    {
        try {
            // Set the language in a cookie
            if ($locale === "en" || $locale === "fa" || $locale === "ps") {
                // 1. Set app language
                App::setLocale($locale);
                return response()->json([
                    'message' => __('app_translation.lang_change_success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                // 3. Passed language not exist in system
                response()->json([
                    'message' => __('app_translation.lang_change_failed'),
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            response()->json([
                'message' => __('app_translation.server_error'),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function font($direction)
    {
        try {
            $path = storage_path("app/public/fonts/");
            if ($direction === "rtl") {
                $path = $path . "nazanin.ttf";

                if (!file_exists($path)) {
                    return response()->json("Not found");
                }
                return response()->file($path);
            }
            return response()->json(["message" => "Not allowed", 405]);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json(['message' => "Something went wrong please try again later!"], 500);
        }
    }
}
