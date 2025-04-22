<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecreateDatabaseViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recreate-database-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $views_dir = 'database/views';

        $files = collect(array_diff(scandir($views_dir), array('..', '.')));
        $files = $files->filter(function ($file) {
            return str_ends_with(strtolower($file), '.sql');
        });
        foreach ($files as $filename) {
            $viewName = str_replace('.sql', '', $filename);
            $viewContent = file_get_contents($views_dir . '/' . $filename);
            $this->info("Running database view script $viewName");
            DB::unprepared($viewContent);
        }
    }
}
