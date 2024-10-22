<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    public function show($storage, $folder, $filename)
    {
        try {
            $path = storage_path('app/' . "{$storage}/{$folder}/{$filename}");
            if (!file_exists($path)) {
                return response()->json("Not found");
            }

            return response()->file($path);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json(['message' => "Something went wrong please try again later!"], 500);
        }
    }
}
