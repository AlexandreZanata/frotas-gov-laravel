<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = ['title','is_group','created_by'];

    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function participants(){ return $this->hasMany(ChatConversationParticipant::class,'conversation_id'); }
    public function messages(){ return $this->hasMany(ChatMessage::class,'conversation_id'); }

    public function scopeForUser($q,int $userId){
        return $q->whereHas('participants', function($p) use ($userId){
            $p->where('user_id',$userId)->whereNull('declined_at');
        });
    }
}

