<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Model;

class ShipSnapshot extends Model
{
    use HasUserAudit;

    protected $fillable = [
        'ship_id',
        'mmsi',
        'snapshot_time',
        'properties',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'snapshot_time' => 'datetime',
        'properties' => 'array',
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

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
