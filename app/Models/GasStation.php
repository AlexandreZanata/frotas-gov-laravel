<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GasStation extends Model
{
    use HasFactory;

    protected $fillable = ['name','status'];
    public $timestamps = false; // migration não cria timestamps
}
