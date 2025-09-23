<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FineStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = ['fine_id','user_id','from_status','to_status'];

    public function fine(){ return $this->belongsTo(Fine::class); }
    public function user(){ return $this->belongsTo(User::class); }
}

