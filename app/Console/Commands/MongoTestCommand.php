<?php

namespace App\Console\Commands;

use App\Jobs\MongoTestJob;
use Illuminate\Console\Command;

class MongoTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mongo {--queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tries to connect to mongo db';

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

        if ($this->option('queued')) {
            $this->info("Dispatching MongoTestJob");
            MongoTestJob::dispatch();
        } else {
            $this->info("Handing MongoTestJob");
            MongoTestJob::dispatchNow();
        }
        return 0;
    }
}
