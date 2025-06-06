<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;

class ShareTranslations
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('translations', self::export_translations());
        View::share('locale', app()->getLocale());
        return $next($request);
    }

    /**
     * Export translations from the 'lang' directory
     */
    function export_translations($locale = null)
    {
        // Use the current application locale if none is provided
        $locale = $locale ?? app()->getLocale();

        // Initialize an empty array to hold translations
        $translations = [];

        // Get the path to the 'lang' directory
        $langPath = base_path('lang/' . $locale);

        // Get all files in the lang directory
        $files = collect(array_diff(scandir($langPath), array('..', '.')));

        // Filter for specific file types, e.g., .php for translation files
        $files = $files->filter(function ($file) {
            return str_ends_with(strtolower($file), '.php'); // Adjust file extension as needed
        });

        // Loop through files and collect translations
        foreach ($files as $filename) {

            // Remove the .php extension and use the file name as the key
            $key = basename($filename, '.php');

            // Get translations for the file (i.e., the content of the PHP file)
            $translations[$key] = trans($key);
        }

        return $translations;
    }
}
