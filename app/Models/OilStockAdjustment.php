<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class OilStockAdjustment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'oil_product_id','user_id','type','quantity','unit_cost_at_time','reason'
    ];

    public function product()
    {
        return $this->belongsTo(OilProduct::class,'oil_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name'=>'Sistema']);
    }
}

