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
    protected $appends = ['width', 'length', 'status'];

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
        'cargoType',
        'lastKnownPosition',
        'lastRealtimePosition'
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
     * Get the latest historical position of the ship.
     */
    public function latestPosition()
    {
        return $this->hasOne(ShipHistoricalPosition::class)->latest();
    }

    /**
     * Get the last real-time position of the ship.
     */
    public function lastRealtimePosition()
    {
        return $this->hasOne(ShipRealtimePosition::class)->latest();
    }
    
    /**
     * Get the last known position of the ship (realtime or historical).
     */
    public function lastKnownPosition()
    {
        return $this->hasOne(ShipRealtimePosition::class)->latest()->union($this->hasOne(ShipHistoricalPosition::class)->latest());
    }

    /**
     * Get the historical positions of the ship.
     */
    public function historicalPositions()
    {
        return $this->hasMany(ShipHistoricalPosition::class);
    }

    /**
     * Get the country of the ship.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'mmsi_prefix', 'number');
    }

    /**
     * Get the MMSI prefix of the ship.
     */
    public function getMmsiPrefixAttribute()
    {
        return (int) substr($this->mmsi, 0, 3);
    }

    /**
     * Get the status of the ship (LIVE if real-time position exists, OFFLINE otherwise).
     */
    public function getStatusAttribute()
    {
        // Verifica se a posição em tempo real está disponível
        return $this->lastRealtimePosition ? 'LIVE' : 'OFFLINE';
    }

    /**
     * Get the real-time position of the ship.
     */
    public function realtimePosition()
    {
        return $this->hasOne(ShipRealtimePosition::class);
    }
}
