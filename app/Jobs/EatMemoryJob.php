<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EatMemoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $maxMemory;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($maxMemory = 0)
    {
        $this->maxMemory = $maxMemory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo self::class . PHP_EOL;

        // 0MB
        $base      = '';
        $maxBytes  = ($this->maxMemory) * 1024 * 1024;
        $usedBytes = memory_get_peak_usage(true);

        while ($usedBytes <= $maxBytes) {
            $base = $base . str_repeat('x', 1024 * 1024);
            $usedBytes = memory_get_peak_usage(true) + 5000;
            echo "Using " . round($usedBytes / (1024 * 1024)) . "MB" . PHP_EOL;
        }


    }
}
