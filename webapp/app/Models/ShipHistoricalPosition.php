<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipHistoricalPosition extends Model
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
    
    /**
     * Hide the following attributes from the response.
     * 
     * @var array<int, string>
     */
    public $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'ship_id',
        'geom'
    ];
}
