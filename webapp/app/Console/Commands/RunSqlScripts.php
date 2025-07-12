<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunSqlScripts extends Command
{
    protected $signature = 'db:run-sql-scripts';
    protected $description = 'Execute all SQL scripts from database/sql directory';

    public function handle()
    {
        $path = database_path('sql');

        if (!is_dir($path)) {
            $this->error("Directory $path does not exist.");
            return;
        }

        $files = glob($path . '/**/*.sql');

        if (empty($files)) {
            $this->info("No SQL files found.");
            return;
        }

        foreach ($files as $file) {
            try {
                $sql = file_get_contents($file);
                DB::unprepared($sql);
                $this->info("Successfully executed: " . basename($file));
            } catch (\Exception $e) {
                $this->error("Error executing " . basename($file));
            }
        }

        $this->info('Done!');
    }
}