<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index()
    {
        //$this->authorize('map.index');
        return Inertia::render('Map/Index');
    }

    public function show(Map $map)
    {
        return Inertia::render('Map/Show');
    }

    public function store(Request $request)
    {
        return redirect()->route('map.index')->with('success', 'Map created successfully.');
    }

    public function update(Request $request, Map $map)
    {
        return redirect()->route('map.index')->with('success', 'Map updated successfully.');
    }

    public function destroy(Map $map)
    {
        return redirect()->route('map.index')->with('success', 'Map deleted successfully.');
    }
}
