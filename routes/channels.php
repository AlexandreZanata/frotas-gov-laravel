<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\{ChatConversationParticipant};

Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    $exists = ChatConversationParticipant::where('conversation_id',$conversationId)
        ->where('user_id',$user->id)
        ->whereNull('left_at')
        ->exists();
    return $exists ? ['id'=>$user->id,'name'=>$user->name] : false;
});

Broadcast::channel('chat.unread.{userId}', function($user,$userId){
    return (int)$user->id === (int)$userId ? ['id'=>$user->id] : false;
});
