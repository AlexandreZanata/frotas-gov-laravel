<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TireLayout extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','positions','description'];
    protected $casts = [ 'positions'=>'array' ];

    public function categories()
    {
        return $this->hasMany(VehicleCategory::class,'tire_layout_id');
    }
}

