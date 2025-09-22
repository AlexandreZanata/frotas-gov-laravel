@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-comments text-blue-600"></i> Chat</h2>
@endsection
@section('content')
<div class="max-w-7xl mx-auto h-[calc(100vh-9rem)] px-4 pb-4" x-data="chatApp()" x-init="init()">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 h-full">
        <!-- Sidebar Conversas -->
        <div class="md:col-span-4 lg:col-span-3 flex flex-col h-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <input x-model="filters.search" @input="debouncedReload()" placeholder="Buscar..." class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
                <button @click="openNewConversation()" class="px-2 py-1.5 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-semibold"><i class="fas fa-plus"></i></button>
            </div>
            <div class="overflow-y-auto flex-1 custom-scroll" x-ref="conversationList">
                <template x-for="c in conversations" :key="c.id">
                    <button @click="selectConversation(c)" class="w-full text-left px-3 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 flex flex-col gap-1" :class="current && current.id===c.id ? 'bg-blue-50 dark:bg-blue-900/30' : ''">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="c.title"></span>
                            <span class="text-[10px] text-gray-500" x-text="formatDate(c.updated_at)"></span>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1" x-text="c.last_message || '—' "></div>
                        <div class="flex flex-wrap gap-1 mt-1" x-show="c.is_group">
                            <template x-for="p in c.participants" :key="p.id">
                                <span class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[10px]" :class="p.accepted ? 'opacity-100':'opacity-50'" x-text="p.name"></span>
                            </template>
                        </div>
                    </button>
                </template>
                <div x-show="!loading && conversations.length===0" class="p-4 text-xs text-gray-500 dark:text-gray-400">Nenhuma conversa.</div>
                <div x-show="loading" class="p-4 text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>
            </div>
        </div>

        <!-- Área principal -->
        <div class="md:col-span-8 lg:col-span-9 flex flex-col h-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm">
            <!-- Cabeçalho da conversa -->
            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between" x-show="current" x-cloak>
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="current?.title"></h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400" x-text="participantsSummary()"></p>
                </div>
                <div class="flex items-center gap-2" x-show="!isCurrentAccepted() && !isCurrentDeclined()">
                    <button @click="acceptCurrent()" class="px-3 py-1.5 rounded bg-green-600 hover:bg-green-700 text-white text-[11px] font-semibold">Aceitar</button>
                    <button @click="declineCurrent()" class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-[11px] font-semibold">Recusar</button>
                </div>
                <div x-show="isCurrentAccepted()" class="text-[11px] px-2 py-1 rounded bg-green-600 text-white" x-cloak>Aceito</div>
                <div x-show="isCurrentDeclined()" class="text-[11px] px-2 py-1 rounded bg-red-600 text-white" x-cloak>Recusado</div>
            </div>
            <div class="p-4 flex-1 overflow-y-auto custom-scroll space-y-4 text-sm" x-ref="messagesPanel" x-show="current" x-cloak>
                <template x-for="m in messages" :key="m.id">
                    <div class="flex flex-col max-w-[80%]" :class="m.user.id===userId ? 'ml-auto items-end' : 'items-start'">
                        <div class="text-[10px] mb-0.5 text-gray-500 dark:text-gray-400" x-text="m.is_system ? 'Sistema' : (m.user.id===userId ? 'Você' : m.user.name)"></div>
                        <template x-if="m.type==='text'">
                            <div class="px-3 py-2 rounded shadow text-xs whitespace-pre-wrap" :class="m.is_system ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200':'bg-blue-600 text-white dark:bg-blue-700'" x-text="m.body"></div>
                        </template>
                        <template x-if="m.type!=='text'">
                            <div class="px-3 py-2 rounded shadow bg-gray-200 dark:bg-gray-700 text-xs flex flex-col gap-1">
                                <span class="font-semibold" x-text="m.type.toUpperCase()"></span>
                                <template x-if="m.attachment_meta && (m.type==='image')">
                                    <img :src="storageUrl(m.attachment_meta.path)" class="max-h-48 rounded" />
                                </template>
                                <template x-if="m.type==='audio'">
                                    <audio :src="storageUrl(m.attachment_meta.path)" controls class="w-56"></audio>
                                </template>
                                <template x-if="m.type==='file'">
                                    <a :href="storageUrl(m.attachment_meta.path)" target="_blank" class="text-blue-600 underline">Baixar arquivo</a>
                                </template>
                            </div>
                        </template>
                        <div class="text-[9px] mt-1 text-gray-400" x-text="formatDate(m.created_at)"></div>
                    </div>
                </template>
                <div x-show="messagesLoading" class="text-center text-xs text-gray-500"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
            <div x-show="!current" class="flex-1 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">Selecione ou crie uma conversa.</div>
            <!-- Composer -->
            <form x-show="current && isCurrentAccepted()" x-cloak @submit.prevent="sendMessage" class="p-3 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-2" enctype="multipart/form-data">
                <div class="flex gap-2 items-end">
                    <textarea x-model="composer.body" placeholder="Mensagem" class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm resize-none h-14" maxlength="5000"></textarea>
                    <div class="flex flex-col gap-2 w-32 text-[11px]">
                        <select x-model="composer.type" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs">
                            <option value="text">Texto</option>
                            <option value="image">Imagem</option>
                            <option value="audio">Áudio</option>
                            <option value="file">Arquivo</option>
                        </select>
                        <input x-show="composer.type!=='text'" x-ref="fileInput" type="file" class="text-[10px]" @change="handleFile" />
                        <div x-show="attachmentName" class="text-[10px] truncate" x-text="attachmentName"></div>
                    </div>
                    <button :disabled="sending || (!composer.body && composer.type==='text')" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold flex items-center gap-2">
                        <i class="fas" :class="sending ? 'fa-spinner fa-spin':'fa-paper-plane'"></i> Enviar
                    </button>
                </div>
                <p class="text-[10px] text-gray-400">Limite: 10MB. Formatos aceitos de acordo com o tipo.</p>
            </form>
        </div>
    </div>

    <!-- Modal Nova Conversa -->
    <div x-show="modal.newConversation" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded shadow-lg w-full max-w-md p-6 space-y-5 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-plus-circle text-green-600"></i> Nova Conversa</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400">Título (opcional)</label>
                    <input x-model="newConv.title" class="mt-1 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400">IDs Participantes (separados por vírgula)</label>
                    <input x-model="newConv.idsRaw" placeholder="Ex: 5,8,12" class="mt-1 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
                    <p class="text-[10px] text-gray-400 mt-1">Você será incluído automaticamente.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="closeModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Cancelar</button>
                <button @click="createConversation()" :disabled="creating" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold flex items-center gap-2"><i class="fas" :class="creating ? 'fa-spinner fa-spin':'fa-check'"></i> Criar</button>
            </div>
        </div>
    </div>
</div>
<script>
function chatApp(){
    return {
        userId: {{ auth()->id() }},
        conversations: [],
        current: null,
        messages: [],
        lastMessageId: null,
        loading:false,
        messagesLoading:false,
        sending:false,
        polling:null,
        filters:{search:''},
        composer:{type:'text',body:''},
        attachmentFile:null,
        attachmentName:null,
        modal:{newConversation:false},
        newConv:{title:'',idsRaw:''},
        creating:false,
        init(){ this.reloadConversations(); this.startPolling(); },
        startPolling(){ this.polling = setInterval(()=>{ if(this.current){ this.fetchMessages(true); } else { this.reloadConversationsQuiet(); } },5000); },
        debouncedReload(){ clearTimeout(this._dr); this._dr=setTimeout(()=>this.reloadConversations(),400); },
        storageUrl(p){ return p ? ('/storage/'+p) : '#'; },
        formatDate(iso){ if(!iso) return ''; const d=new Date(iso); return d.toLocaleString(); },
        reloadConversationsQuiet(){ if(this.loading) return; fetch('{{ route('api.chat.conversations') }}').then(r=>r.json()).then(j=>{ this.mergeConversationUpdates(j.data||[]); }); },
        reloadConversations(){ this.loading=true; fetch('{{ route('api.chat.conversations') }}').then(r=>r.json()).then(j=>{ this.conversations=j.data||[]; if(this.current){ const upd=this.conversations.find(c=>c.id===this.current.id); if(upd) this.current=upd; } }).finally(()=>this.loading=false); },
        mergeConversationUpdates(list){ list.forEach(n=>{ const i=this.conversations.findIndex(c=>c.id===n.id); if(i>=0){ this.conversations[i]=n; } else { this.conversations.unshift(n); } }); },
        selectConversation(c){ if(this.current && this.current.id===c.id) return; this.current=c; this.messages=[]; this.lastMessageId=null; this.fetchMessages(); },
        fetchMessages(silent=false){ if(!this.current) return; if(!silent) this.messagesLoading=true; let url=`/api/chat/conversations/${this.current.id}/messages`; if(this.lastMessageId) url+=`?after_id=${this.lastMessageId}`; fetch(url).then(r=>r.json()).then(j=>{ const data=j.data||[]; if(data.length){ data.forEach(m=>{ if(!this.messages.find(x=>x.id===m.id)) this.messages.push(m); }); this.lastMessageId=this.messages[this.messages.length-1].id; this.$nextTick(()=>{ this.scrollMessagesBottom(); }); } }).finally(()=>{ if(!silent) this.messagesLoading=false; }); },
        scrollMessagesBottom(){ const el=this.$refs.messagesPanel; if(el) el.scrollTop=el.scrollHeight; },
        handleFile(e){ const f=e.target.files[0]; if(!f) { this.attachmentFile=null; this.attachmentName=null; return; } if(f.size>10*1024*1024){ alert('Arquivo excede 10MB'); e.target.value=''; return; } this.attachmentFile=f; this.attachmentName=f.name; },
        sendMessage(){ if(this.sending) return; if(this.composer.type==='text' && !this.composer.body.trim()) return; const fd=new FormData(); fd.append('conversation_id',this.current.id); fd.append('type',this.composer.type); if(this.composer.type==='text'){ fd.append('body',this.composer.body); } else if(this.attachmentFile){ fd.append('attachment',this.attachmentFile); } this.sending=true; fetch('{{ route('api.chat.messages.send') }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:fd}).then(r=>r.json()).then(j=>{ if(j.id){ this.composer.body=''; this.composer.type='text'; this.attachmentFile=null; this.attachmentName=null; if(this.$refs.fileInput) this.$refs.fileInput.value=''; this.fetchMessages(true); this.reloadConversationsQuiet(); } }).finally(()=>this.sending=false); },
        isCurrentAccepted(){ if(!this.current) return false; const me=this.current.participants.find(p=>p.id===this.userId); return me && me.accepted; },
        isCurrentDeclined(){ if(!this.current) return false; const me=this.current.participants.find(p=>p.id===this.userId); return me && !me.accepted; },
        acceptCurrent(){ fetch(`/api/chat/conversations/${this.current.id}/accept`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(()=>{ this.reloadConversations(); }); },
        declineCurrent(){ fetch(`/api/chat/conversations/${this.current.id}/decline`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(()=>{ this.reloadConversations(); this.current=null; }); },
        participantsSummary(){ if(!this.current) return ''; const names=this.current.participants.map(p=>p.name); return names.join(', '); },
        openNewConversation(){ this.modal.newConversation=true; },
        closeModal(){ this.modal.newConversation=false; this.newConv={title:'',idsRaw:''}; },
        createConversation(){ if(this.creating) return; const ids=this.newConv.idsRaw.split(',').map(s=>parseInt(s.trim())).filter(n=>!isNaN(n)); this.creating=true; fetch('{{ route('api.chat.conversations.store') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({title:this.newConv.title||null,participants:ids})}).then(r=>r.json()).then(j=>{ if(j.conversation_id){ this.closeModal(); this.reloadConversations(); } }).finally(()=>this.creating=false); }
    }
}
</script>
@endsection
