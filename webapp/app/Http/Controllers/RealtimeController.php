<?php

namespace App\Http\Controllers;

use App\Models\ShipLatestPositionView;
use Inertia\Inertia;

class RealtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ships = ShipLatestPositionView::all();
        
        return Inertia::render('realtime/Index', [
            'ships' => $ships,
        ]);
    }

}
