<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use HasUserAudit, SoftDeletes;

    protected $fillable = [
        'layer_id',
        'geometry',
        'geojson',
        'properties',
        'created_by', 
        'updated_by', 
        'deleted_by',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function layer()
    {
        return $this->belongsTo(Layer::class);
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
