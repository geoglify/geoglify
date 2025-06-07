<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportTranslations extends Command
{
    protected $signature = 'lang:export {locale? : (Optional) Export only a specific locale}';
    
    protected $description = 'Exports Laravel translations to public/lang/*.json for use in the frontend';

    public function handle()
    {
        $basePath = base_path('lang');
        $locales = [];

        if ($locale = $this->argument('locale')) {
            $locales = [$locale];
        } else {
            // Get all folders inside resources/lang
            $locales = collect(File::directories($basePath))
                ->map(fn($path) => basename($path))
                ->toArray();
        }

        if (empty($locales)) {
            $this->warn('No locales found to export.');
            return Command::FAILURE;
        }

        File::ensureDirectoryExists(public_path('lang'));

        foreach ($locales as $locale) {
            $langPath = "{$basePath}/{$locale}";
            $translations = [];

            foreach (File::files($langPath) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $key = basename($file, '.php');
                $translations[$key] = require $file->getPathname();
            }

            $outputPath = public_path("lang/{$locale}.json");

            File::put($outputPath, json_encode(
                $translations,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ));

            $this->info("Exported: {$outputPath}");
        }

        return Command::SUCCESS;
    }
}
