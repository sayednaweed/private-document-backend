<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DisableEditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:disable-edit-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disables document edit after 24 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the current time
        $now = Carbon::now();

        // Update the boolean field to false where it's true, and created_at is more than 24 hours ago
        DB::table('documents')
            ->where('disabled', true)
            ->where('created_at', '<', $now->subHours(24)) // If created_at is older than 24 hours
            ->update(['disabled' => false]);

        $this->info('Document disabled is older than 24 hours have been updated to false.');
    }
}
