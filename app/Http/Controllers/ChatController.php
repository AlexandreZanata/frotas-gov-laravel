<?php
namespace App\Http\Controllers;

use App\Models\{ChatConversation, ChatConversationParticipant, ChatMessage, ChatMessageRead, User};
use App\Http\Requests\{CreateChatConversationRequest, ChatSendMessageRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    private function chatTablesReady(): bool
    {
        return Schema::hasTable('chat_conversations') && Schema::hasTable('chat_conversation_participants') && Schema::hasTable('chat_messages');
    }

    private function ensureParticipant(ChatConversation $conversation, int $userId): ChatConversationParticipant
    {
        $p = $conversation->participants()->where('user_id',$userId)->first();
        if (!$p || $p->declined_at || $p->left_at) {
            abort(403,'Você não participa desta conversa.');
        }
        return $p;
    }

    public function index(Request $request)
    {
        if (!$this->chatTablesReady()) {
            return view('chat.missing');
        }
        $userId = $request->user()->id;
        $conversations = ChatConversation::with(['participants.user:id,name','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->forUser($userId)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();
        return view('chat.index', compact('conversations'));
    }

    public function list(Request $request)
    {
        if (!$this->chatTablesReady()) {
            return response()->json(['data'=>[], 'warning'=>'Chat tables not migrated'], 200);
        }
        $userId = $request->user()->id;
        $conversations = ChatConversation::with(['participants.user:id,name','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->forUser($userId)
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get()
            ->map(fn($c)=>[
                'id'=>$c->id,
                'title'=>$c->title ?? $this->deriveTitle($c,$userId),
                'is_group'=>$c->is_group,
                'last_message'=>$c->messages->first()?->body,
                'updated_at'=>$c->updated_at?->toIso8601String(),
                'participants'=>$c->participants->map(fn($p)=>['id'=>$p->user_id,'name'=>$p->user->name,'accepted'=>$p->hasAccepted()])->values(),
            ]);
        return response()->json(['data'=>$conversations]);
    }

    private function deriveTitle(ChatConversation $c, int $viewerId): string
    {
        if ($c->title) return $c->title;
        if (!$c->is_group) {
            $other = $c->participants->firstWhere('user_id','!=',$viewerId);
            return $other?->user?->name ?? 'Conversa';
        }
        return 'Grupo #'.$c->id;
    }

    public function store(CreateChatConversationRequest $request)
    {
        if (!$this->chatTablesReady()) {
            return response()->json(['message'=>'Chat não migrado'], 503);
        }
        $data = $request->validated();
        $user = $request->user();
        $participantIds = collect($data['participants'] ?? [])->unique()->take(50)->values();
        $isGroup = $data['is_group'] ?? ($participantIds->count() > 1);
        $conversation = null;
        DB::transaction(function() use (&$conversation,$user,$data,$participantIds,$isGroup) {
            $conversation = ChatConversation::create([
                'title'=>$data['title'] ?? null,
                'is_group'=>$isGroup,
                'created_by'=>$user->id,
            ]);
            $conversation->participants()->create([
                'user_id'=>$user->id,
                'invited_at'=>now(),
                'accepted_at'=>now(),
                'is_admin'=>true
            ]);
            foreach ($participantIds as $pid) {
                if ($pid == $user->id) continue;
                $conversation->participants()->create([
                    'user_id'=>$pid,
                    'invited_at'=>now(),
                ]);
            }
        });
        return response()->json(['conversation_id'=>$conversation->id,'message'=>'Conversa criada']);
    }

    public function invite(Request $request, ChatConversation $conversation)
    {
        if (!$this->chatTablesReady()) { abort(503,'Chat não migrado'); }
        $this->ensureParticipant($conversation,$request->user()->id);
        $request->validate(['users'=>['required','array','max:50'],'users.*'=>['integer']]);
        $now = now();
        foreach (array_unique($request->users) as $uid) {
            if ($conversation->participants()->where('user_id',$uid)->exists()) continue;
            $conversation->participants()->create(['user_id'=>$uid,'invited_at'=>$now]);
            $this->systemMessage($conversation, $request->user()->id, 'convite enviado para usuário #'.$uid);
        }
        $conversation->touch();
        return response()->json(['message'=>'Convites enviados']);
    }

    public function accept(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { abort(503,'Chat não migrado'); }
        $p = $conversation->participants()->where('user_id',$request->user()->id)->firstOrFail();
        if ($p->declined_at) return response()->json(['message'=>'Convite recusado anteriormente'],422);
        if ($p->accepted_at) return response()->json(['message'=>'Já aceito']);
        $p->accepted_at = now();
        $p->save();
        $this->systemMessage($conversation,$request->user()->id,'participação aceita');
        return response()->json(['message'=>'Participação aceita']);
    }

    public function decline(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { abort(503,'Chat não migrado'); }
        $p = $conversation->participants()->where('user_id',$request->user()->id)->firstOrFail();
        if ($p->accepted_at) return response()->json(['message'=>'Já aceito, não pode recusar'],422);
        if ($p->declined_at) return response()->json(['message'=>'Já recusado']);
        $p->declined_at = now();
        $p->save();
        $this->systemMessage($conversation,$request->user()->id,'participação recusada');
        return response()->json(['message'=>'Convite recusado']);
    }

    public function leave(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { abort(503,'Chat não migrado'); }
        $p = $this->ensureParticipant($conversation,$request->user()->id);
        if ($p->left_at) return response()->json(['message'=>'Já saiu']);
        $p->left_at = now();
        $p->save();
        $this->systemMessage($conversation,$request->user()->id,'saiu da conversa');
        return response()->json(['message'=>'Você saiu da conversa']);
    }

    public function messages(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['data'=>[]]); }
        $this->ensureParticipant($conversation,$request->user()->id);
        $after = $request->get('after_id');
        $query = $conversation->messages()->with('user:id,name')->orderBy('id');
        if ($after) { $query->where('id','>',(int)$after); }
        $messages = $query->limit(200)->get();
        // marca como lidas
        $unread = $messages->where('user_id','!=',$request->user()->id)->pluck('id');
        $now = now();
        foreach ($unread as $mid) {
            ChatMessageRead::firstOrCreate(['message_id'=>$mid,'user_id'=>$request->user()->id],[ 'read_at'=>$now ]);
        }
        return response()->json(['data'=>$messages->map(fn($m)=>[
            'id'=>$m->id,
            'type'=>$m->type,
            'is_system'=>$m->is_system,
            'user'=>['id'=>$m->user_id,'name'=>$m->user?->name],
            'body'=>$m->body,
            'attachment_meta'=>$m->attachment_meta,
            'created_at'=>$m->created_at?->toIso8601String()
        ])]);
    }

    public function send(ChatSendMessageRequest $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'],503); }
        $data = $request->validated();
        $conversation = ChatConversation::findOrFail($data['conversation_id']);
        $this->ensureParticipant($conversation,$request->user()->id);
        $attachmentMeta = null;
        if ($data['type'] !== 'text' && $request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat','public');
            $attachmentMeta = [
                'original_name'=>$file->getClientOriginalName(),
                'size'=>$file->getSize(),
                'mime'=>$file->getMimeType(),
                'path'=>$path
            ];
        }
        $msg = $conversation->messages()->create([
            'user_id'=>$request->user()->id,
            'type'=>$data['type'],
            'body'=>$data['type']==='text' ? $data['body'] : null,
            'attachment_meta'=>$attachmentMeta,
        ]);
        $conversation->touch();
        return response()->json(['message'=>'Enviado','id'=>$msg->id]);
    }

    public function read(ChatMessage $message, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'],503); }
        $conversation = $message->conversation;
        $this->ensureParticipant($conversation,$request->user()->id);
        ChatMessageRead::firstOrCreate(['message_id'=>$message->id,'user_id'=>$request->user()->id],[ 'read_at'=>now() ]);
        return response()->json(['message'=>'OK']);
    }

    private function systemMessage(ChatConversation $conversation,int $actorId,string $text): void
    {
        $conversation->messages()->create([
            'user_id'=>$actorId,
            'type'=>'text',
            'is_system'=>true,
            'body'=>$text
        ]);
        $conversation->touch();
    }
}

