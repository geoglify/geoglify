<?php

namespace App\Http\Controllers\Configurations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configurations\AisServerUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Configuration;
use Illuminate\Support\Facades\Redis;

class AisServerController extends Controller
{
    /**
     * Show the form for editing the AIS server's settings.
     */
    public function edit(Request $request): Response
    {
        $defaults = [
            'ais_host' => 'localhost',
            'ais_port' => 8080,
        ];

        // Save the default settings in the database if they don't exist
        $settings = collect($defaults)->mapWithKeys(function ($default, $key) {
            return [$key => Configuration::where('key', $key)->value('value') ?? $default];
        });

        // Save the settings in Redis
        $settings->each(function ($value, $key) {
            Redis::set($key, $value);
        });

        // Return the Inertia response with the settings
        return Inertia::render('configurations/AisServer', $settings->toArray());
    }

    /**
     * Update the AIS server's settings.
     */
    public function update(AisServerUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Configuration::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return to_route('ais-server.edit');
    }
}
