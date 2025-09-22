<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FineInfractionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fine_infraction_id','type','original_name','path','size'
    ];

    public function infraction(){ return $this->belongsTo(FineInfraction::class,'fine_infraction_id'); }
}

