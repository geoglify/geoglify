<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ship extends Model
{
    use HasUserAudit, SoftDeletes;

    protected $fillable = [
        'mmsi',
        'imo',
        'name',
        'call_sign',
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
        'deleted_by',
    ];

    protected $casts = [
        'dim_a' => 'integer',
        'dim_b' => 'integer',
        'dim_c' => 'integer',
        'dim_d' => 'integer',
        'ais_type' => 'integer',
        'cargo' => 'integer',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
    ];

    public function positions()
    {
        return $this->hasMany(ShipPosition::class);
    }

    public function shipInfo()
    {
        return $this->hasMany(ShipInfo::class);
    }

    public function latestInfo()
    {
        return $this->hasOne(ShipInfo::class)->latest('timestamp');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
