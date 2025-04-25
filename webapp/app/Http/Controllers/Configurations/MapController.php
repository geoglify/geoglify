<?php

namespace App\Http\Controllers\Configurations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configurations\MapUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Configuration;

class MapController extends Controller
{
    /**
     * Show the form for editing the map's settings.
     */
    public function edit(Request $request): Response
    {
        // Keys and default values
        $defaults = [
            'default_latitude' => 0,
            'default_longitude' => 0,
            'default_zoom' => 0,
            'default_bearing' => 0,
            'default_style' => 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
        ];

        // Load each setting from DB or fallback to default
        $settings = collect($defaults)->mapWithKeys(function ($default, $key) {
            return [$key => Configuration::where('key', $key)->value('value') ?? $default];
        });

        return Inertia::render('configurations/Map', $settings->toArray());
    }

    /**
     * Update the map's settings.
     */
    public function update(MapUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Configuration::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return to_route('map.edit');
    }
}
