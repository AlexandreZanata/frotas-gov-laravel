<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatUnreadPing implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $userId, public int $conversationId, public int $messageId) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.unread.'.$this->userId);
    }

    public function broadcastAs(): string
    {
        return 'ChatUnreadPing';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id'=>$this->conversationId,
            'message_id'=>$this->messageId
        ];
    }
}

