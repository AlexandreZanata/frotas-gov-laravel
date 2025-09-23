<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id','user_id','type','is_system','body','attachment_meta','template_id','style_class','is_broadcast'
    ];

    protected $casts = [
        'is_system'=>'boolean',
        'is_broadcast'=>'boolean',
        'attachment_meta'=>'array'
    ];

    public function conversation(){ return $this->belongsTo(ChatConversation::class,'conversation_id'); }
    public function user(){ return $this->belongsTo(User::class); }
    public function reads(){ return $this->hasMany(ChatMessageRead::class,'message_id'); }
    public function template(){ return $this->belongsTo(ChatMessageTemplate::class,'template_id'); }
}
