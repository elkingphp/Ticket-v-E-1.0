<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TestSystem extends Command
{
    protected $signature = 'system:check';
    protected $description = 'Check if all enterprise components are working';

    public function handle()
    {
        $this->info("Checking Database...");
        try {
            DB::connection()->getPdo();
            $this->info("✔ Database: Working");
        }
        catch (\Exception $e) {
            $this->error("✘ Database: Error - " . $e->getMessage());
        }

        $this->info("\nChecking Redis...");
        try {
            Redis::ping();
            $this->info("✔ Redis: Working");
        }
        catch (\Exception $e) {
            $this->error("✘ Redis: Error - " . $e->getMessage());
        }

        $this->info("\nChecking Cache...");
        try {
            Cache::put('test', 'ok', 10);
            if (Cache::get('test') === 'ok') {
                $this->info("✔ Cache: Working");
            }
            else {
                $this->error("✘ Cache: Failed to retrieve");
            }
        }
        catch (\Exception $e) {
            $this->error("✘ Cache: Error - " . $e->getMessage());
        }

        $this->info("\nChecking Logging...");
        try {
            Log::info("System check performed");
            $this->info("✔ Logging: Working");
        }
        catch (\Exception $e) {
            $this->error("✘ Logging: Error - " . $e->getMessage());
        }
    }
}