<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    use HasFactory;

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
}
