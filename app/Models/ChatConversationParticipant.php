<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id','user_id','invited_at','accepted_at','declined_at','left_at','is_admin'
    ];

    protected $casts = [
        'invited_at'=>'datetime','accepted_at'=>'datetime','declined_at'=>'datetime','left_at'=>'datetime','is_admin'=>'boolean'
    ];

    public function conversation(){ return $this->belongsTo(ChatConversation::class,'conversation_id'); }
    public function user(){ return $this->belongsTo(User::class); }

    public function scopeActive($q){
        return $q->whereNull('declined_at')->whereNull('left_at');
    }

    public function hasAccepted(): bool { return !is_null($this->accepted_at) && is_null($this->declined_at); }
}

