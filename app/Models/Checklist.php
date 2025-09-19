<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    // A tabela checklists tem apenas `created_at`
    const UPDATED_AT = null;

    protected $fillable = [
        'run_id',
        'user_id',
        'vehicle_id',
    ];

    public function run()
    {
        return $this->belongsTo(Run::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        // Um checklist tem várias respostas
        return $this->hasMany(ChecklistAnswer::class);
    }
}
