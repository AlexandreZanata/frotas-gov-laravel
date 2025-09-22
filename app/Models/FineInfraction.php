<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\FineInfractionAttachment;

class FineInfraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fine_id','code','description','base_amount','extra_fixed','extra_percent','discount_fixed','discount_percent','final_amount','infraction_date','due_date','notes'
    ];

    protected $casts = [
        'infraction_date'=>'date',
        'due_date'=>'date'
    ];

    protected static function booted(): void
    {
        static::saving(function(FineInfraction $inf){
            $base = (float)$inf->base_amount;
            $extra = (float)$inf->extra_fixed + ($base * ((float)$inf->extra_percent/100));
            $discount = (float)$inf->discount_fixed + ($base * ((float)$inf->discount_percent/100));
            $final = \max(0, \round($base + $extra - $discount, 2));
            $inf->final_amount = $final;
        });
        static::saved(function(FineInfraction $inf){
            $fine = $inf->fine; if ($fine) { $fine->recalculateTotal(); }
        });
        static::deleted(function(FineInfraction $inf){
            $fine = $inf->fine; if ($fine) { $fine->recalculateTotal(); }
        });
    }

    public function fine(){ return $this->belongsTo(Fine::class); }
    public function attachments(){ return $this->hasMany(FineInfractionAttachment::class); }
}
