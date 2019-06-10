<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SecretTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:secret';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the app secrets';

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
        if (isset($_SERVER['APP_SECRETS'])) {
            $this->info('We have secrets!');
            $secrets = json_decode(file_get_contents($_SERVER['APP_SECRETS']), true);
            dump($secrets);
        } else {
            $this->info('No secrets here!');
        }
    }
}
