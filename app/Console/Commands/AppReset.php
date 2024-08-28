<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the application by running migrate:fresh and db:seed with the Starter seeder';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info(" Mengatur ulang aplikasi...");

        $this->info(" Menjalankan migrate:fresh...");
        Artisan::call('migrate:fresh', [], $this->getOutput());

        $this->info(" Mengisi database...");
        $bar = $this->output->createProgressBar(3);
        $bar->start();

        for ($i = 0; $i < 3; $i++) {
            sleep(1);
            $bar->advance();
        }

        $bar->finish();

        $this->info(" Mengisi database dengan data Starter...");
        Artisan::call('db:seed', ['--class' => 'Starter'], $this->getOutput());

        $this->info(" Aplikasi telah diatur ulang!");
    }
}
