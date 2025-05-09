<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoCategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'color'];

    /**
     * Cargo types relationship
     */
    public function cargoTypes()
    {
        return $this->hasMany(CargoType::class);
    }
}
