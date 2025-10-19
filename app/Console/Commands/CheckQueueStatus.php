<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckQueueStatus extends Command
{
    protected $signature = 'queue:status';

    protected $description = 'Check the status of queue jobs';

    public function handle()
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();

        $this->info("Pending jobs: {$pending}");
        $this->info("Failed jobs: {$failed}");

        if ($pending > 0) {
            $this->info('Next job will be processed soon...');
        }

        return 0;
    }
}
