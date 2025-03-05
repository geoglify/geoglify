<?php

namespace App\Http\Controllers;

use App\Models\ShipLatestPositionView;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ships = ShipLatestPositionView::all();
        
        return Inertia::render('dashboard/Index', [
            'ships' => $ships,
        ]);
    }

}
