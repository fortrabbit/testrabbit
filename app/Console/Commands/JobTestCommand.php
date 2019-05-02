<?php

namespace App\Console\Commands;

use App\Jobs\EatMemoryJob;
use App\Jobs\RandomErrorJob;
use App\Jobs\SleepJob;
use Illuminate\Console\Command;

class JobTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:job {type : Type of job (error|sleep)} {--C|count=1} {--A|args=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put a job in the queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $max = (int)$this->option('count');
        $type = $this->argument('type');
        $jobArgs = $this->option('args');

        if (!in_array($type, ['error','sleep','memory'])) {
            $this->error("Unpexpected Job type '$type'");
            return 1;
        }

        foreach (range(1, $max) as $c) {
            $this->info("Dispatching new '$type' Job");
            if ($type === 'error') {
                RandomErrorJob::dispatch();
            }
            if ($type === 'sleep') {
                SleepJob::dispatch();
            }
            if ($type === 'memory') {
                EatMemoryJob::dispatch(...$jobArgs);
            }
        }

        return 0;

    }
}
