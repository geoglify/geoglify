<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ship;

class ShipRealtimePosition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ship_id',
        'cog',
        'sog',
        'hdg',
        'last_updated',
        'eta',
        'destination',
        'latitude',
        'longitude'
    ];
}
