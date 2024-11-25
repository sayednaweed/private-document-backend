<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogErrorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $logData;

    // Constructor to pass the log data
    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }

    // Handle method to log the error asynchronously in both file and model
    public function handle()
    {
        // Store the error details in the database
        ErrorLog::create([
            'error_message' => $this->logData['error_message'],
            'trace' => $this->logData['trace'],
            'error_code' => $this->logData['error_code'] ?? null,
            'exception_type' => $this->logData['exception_type'] ?? null,
            'user_id' => $this->logData['user_id'] ?? null,
            'username' => $this->logData['username'] ?? null,
            'ip_address' => $this->logData['ip_address'],
            'method' => $this->logData['method'],
            'uri' => $this->logData['uri'],
        ]);
    }
}
