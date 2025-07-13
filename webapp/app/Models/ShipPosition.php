<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Model;

class ShipPosition extends Model
{
    use HasUserAudit;

    protected $fillable = [
        'ship_id',
        'mmsi',
        'latitude',
        'longitude',
        'cog',
        'sog',
        'heading',
        'nav_status',
        'timestamp',
        'geometry',
        'geojson',
        'properties',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'cog' => 'decimal:1',
        'sog' => 'decimal:1',
        'heading' => 'integer',
        'nav_status' => 'integer',
        'timestamp' => 'datetime',
        'properties' => 'array',
    ];

    // ...existing code...
    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
