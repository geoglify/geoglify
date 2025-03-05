<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipLatestPositionView extends Model
{
    // Define that this model maps to a view (not a physical table)
    protected $table = 'ships_latest_positions_view';

    // No need to define fillable attributes for a read-only view
    protected $guarded = [];

}
