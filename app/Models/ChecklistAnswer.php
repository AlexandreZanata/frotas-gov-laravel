<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistAnswer extends Model
{
    use HasFactory;
    public $timestamps = false; // Não tem timestamps

    protected $fillable = [
        'checklist_id',
        'item_id',
        'status',
        'notes',
    ];

    public function item()
    {
        // A resposta pertence a um item de checklist
        return $this->belongsTo(ChecklistItem::class, 'item_id');
    }
}
