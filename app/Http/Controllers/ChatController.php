<?php
namespace App\Http\Controllers;

use App\Models\{ChatConversation, ChatConversationParticipant, ChatMessage, ChatMessageRead, User, ChatMessageTemplate};
use App\Http\Requests\{CreateChatConversationRequest, ChatSendMessageRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Cache;
use App\Events\{ChatMessageCreated, ChatParticipantsUpdated, ChatTyping};
use App\Events\ChatUnreadPing; // novo evento

class ChatController extends Controller
{
    private function chatTablesReady(): bool
    {
        return Schema::hasTable('chat_conversations') && Schema::hasTable('chat_conversation_participants') && Schema::hasTable('chat_messages');
    }

    private function ensureParticipant(ChatConversation $conversation, int $userId): ChatConversationParticipant
    {
        $p = $conversation->participants()->where('user_id',$userId)->first();
        if (!$p || $p->left_at) { abort(403,'Você não participa desta conversa.'); }
        return $p;
    }

    public function index(Request $request)
    {
        if (!$this->chatTablesReady()) { return view('chat.missing'); }
        $userId = $request->user()->id;
        $roleId = (int)$request->user()->role_id;
        $conversations = ChatConversation::with(['participants.user:id,name,role_id','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->forUser($userId)
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();
        return view('chat.index', [ 'conversations'=>$conversations, 'roleId'=>$roleId ]);
    }

    public function list(Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['data'=>[], 'warning'=>'Chat tables not migrated'], 200); }
        $userId = $request->user()->id;
        $search = trim($request->get('q',''));
        $query = ChatConversation::with(['participants.user:id,name,role_id,secretariat_id','messages'=>fn($q)=>$q->latest()->limit(1)])
            ->forUser($userId);
        if ($search !== '') {
            $like = "%$search%";
            $query->where(function($q) use ($like,$userId){
                $q->where('title','like',$like)
                  ->orWhereHas('participants.user', function($p) use ($like,$userId){
                      $p->where('name','like',$like)->where('user_id','!=',$userId);
                  });
            });
        }
        $conversations = $query->orderByDesc('updated_at')->limit(100)->get();
        $data = $conversations->map(function($c) use ($userId){
            $unread = ChatMessage::where('conversation_id',$c->id)
                ->where('user_id','!=',$userId)
                ->whereDoesntHave('reads', fn($r)=>$r->where('user_id',$userId))
                ->count();
            return [
                'id'=>$c->id,
                'title'=>$c->title ?? $this->deriveTitle($c,$userId),
                'is_group'=>$c->is_group,
                'last_message'=>$c->messages->first()?->body,
                'updated_at'=>$c->updated_at?->toIso8601String(),
                'unread'=>$unread,
                'participants'=>$c->participants->map(fn($p)=>[
                    'id'=>$p->user_id,
                    'name'=>$p->user->name,
                    'role'=>$p->user->role?->name,
                ])->values(),
            ];
        });
        return response()->json(['data'=>$data]);
    }

    private function deriveTitle(ChatConversation $c, int $viewerId): string
    {
        if ($c->title) return $c->title;
        if (!$c->is_group) {
            $other = $c->participants->firstWhere('user_id','!=',$viewerId);
            if ($other && $other->user) {
                $role = $other->user->role?->name;
                return $role ? ($other->user->name.' ('.$role.')') : $other->user->name;
            }
            return 'Conversa';
        }
        return 'Grupo #'.$c->id;
    }

    public function store(CreateChatConversationRequest $request)
    {
        // ...existing code before transaction...
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'], 503); }
        $data = $request->validated();
        $user = $request->user();
        $participantIds = collect($data['participants'] ?? [])->unique()->take(50)->values();
        $isGroup = ($data['is_group'] ?? ($participantIds->count() > 1));
        if ($isGroup && !in_array((int)$user->role_id,[1,2])) {
            return response()->json(['message'=>'Apenas admins podem criar grupos'],403);
        }
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
                'accepted_at'=>now(), // auto-aceito
                'is_admin'=>true
            ]);
            foreach ($participantIds as $pid) {
                if ($pid == $user->id) continue;
                $conversation->participants()->create([
                    'user_id'=>$pid,
                    'invited_at'=>now(),
                    'accepted_at'=>now(), // auto-aceito
                ]);
            }
        });
        return response()->json(['conversation_id'=>$conversation->id,'message'=>'Conversa criada']);
    }

    public function invite(Request $request, ChatConversation $conversation)
    {
        // ...existing code start...
        if (!$this->chatTablesReady()) { abort(503,'Chat não migrado'); }
        $this->ensureParticipant($conversation,$request->user()->id);
        $request->validate(['users'=>['required','array','max:50'],'users.*'=>['integer']]);
        $now = now();
        foreach (array_unique($request->users) as $uid) {
            if ($conversation->participants()->where('user_id',$uid)->exists()) continue;
            $conversation->participants()->create(['user_id'=>$uid,'invited_at'=>$now,'accepted_at'=>$now]); // auto accept
            $this->systemMessage($conversation, $request->user()->id, 'participante adicionado (#'.$uid.')');
        }
        $conversation->touch();
        if (config('broadcasting.default') !== 'log') { broadcast(new ChatParticipantsUpdated($conversation->id)); }
        return response()->json(['message'=>'Participantes adicionados']);
    }

    // Removidos métodos accept e decline (mantidos para compat se chamados, retornam 410)
    public function accept(ChatConversation $conversation, Request $request){ return response()->json(['message'=>'Endpoint removido'],410); }
    public function decline(ChatConversation $conversation, Request $request){ return response()->json(['message'=>'Endpoint removido'],410); }

    public function leave(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { abort(503,'Chat não migrado'); }
        $p = $this->ensureParticipant($conversation,$request->user()->id);
        if ($p->left_at) return response()->json(['message'=>'Já saiu']);
        $p->left_at = now();
        $p->save();
        $this->systemMessage($conversation,$request->user()->id,'saiu da conversa');
        if (config('broadcasting.default') !== 'log') { broadcast(new ChatParticipantsUpdated($conversation->id)); }
        return response()->json(['message'=>'Você saiu da conversa']);
    }

    public function messages(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['data'=>[]]); }
        $this->ensureParticipant($conversation,$request->user()->id);
        $after = $request->get('after_id');
        $query = $conversation->messages()->with(['user:id,name,role_id'])->orderBy('id');
        if ($after) { $query->where('id','>',(int)$after); }
        $messages = $query->limit(200)->get();
        $unread = $messages->where('user_id','!=',$request->user()->id)->pluck('id');
        $now = now();
        foreach ($unread as $mid) {
            ChatMessageRead::firstOrCreate(['message_id'=>$mid,'user_id'=>$request->user()->id],[ 'read_at'=>$now ]);
        }
        return response()->json([
            'data'=>$messages->map(fn($m)=>[
                'id'=>$m->id,
                'type'=>$m->type,
                'is_system'=>$m->is_system,
                'user'=>['id'=>$m->user_id,'name'=>$m->user?->name,'role'=>$m->user?->role?->name],
                'body'=>$m->body,
                'attachment_meta'=>$m->attachment_meta,
                'style_class'=>$m->style_class,
                'template_id'=>$m->template_id,
                'created_at'=>$m->created_at?->toIso8601String()
            ]),
            'participants'=>$conversation->participants()->with('user:id,name,role_id')->get()->map(fn($p)=>[
                'id'=>$p->user_id,'name'=>$p->user->name,'role'=>$p->user->role?->name
            ])
        ]);
    }

    public function typing(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'],503); }
        $this->ensureParticipant($conversation,$request->user()->id);
        $typingKey = "chat:typing:{$conversation->id}";
        $set = Cache::get($typingKey, []);
        $set[$request->user()->id] = microtime(true);
        Cache::put($typingKey, $set, 10); // expira em 10s
        if (config('broadcasting.default') !== 'log') {
            broadcast(new ChatTyping($conversation->id, $request->user()->id))->toOthers();
        }
        return response()->json(['message'=>'OK']);
    }

    public function send(ChatSendMessageRequest $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'],503); }
        $data = $request->validated();
        $conversation = ChatConversation::findOrFail($data['conversation_id']);
        $this->ensureParticipant($conversation,$request->user()->id);
        $user = $request->user();
        $template = null; $styleClass=null; $body = $data['body'] ?? null;
        if (!empty($data['template_id'])) {
            $template = ChatMessageTemplate::findOrFail($data['template_id']);
            if ($template->scope==='secretariat' && $template->secretariat_id !== $user->secretariat_id) {
                return response()->json(['message'=>'Template não autorizado'],403);
            }
            if ((int)$user->role_id===2) {
                $participantUserIds = $conversation->participants()->pluck('user_id');
                $others = User::whereIn('id',$participantUserIds)->where(function($q) use ($user){ $q->whereNull('secretariat_id')->orWhere('secretariat_id','!=',$user->secretariat_id); })->exists();
                if ($others) { return response()->json(['message'=>'Template só pode ser enviado para usuários da sua secretaria'],403); }
            }
            $body = $body && trim($body)!=='' ? $body : $template->body;
            $styleClass = $template->style['class'] ?? null;
        }
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
            'user_id'=>$user->id,
            'type'=>$data['type'],
            'body'=>$data['type']==='text' ? $body : null,
            'attachment_meta'=>$attachmentMeta,
            'template_id'=>$template?->id,
            'style_class'=>$styleClass,
        ]);
        $conversation->touch();
        if (config('broadcasting.default') !== 'log') {
            broadcast(new ChatMessageCreated($conversation->id, $msg->id))->toOthers();
            // broadcast ping de unread para cada participante diferente do autor
            $recipientIds = $conversation->participants()->whereNull('left_at')->pluck('user_id')->filter(fn($id)=>$id!=$user->id);
            foreach ($recipientIds as $rid) { broadcast(new ChatUnreadPing($rid, $conversation->id, $msg->id)); }
        }
        return response()->json(['message'=>'Enviado','id'=>$msg->id]);
    }

    public function unreadSummary(Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['total'=>0]); }
        $userId = $request->user()->id;
        $conversations = ChatConversation::forUser($userId)->pluck('id');
        if (!$conversations->count()) return response()->json(['total'=>0]);
        $total = ChatMessage::whereIn('conversation_id',$conversations)
            ->where('user_id','!=',$userId)
            ->whereDoesntHave('reads', fn($r)=>$r->where('user_id',$userId))
            ->count();
        return response()->json(['total'=>$total]);
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

    public function templates(Request $request)
    {
        if (!$this->chatTablesReady() || !Schema::hasTable('chat_message_templates')) { return response()->json(['data'=>[]]); }
        $user = $request->user();
        $query = ChatMessageTemplate::query();
        $query->where(function($q) use ($user){
            // Templates globais acessíveis por todos
            $q->where('scope','global');

            // Templates da secretaria do usuário
            if ($user->secretariat_id) {
                $q->orWhere(function($q2) use ($user){
                    $q2->where('scope','secretariat')->where('secretariat_id',$user->secretariat_id);
                });
            }

            // Templates pessoais do próprio usuário
            $q->orWhere(function($q2) use ($user){
                $q2->where('scope','personal')->where('created_by',$user->id);
            });
        });
        $templates = $query->orderBy('scope')->orderBy('title')->get();
        return response()->json(['data'=>$templates->map(fn($t)=>[
            'id'=>$t->id,
            'title'=>$t->title,
            'body'=>$t->body,
            'scope'=>$t->scope,
            'style'=>$t->style,
            'secretariat_id'=>$t->secretariat_id,
        ])]);
    }

    public function templateStore(Request $request)
    {
        $user = $request->user();
        if (!in_array((int)$user->role_id,[1,2])) { return response()->json(['message'=>'Sem permissão'],403); }
        $data = $request->validate([
            'title'=>['required','string','max:120'],
            'body'=>['required','string','max:5000'],
            'scope'=>['required','in:global,secretariat,personal'],
            'style'=>['nullable','array'],
            'style.class'=>['nullable','string','max:80'],
        ]);

        // Verificar permissões para criação de templates
        if ((int)$user->role_id===2 && $data['scope']==='global') {
            return response()->json(['message'=>'Role 2 não cria template global'],422);
        }

        $secretariatId = null;
        if ($data['scope']==='secretariat') {
            $secretariatId = $user->secretariat_id;
            if (!$secretariatId) {
                return response()->json(['message'=>'Usuário sem secretaria não pode criar template de secretaria'],422);
            }
        }

        $tpl = ChatMessageTemplate::create([
            'title'=>$data['title'],
            'body'=>$data['body'],
            'scope'=>$data['scope'],
            'style'=>$data['style'] ?? null,
            'secretariat_id'=>$secretariatId,
            'created_by'=>$user->id,
        ]);
        return response()->json(['message'=>'Template criado','id'=>$tpl->id]);
    }

    public function templateUpdate(ChatMessageTemplate $template, Request $request)
    {
        $user = $request->user();
        if (!in_array((int)$user->role_id,[1,2])) { return response()->json(['message'=>'Sem permissão'],403); }
        if ((int)$user->role_id===2 && ($template->scope!=='secretariat' || $template->secretariat_id !== $user->secretariat_id)) { return response()->json(['message'=>'Template fora do escopo'],403); }
        $data = $request->validate([
            'title'=>['sometimes','string','max:120'],
            'body'=>['sometimes','string','max:5000'],
            'style'=>['sometimes','array'],
            'style.class'=>['nullable','string','max:80'],
        ]);
        $template->fill($data);
        $template->save();
        return response()->json(['message'=>'Template atualizado']);
    }

    public function templateDelete(ChatMessageTemplate $template, Request $request)
    {
        $user = $request->user();
        if (!in_array((int)$user->role_id,[1,2])) { return response()->json(['message'=>'Sem permissão'],403); }
        if ((int)$user->role_id===2 && ($template->scope!=='secretariat' || $template->secretariat_id !== $user->secretariat_id)) { return response()->json(['message'=>'Template fora do escopo'],403); }
        $template->delete();
        return response()->json(['message'=>'Template removido']);
    }

    public function updates(ChatConversation $conversation, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['messages'=>[], 'participants'=>[], 'typing'=>[]]); }
        $this->ensureParticipant($conversation,$request->user()->id);
        $afterId = (int)$request->get('after_id',0);
        $beforeId = (int)$request->get('before_id',0);
        $userId = $request->user()->id;
        $limit = 60;
        $messagesQuery = $conversation->messages()->with('user:id,name,role_id');
        if ($beforeId>0) {
            $messagesQuery->where('id','<',$beforeId)->orderByDesc('id');
        } elseif ($afterId>0) {
            $messagesQuery->where('id','>',$afterId)->orderBy('id');
        } else { // inicial: pegar últimas
            $messagesQuery->orderByDesc('id');
        }
        $messages = $messagesQuery->limit($limit)->get();
        if ($beforeId>0 || ($afterId===0 && $beforeId===0)) { $messages = $messages->sortBy('id')->values(); }
        $now = now();
        foreach ($messages->where('user_id','!=',$userId) as $m) {
            ChatMessageRead::firstOrCreate(['message_id'=>$m->id,'user_id'=>$userId],[ 'read_at'=>$now ]);
        }
        $participants = $conversation->participants()->with('user:id,name,role_id')->active()->get()->map(fn($p)=>[
            'id'=>$p->user_id,'name'=>$p->user->name,'role'=>$p->user->role?->name
        ]);
        $unread = ChatMessage::where('conversation_id',$conversation->id)
            ->where('user_id','!=',$userId)
            ->whereDoesntHave('reads', fn($r)=>$r->where('user_id',$userId))
            ->count();
        $typingKey = "chat:typing:{$conversation->id}";
        $typingUsers = collect(Cache::get($typingKey, []))->filter(fn($ts,$uid)=>$ts > microtime(true)-5)->keys()->map(fn($id)=>(int)$id)->values();
        $oldestIdOverall = ChatMessage::where('conversation_id',$conversation->id)->min('id');
        $currentOldest = $messages->first()?->id ?? 0;
        return response()->json([
            'messages'=>$messages->map(fn($m)=>[
                'id'=>$m->id,
                'type'=>$m->type,
                'is_system'=>$m->is_system,
                'user'=>['id'=>$m->user_id,'name'=>$m->user?->name,'role'=>$m->user?->role?->name],
                'body'=>$m->body,
                'attachment_meta'=>$m->attachment_meta,
                'style_class'=>$m->style_class,
                'template_id'=>$m->template_id,
                'created_at'=>$m->created_at?->toIso8601String()
            ]),
            'participants'=>$participants,
            'unread'=>$unread,
            'typing'=>$typingUsers,
            'has_more_older'=>$oldestIdOverall && $oldestIdOverall < $currentOldest
        ]);
    }

    public function read(ChatMessage $message, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'],503); }
        $conversation = $message->conversation;
        $this->ensureParticipant($conversation,$request->user()->id);
        ChatMessageRead::firstOrCreate(['message_id'=>$message->id,'user_id'=>$request->user()->id],[ 'read_at'=>now() ]);
        return response()->json(['message'=>'OK']);
    }

    public function searchUsers(Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['data'=>[]]); }
        $q = trim($request->get('q',''));
        $current = $request->user()->id;
        $isAdmin = in_array((int)$request->user()->role_id,[1,2]);
        $query = User::query()->with('role:id,name')->where('id','!=',$current);
        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->where('name','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
                  ->orWhere('cpf','like',"%$q%" );
            });
        } elseif (!$isAdmin) {
            return response()->json(['data'=>[]]);
        }
        $users = $query->orderBy('name')->limit( ($isAdmin && $q==='') ? 500 : 30)->get(['id','name','role_id']);
        return response()->json(['data'=>$users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name,'role'=>$u->role?->name])]);
    }

    public function allUsers(Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['data'=>[]]); }
        $auth = $request->user();
        if (!in_array((int)$auth->role_id,[1])) { return response()->json(['data'=>[]],403); }
        $users = User::with('role:id,name')->where('id','!=',$auth->id)->orderBy('name')->get(['id','name','role_id']);
        $directConvs = ChatConversation::where('is_group',false)
            ->whereHas('participants', fn($q)=>$q->where('user_id',$auth->id))
            ->with(['participants'=>fn($q)=>$q->where('user_id','!=',$auth->id)])
            ->get();
        $map = [];
        foreach ($directConvs as $conv) { $other = $conv->participants->first(); if ($other) { $map[$other->user_id] = $conv->id; } }
        $data = $users->map(function($u) use ($auth,$map){
            $conversationId = $map[$u->id] ?? null; $unread=0;
            if ($conversationId) {
                $unread = ChatMessage::where('conversation_id',$conversationId)
                    ->where('user_id','!=',$auth->id)
                    ->whereDoesntHave('reads', fn($r)=>$r->where('user_id',$auth->id))
                    ->count();
            }
            return [ 'id'=>$u->id, 'name'=>$u->name, 'role'=>$u->role?->name, 'conversation_id'=>$conversationId, 'unread'=>$unread ];
        });
        return response()->json(['data'=>$data]);
    }

    public function direct(User $user, Request $request)
    {
        if (!$this->chatTablesReady()) { return response()->json(['message'=>'Chat não migrado'],503); }
        $auth = $request->user();
        if ($auth->id === $user->id) { return response()->json(['message'=>'Inválido'],422); }
        $conversation = ChatConversation::where('is_group',false)
            ->whereHas('participants', fn($q)=>$q->where('user_id',$auth->id))
            ->whereHas('participants', fn($q)=>$q->where('user_id',$user->id))
            ->first();
        if (!$conversation) {
            DB::transaction(function() use (&$conversation,$auth,$user){
                $conversation = ChatConversation::create(['is_group'=>false,'created_by'=>$auth->id]);
                $conversation->participants()->create(['user_id'=>$auth->id,'invited_at'=>now(),'accepted_at'=>now(),'is_admin'=>true]);
                $conversation->participants()->create(['user_id'=>$user->id,'invited_at'=>now(),'accepted_at'=>now()]);
            });
        }
        return response()->json(['conversation_id'=>$conversation->id]);
    }
}
