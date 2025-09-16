<?php

namespace App\Models;

use App\Traits\Auditable; // 1. Importe o Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCategory extends Model
{
    use HasFactory, Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'layout_key',
        'oil_change_km',
        'oil_change_days',
    ];
}
