<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['code', 'description', 'cargo_category_id'];

    /**
     * Cargo category relationship
     */
    public function cargoCategory()
    {
        return $this->belongsTo(CargoCategory::class);
    }
}
