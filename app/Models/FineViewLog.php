<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FineViewLog extends Model
{
    use HasFactory;

    protected $fillable = ['fine_id','user_id','viewed_at','ip_address'];

    protected $casts = [ 'viewed_at'=>'datetime' ];

    public function fine(){ return $this->belongsTo(Fine::class); }
    public function user(){ return $this->belongsTo(User::class); }
}

