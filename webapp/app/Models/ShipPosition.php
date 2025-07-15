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
        'imo',
        'name',
        'call_sign',
        'latitude',
        'longitude',
        'cog',
        'sog',
        'heading',
        'nav_status',
        'rot',
        'repeat',
        'channel',
        'utc',
        'smi',
        'timestamp',
        'geometry',
        'destination',
        'draught',
        'ais_type',
        'dim_a',
        'dim_b',
        'dim_c',
        'dim_d',
        'cargo',
        'length',
        'width',
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
        'rot' => 'decimal:3',
        'repeat' => 'integer',
        'utc' => 'integer',
        'timestamp' => 'datetime',
        'draught' => 'decimal:2',
    ];

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
