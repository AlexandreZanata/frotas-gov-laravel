<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['title','body','style','scope','secretariat_id','created_by'];

    protected $casts = [ 'style'=>'array' ];

    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function secretariat(){ return $this->belongsTo(Secretariat::class); }
}

