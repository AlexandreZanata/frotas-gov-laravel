<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;
    public $timestamps = false; // Não tem timestamps

    protected $fillable = ['name', 'description'];
}
