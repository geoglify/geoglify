<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    use HasFactory;

    /**
     * Append the following attributes to the response.
     */
    protected $appends = ['width', 'length', 'cargo_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mmsi',
        'name',
        'dim_a',
        'dim_b',
        'dim_c',
        'dim_d',
        'imo',
        'callsign',
        'draught',
        'imo',
        'cargo_type_id'
    ];

    /**
     * Hide the following attributes from the response.
     * 
     * @var array<int, string>
     */
    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'dim_a',
        'dim_b',
        'dim_c',
        'dim_d',
        'cargo_type_id',
        'cargoType'
    ];

    /**
     * Get the cargo type associated with the ship.
     */
    public function cargoType()
    {
        return $this->belongsTo(CargoType::class);
    }

    /** 
     * Get width of the ship.
     */
    public function getWidthAttribute()
    {
        return $this->dim_a + $this->dim_b;
    }

    /** 
     * Get length of the ship.
     */
    public function getLengthAttribute()
    {
        return $this->dim_c + $this->dim_d;
    }

    /**
     * Get Cargo Name
     */
    public function getCargoNameAttribute()
    {
        return $this->cargoType ? $this->cargoType->name : 'Unknown'; // Return a default value if cargoType is null
    }
}
