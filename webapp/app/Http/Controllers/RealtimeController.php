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
        $ships = ShipLatestPositionView::limit(1000)
            ->orderBy('position_updated_at', 'desc')
            ->get();
        
        return Inertia::render('realtime/Index', [
            'ships' => $ships,
        ]);
    }

}
