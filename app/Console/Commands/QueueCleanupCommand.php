<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Support\Facades\DB;

class QueueCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes a jobs from the DB queue';

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
        dd(DB::table('jobs')->delete());

    }
}
