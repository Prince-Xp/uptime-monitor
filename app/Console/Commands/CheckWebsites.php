<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsiteJob;
use App\Models\Website;
use Illuminate\Console\Command;

class CheckWebsites extends Command
{
    protected $signature = 'websites:check';
    protected $description = 'Dispatch a queued uptime check for every monitored website';

    public function handle(): int
    {
        $count = 0;

        Website::query()->chunkById(200, function ($websites) use (&$count) {
            foreach ($websites as $website) {
                CheckWebsiteJob::dispatch($website)->onQueue('monitoring');
                $count++;
            }
        });

        $this->info("Dispatched {$count} website check(s).");
        return self::SUCCESS;
    }
}