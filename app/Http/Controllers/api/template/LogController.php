<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function databaseLogs()
    {
        $logs = ErrorLog::select('id', "error_message", "trace", "error_code", "exception_type", "user_id", "username", "ip_address", "method", "uri", 'created_at')
            ->get();
        // Return the formatted logs as JSON
        return response()->json([
            'status' => 'success',
            'logs' => $logs
        ]);
    }
    public function fileLogs()
    {
        // Path to the user_error.log file
        $logFilePath = storage_path('logs/user_error.log');

        // Check if the file exists
        if (!File::exists($logFilePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Log file not found.'
            ], 404);
        }

        // Get the contents of the user_error.log file
        $logContents = File::get($logFilePath);

        // Split the log contents into individual lines (assuming each log entry is on a separate line)
        $logLines = explode("\n", $logContents);

        $logs = [];

        // Iterate through each log line and parse it as JSON
        foreach ($logLines as $line) {
            // Skip empty lines
            if (empty($line)) continue;

            // Decode the JSON log entry
            $logData = json_decode($line, true);

            // Check if decoding was successful
            if ($logData) {
                $context = $logData['context'] ?? null;

                if ($context) {
                    // Add additional information to each log entry
                    $logs[] = [
                        'created_at' => $context['created_at'] ?? 'N/A',
                        'exception_type' => $context['exception_type'] ?? 'N/A',
                        'error_code' => $context['error_code'] ?? 'N/A',
                        'error_message' => $context['error_message'] ?? 'N/A',
                        'user_id' => $context['user_id'] ?? 'N/A',
                        'username' => $context['username'] ?? 'N/A',
                        'ip_address' => $context['ip_address'] ?? 'N/A',
                        'method' => $context['method'] ?? 'N/A',
                        'uri' => $context['uri'] ?? 'N/A',
                    ];
                }
            }
        }

        // Return the formatted logs as JSON
        return response()->json([
            'status' => 'success',
            'logs' => $logs
        ]);
    }
    public function clear()
    {
        $logFilePath = storage_path('logs/user_error.log');
        if (File::exists($logFilePath)) {
            File::put($logFilePath, ''); // Empty the log file
            return response()->json([
                'message' => __('app_translation.success')
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => __('app_translation.not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
