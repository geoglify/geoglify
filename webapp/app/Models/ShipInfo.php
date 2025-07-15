<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUserAudit;

class ShipInfo extends Model
{
    use HasFactory, HasUserAudit;

    protected $table = 'ship_info';

    protected $fillable = [
        'ship_id',
        'mmsi',
        'name',
        'imo',
        'call_sign',
        'ais_type',
        'destination',
        'draught',
        'cargo',
        'dim_a',
        'dim_b',
        'dim_c',
        'dim_d',
        'length',
        'width',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'draught' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'mmsi' => 'integer',
        'imo' => 'integer',
        'ais_type' => 'integer',
        'cargo' => 'integer',
        'dim_a' => 'integer',
        'dim_b' => 'integer',
        'dim_c' => 'integer',
        'dim_d' => 'integer',
    ];

    /**
     * Get the ship that owns this info record.
     */
    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    /**
     * Scope to get latest info for each ship
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('timestamp', 'desc');
    }

    /**
     * Scope to filter by MMSI
     */
    public function scopeByMmsi($query, $mmsi)
    {
        return $query->where('mmsi', $mmsi);
    }
}
