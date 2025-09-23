<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $conversationId, public int $userId) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.conversation.'.$this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'ChatTyping';
    }

    public function broadcastWith(): array
    {
        return [ 'conversation_id'=>$this->conversationId, 'user_id'=>$this->userId ];
    }
}

