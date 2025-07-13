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
        'cargo_type',
        'dim_a',
        'dim_b',
        'dim_c',
        'dim_d',
        'properties',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'properties' => 'array',
        'dim_a' => 'integer',
        'dim_b' => 'integer',
        'dim_c' => 'integer',
        'dim_d' => 'integer',
    ];

    // ...existing code...
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
