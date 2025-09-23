@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-comments text-blue-600"></i> Chat</h2>
@endsection
@section('content')
<div class="max-w-7xl mx-auto h-[calc(100vh-9rem)] px-2 sm:px-4 pb-4" x-data="chatApp()" x-init="init()">
    <style>
        .custom-scroll { scrollbar-width: thin; }
        .msg-new { animation: pulse-fade 0.7s cubic-bezier(0.4, 0, 0.6, 1) 1; }
        @keyframes pulse-fade {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.9; transform: scale(1.02); background-color: rgba(34,197,94,.2); }
        }
        @media (max-width: 767px) { .mobile-hidden { display: none !important; } }
        .chat-img-wrapper{position:relative;max-width:100%;}
        .chat-img-wrapper img{display:block;max-height:18rem;width:auto;height:auto;}
    </style>
    <div class="grid grid-cols-12 gap-3 h-full">
        <!-- LISTA -->
        <div class="col-span-12 md:col-span-4 lg:col-span-3 flex flex-col min-h-0 h-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm z-10"
             :class="mobile.view==='list' ? '' : 'mobile-hidden md:flex'">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2 z-20 relative">
                <button class="md:hidden px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200" x-show="mobile.view==='conversation'" @click="showList()"><i class="fas fa-arrow-left"></i></button>
                <input x-model="filters.search" @input="debouncedReload()" placeholder="Buscar conversa..." class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
                <button @click="openNewConversation()" class="px-2 py-1.5 rounded bg-green-600 hover:bg-green-700 text-white text-xs font-semibold" aria-label="Nova conversa"><i class="fas fa-plus"></i></button>
                <button @click="openContactsModal()" class="px-2 py-1.5 rounded bg-teal-600 hover:bg-teal-700 text-white text-xs" title="Contatos"><i class="fas fa-user"></i></button>
            </div>
            <div class="px-3 pt-2 flex items-center justify-between text-[11px] gap-2" x-show="canSeeAllContacts">
                <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded overflow-hidden">
                    <button @click="ui.tab='conversations'" :class="ui.tab==='conversations'?'bg-blue-600 text-white':'text-gray-600 dark:text-gray-300'" class="px-3 py-1 font-medium">Conversas</button>
                    <button @click="loadContacts()" :class="ui.tab==='contacts'?'bg-blue-600 text-white':'text-gray-600 dark:text-gray-300'" class="px-3 py-1 font-medium">Contatos</button>
                </div>
            </div>
            <div class="overflow-y-auto flex-1 custom-scroll" x-ref="conversationList">
                <!-- CONVERSAS -->
                <template x-if="ui.tab==='conversations'">
                    <div>
                        <template x-for="c in conversations" :key="c.id">
                            <button @click="selectConversation(c)" class="relative w-full text-left px-3 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 flex flex-col gap-1"
                                    :class="current && current.id===c.id ? 'bg-blue-50 dark:bg-blue-900/30' : ''">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="c.title"></span>
                                    <div class="flex items-center gap-1">
                                        <i x-show="isMuted(c.id)" class="fas fa-bell-slash text-gray-400 text-[11px]"></i>
                                        <span class="text-[10px] text-gray-500" x-text="formatTime(c.updated_at)"></span>
                                    </div>
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1" x-text="c.last_message || '—' "></div>
                                <div class="flex flex-wrap gap-1 mt-1" x-show="c.is_group">
                                    <template x-for="p in c.participants" :key="p.id">
                                        <span class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[10px]" x-text="p.role? (p.name+' ('+p.role+')'): p.name"></span>
                                    </template>
                                </div>
                                <span x-show="c.unread>0" class="absolute top-2 right-3 inline-flex items-center justify-center bg-red-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px]" x-text="c.unread"></span>
                            </button>
                        </template>
                        <div x-show="!loading && conversations.length===0" class="p-4 text-xs text-gray-500 dark:text-gray-400">Nenhuma conversa.</div>
                        <div x-show="loading" class="p-4 text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>
                    </div>
                </template>
                <!-- CONTATOS ADMIN -->
                <template x-if="ui.tab==='contacts'">
                    <div>
                        <div class="p-2">
                            <input x-model="contactSearch" @input="debouncedContactFilter()" placeholder="Filtrar contatos..." class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
                        </div>
                        <template x-for="u in filteredContacts()" :key="u.id">
                            <button @click="openDirect(u)" class="relative w-full text-left px-3 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate" x-text="u.name"></span>
                                <span x-show="u.unread>0" class="inline-flex items-center justify-center bg-red-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px]" x-text="u.unread"></span>
                            </button>
                        </template>
                        <div x-show="!contactsLoading && contacts.length===0" class="p-4 text-xs text-gray-500 dark:text-gray-400">Nenhum contato.</div>
                        <div x-show="contactsLoading" class="p-4 text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>
                    </div>
                </template>
            </div>
        </div>
        <!-- CONVERSA -->
        <div class="col-span-12 md:col-span-8 lg:col-span-9 flex flex-col min-h-0 h-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm"
             :class="mobile.view==='conversation' || mobile.view==='none' ? '' : 'mobile-hidden md:flex'">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between" x-show="current" x-cloak>
                <div class="flex items-start gap-3">
                    <button class="md:hidden px-2 py-1 rounded bg-gray-200 dark:bg-gray-700" @click="showList()"><i class="fas fa-arrow-left"></i></button>
                    <div class="flex flex-col">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <span x-text="current?.title"></span>
                            <span x-show="current && current.unread>0" class="inline-flex items-center justify-center bg-red-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px]" x-text="current?.unread"></span>
                        </h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400" x-text="participantsSummary()"></p>
                        <p class="text-[10px] text-blue-600" x-show="typingNames().length" x-text="typingNames().join(', ')+ ' está digitando...' "></p>
                    </div>
                </div>
                <div class="flex items-center gap-2" x-show="current">
                    <button class="px-2 py-1 text-[11px] rounded bg-gray-200 dark:bg-gray-700" @click="openInvite()" x-show="current && current.is_group" title="Convidar"><i class="fas fa-user-plus"></i></button>
                    <button class="px-2 py-1 text-[11px] rounded" :class="(current && isMuted(current.id)) ? 'bg-red-600 text-white hover:bg-red-700':'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200'" @click="current && toggleMute(current.id)" :title="current && isMuted(current.id)?'Desmutar':'Mutar'" x-show="current"><i class="fas" :class="current && isMuted(current.id)?'fa-bell-slash':'fa-bell'"></i></button>
                    <span class="text-[11px] px-2 py-1 rounded bg-green-600 text-white">Ativo</span>
                </div>
            </div>
            <div x-show="!current" class="flex-1 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">Selecione ou crie uma conversa.</div>
            <div class="p-4 flex-1 overflow-y-auto custom-scroll space-y-4 text-sm relative" x-ref="messagesPanel" x-show="current" x-cloak>
                <div x-ref="topSentinel" class="h-1 w-full"></div>
                <div x-show="olderLoading" class="text-center text-[10px] text-gray-400"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>
                <template x-for="(m,i) in messages" :key="m.id">
                    <div>
                        <template x-if="safeIsNewDate(i)"><div class="flex items-center my-2" x-cloak>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                            <span class="mx-2 text-[10px] px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300" x-text="formatDateHeader(m.created_at)"></span>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        </div></template>
                        <div class="flex flex-col" :class="[m.user.id===userId ? 'ml-auto items-end' : 'items-start', m._new ? 'msg-new' : '', compact ? 'max-w-[75%] gap-0.5' : 'max-w-[80%] gap-1']">
                            <div class="text-[10px] mb-0.5" :class="m.is_system ? 'text-yellow-600 dark:text-yellow-300':'text-gray-500 dark:text-gray-400'" x-text="m.is_system ? 'Sistema' : (m.user.id===userId ? 'Você' : (m.user.role? m.user.name+' ('+m.user.role+')':m.user.name))"></div>
                            <template x-if="m.type==='text'">
                                <div :class="['rounded shadow whitespace-pre-wrap break-words',compact?'px-2 py-1 text-[11px]':'px-3 py-2 text-xs', m.is_system ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200' : (m.style_class ? m.style_class : (m.user.id===userId ? 'bg-blue-600 text-white dark:bg-blue-700':'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100'))]" x-text="m.body"></div>
                            </template>
                            <template x-if="m.type!=='text'">
                                <div :class="['rounded shadow bg-gray-200 dark:bg-gray-700 flex flex-col gap-1', compact?'px-2 py-1 text-[11px] max-w-[220px]':'px-3 py-2 text-xs w-full max-w-xs']">
                                    <span class="font-semibold" x-text="m.type.toUpperCase()"></span>
                                    <template x-if="m.attachment_meta && (m.type==='image')">
                                        <img :src="storageUrl(m.attachment_meta.path)" class="max-h-56 max-w-full rounded object-contain" />
                                    </template>
                                    <template x-if="m.type==='audio'">
                                        <audio :src="storageUrl(m.attachment_meta.path)" controls class="w-full"></audio>
                                    </template>
                                    <template x-if="m.type==='file'">
                                        <a :href="storageUrl(m.attachment_meta.path)" target="_blank" class="text-blue-600 underline truncate">Baixar</a>
                                    </template>
                                </div>
                            </template>
                            <div class="text-[9px] mt-1 text-gray-400" x-text="formatTime(m.created_at)"></div>
                        </div>
                    </div>
                </template>
                <div x-show="messagesLoading" class="text-center text-xs text-gray-500"><i class="fas fa-spinner fa-spin"></i></div>
                <button x-show="showScrollBottom" @click="scrollMessagesBottom(true)" class="absolute bottom-3 right-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full w-9 h-9 flex items-center justify-center shadow-lg focus:outline-none" title="Ir para o fim"><i class="fas fa-arrow-down"></i></button>
            </div>
            <form x-show="current" x-cloak @submit.prevent="sendMessage" class="p-3 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-2" enctype="multipart/form-data">
                <!-- Barra de ações que vem de cima -->
                <div class="flex items-center justify-end gap-2 mb-1">
                    <button x-show="hasTemplatePrivilege" @click="openTemplateModal()" class="px-2 py-1 rounded bg-purple-600 hover:bg-purple-700 text-white text-xs" title="Templates"><i class="fas fa-file-signature"></i></button>
                    <a x-show="hasTemplatePrivilege" href="{{ route('chat.broadcast') }}" class="px-2 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs" title="Mensagens Automáticas"><i class="fas fa-bullhorn"></i></a>
                    <button @click="toggleCompact()" class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-xs" :title="compact ? 'Modo espaçado' : 'Modo compacto'"><i class="fas" :class="compact?'fa-compress':'fa-expand'"></i></button>
                </div>

                <div class="flex gap-2 items-end">
                    <textarea x-model="composer.body" @input="handleTyping()" placeholder="Mensagem" class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 resize-none h-14"></textarea>
                    <div class="flex flex-col gap-1 w-40 text-[11px]">
                        <select x-model="composer.type" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-100" @change="composer.type==='text'?resetAttachment():null">
                            <option value="text">Texto</option>
                            <option value="image">Imagem</option>
                            <option value="audio">Áudio</option>
                            <option value="file">Arquivo</option>
                        </select>
                        <input x-show="composer.type!=='text'" x-ref="fileInput" type="file" class="text-[10px]" :accept="fileAccept()" @change="handleFile" />
                        <div x-show="attachmentName" class="text-[10px] truncate" x-text="attachmentName"></div>
                        <template x-if="composer.previewData">
                            <div class="relative border rounded p-1 bg-gray-100 dark:bg-gray-700">
                                <img :src="composer.previewData" class="max-h-24 mx-auto object-contain" />
                                <button type="button" @click="resetAttachment()" class="absolute top-0 right-0 text-red-600 hover:text-red-800 px-1"><i class="fas fa-times"></i></button>
                            </div>
                        </template>
                    </div>
                    <button :disabled="sending || (composer.type==='text' && !composer.template_id && !composer.body.trim())" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold flex items-center gap-2">
                        <i class="fas" :class="sending ? 'fa-spinner fa-spin':'fa-paper-plane'"></i> Enviar
                    </button>
                </div>
                <p class="text-[10px] text-gray-400">Limite: 10MB | Tipos: imagem (jpeg/png/webp), áudio (mp3/ogg), arquivos gerais.</p>
            </form>
        </div>
    </div>

    <!-- MODAIS (mantidos) -->
    <!-- MODAL NOVA CONVERSA -->
    <div x-show="modal.newConversation" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="closeModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded shadow-lg w-full max-w-md p-6 space-y-5 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-plus-circle text-green-600"></i> Nova Conversa</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400">Título (opcional)</label>
                    <input x-model="newConv.title" class="mt-1 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
                </div>
                <div class="space-y-2">
                    <label class="block text-[11px] font-medium text-gray-600 dark:text-gray-400">Adicionar Participantes</label>
                    <div class="flex gap-2">
                        <input x-model="userSearch" @input="debouncedUserSearch()" placeholder="Buscar usuário..." class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
                        <button type="button" @click="clearUserSearch()" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded text-xs divide-y divide-gray-100 dark:divide-gray-700" x-show="userSuggestions.length">
                        <template x-for="u in userSuggestions" :key="u.id">
                            <button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between" @click="addParticipant(u)">
                                <span x-text="u.name"></span>
                                <span class="text-[10px] text-blue-600" x-show="participants.find(p=>p.id===u.id)">Adicionado</span>
                            </button>
                        </template>
                    </div>
                    <div class="flex flex-wrap gap-1" x-show="participants.length">
                        <template x-for="p in participants" :key="p.id">
                            <span class="px-2 py-1 rounded bg-blue-600 text-white text-[11px] flex items-center gap-1">
                                <span x-text="p.name"></span>
                                <button type="button" class="text-white/80 hover:text-white" @click="removeParticipant(p)"><i class="fas fa-times"></i></button>
                            </span>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-400" x-show="!participants.length">Busque e selecione ao menos 1 participante.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="closeModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Cancelar</button>
                <button @click="createConversation()" :disabled="creating || participants.length===0" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold flex items-center gap-2 disabled:opacity-50"><i class="fas" :class="creating ? 'fa-spinner fa-spin':'fa-check'"></i> Criar</button>
            </div>
        </div>
    </div>

    <!-- MODAL CONVITE PARTICIPANTES -->
    <div x-show="modal.invite" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="closeInvite()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded shadow-lg w-full max-w-md p-6 space-y-5 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-user-plus text-blue-600"></i> Convidar Participantes</h3>
            <div class="space-y-3">
                <div class="flex gap-2">
                    <input x-model="invite.search" @input="debouncedInviteSearch()" placeholder="Buscar usuário..." class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
                    <button type="button" @click="invite.search=''; invite.suggestions=[]" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs"><i class="fas fa-times"></i></button>
                </div>
                <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded divide-y divide-gray-100 dark:divide-gray-700 text-xs" x-show="invite.suggestions.length">
                    <template x-for="u in invite.suggestions" :key="u.id">
                        <button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between" @click="toggleInviteUser(u)">
                            <span x-text="u.name"></span>
                            <i class="fas" :class="invite.selected.find(s=>s.id===u.id)?'fa-check text-green-600':'fa-plus text-blue-600'"></i>
                        </button>
                    </template>
                </div>
                <div class="flex flex-wrap gap-1" x-show="invite.selected.length">
                    <template x-for="p in invite.selected" :key="p.id">
                        <span class="px-2 py-1 rounded bg-blue-600 text-white text-[11px] flex items-center gap-1">
                            <span x-text="p.name"></span>
                            <button type="button" @click="toggleInviteUser(p)" class="text-white/80 hover:text-white"><i class="fas fa-times"></i></button>
                        </span>
                    </template>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="closeInvite()" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Fechar</button>
                <button @click="sendInvites()" :disabled="invite.sending || invite.selected.length===0" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold flex items-center gap-2 disabled:opacity-50"><i class="fas" :class="invite.sending ? 'fa-spinner fa-spin':'fa-paper-plane'"></i> Convidar</button>
            </div>
        </div>
    </div>

    <!-- TEMPLATE MODAL -->
    <div x-show="showTemplateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="closeTemplateModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded shadow-lg w-full max-w-lg p-4 sm:p-6 space-y-5 border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-signature text-blue-600"></i>
                    Templates de Mensagem
                </div>
                <button @click="toggleTemplateScope" class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                    <span x-text="templateFilters.onlyMine ? 'Mostrar Todos' : 'Só Meus Templates'"></span>
                </button>
            </h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 md:border-r md:pr-4 border-gray-200 dark:border-gray-700">
                    <p class="text-[11px] text-gray-500">Criar Template</p>
                    <input x-model="templateForm.title" placeholder="Título" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
                    <select x-model="templateForm.scope" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-100">
                        <template x-if="roleId === 1">
                            <option value="global">Global</option>
                        </template>
                        <template x-if="hasTemplatePrivilege">
                            <option value="secretariat">Secretaria</option>
                        </template>
                        <option value="personal">Pessoal</option>
                    </select>
                    <textarea x-model="templateForm.body" placeholder="Corpo" rows="5" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>

                    <!-- Estilos pré-definidos -->
                    <div class="space-y-2">
                        <p class="text-[11px] text-gray-500">Estilo (clique para selecionar):</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="setTemplateStyle('bg-blue-600 text-white')" class="px-2 py-1 rounded text-[10px] bg-blue-600 text-white">Azul</button>
                            <button type="button" @click="setTemplateStyle('bg-green-600 text-white')" class="px-2 py-1 rounded text-[10px] bg-green-600 text-white">Verde</button>
                            <button type="button" @click="setTemplateStyle('bg-purple-600 text-white')" class="px-2 py-1 rounded text-[10px] bg-purple-600 text-white">Roxo</button>
                            <button type="button" @click="setTemplateStyle('bg-red-600 text-white')" class="px-2 py-1 rounded text-[10px] bg-red-600 text-white">Vermelho</button>
                            <button type="button" @click="setTemplateStyle('bg-amber-500 text-white')" class="px-2 py-1 rounded text-[10px] bg-amber-500 text-white">Âmbar</button>
                            <button type="button" @click="setTemplateStyle('bg-teal-600 text-white')" class="px-2 py-1 rounded text-[10px] bg-teal-600 text-white">Turquesa</button>
                            <button type="button" @click="setTemplateStyle('bg-yellow-200 text-gray-900')" class="px-2 py-1 rounded text-[10px] bg-yellow-200 text-gray-900">Amarelo</button>
                            <button type="button" @click="setTemplateStyle('bg-pink-600 text-white')" class="px-2 py-1 rounded text-[10px] bg-pink-600 text-white">Rosa</button>
                        </div>
                    </div>

                    <!-- Preview do template -->
                    <div class="border border-gray-200 dark:border-gray-700 rounded p-2 mt-2">
                        <p class="text-[10px] text-gray-500 mb-1">Preview:</p>
                        <div :class="['text-xs rounded p-2 whitespace-pre-wrap break-words', templateForm.styleClass || 'bg-gray-200 dark:bg-gray-700']" x-text="templateForm.body || 'Texto do template...'"></div>
                    </div>

                    <button @click="createTemplate()" :disabled="templateCreating" class="w-full px-3 py-2 text-xs rounded bg-blue-600 hover:bg-blue-700 text-white font-semibold flex items-center justify-center gap-2 disabled:opacity-50"><i class="fas" :class="templateCreating?'fa-spinner fa-spin':'fa-save'"></i> Salvar</button>
                </div>
                <div class="space-y-2">
                    <p class="text-[11px] text-gray-500 flex items-center justify-between gap-2">
                        <span>Templates</span>
                        <span x-show="templatesLoading"><i class="fas fa-spinner fa-spin"></i></span>
                    </p>
                    <div class="max-h-[60vh] md:max-h-72 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded">
                        <template x-for="t in filteredTemplates()" :key="t.id">
                            <div class="p-2 flex flex-col gap-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold" x-text="t.title"></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded" :class="getScopeClass(t.scope)" x-text="getScopeName(t.scope)"></span>
                                </div>
                                <div :class="['rounded p-2 my-1 whitespace-pre-wrap break-words line-clamp-2', t.style?.class || 'bg-gray-200 dark:bg-gray-700']" x-text="t.body"></div>
                                <div class="flex gap-2 mt-1 justify-end">
                                    <button @click="applyTemplate(t)" class="px-2 py-0.5 rounded bg-green-600 text-white hover:bg-green-700">Usar</button>
                                </div>
                            </div>
                        </template>
                        <div x-show="!templatesLoading && filteredTemplates().length === 0" class="p-3 text-[11px] text-gray-500">Nenhum template.</div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button @click="closeTemplateModal()" class="px-4 py-2 text-xs rounded bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200">Fechar</button>
            </div>
        </div>
    </div>

    <!-- MODAL CONTATOS -->
    <div x-show="contactsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60" @click="closeContactsModal()"></div>
      <div class="relative bg-white dark:bg-gray-800 rounded shadow-lg w-full max-w-md p-6 space-y-4 border border-gray-200 dark:border-gray-700">
         <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-user text-teal-600"></i> Contatos</h3>
         <div class="flex gap-2">
            <input x-model="contactsSearch" @input="debouncedContactsSearch()" placeholder="Buscar usuário..." class="flex-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500" />
            <button @click="contactsSearch=''; contactsResults=[]" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs"><i class="fas fa-times"></i></button>
         </div>
         <div class="max-h-72 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded divide-y divide-gray-100 dark:divide-gray-700 text-xs">
            <template x-for="u in contactsResults" :key="u.id">
                <button @click="startDirect(u)" class="w-full text-left px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between">
                   <span x-text="u.role? (u.name+' ('+u.role+')'):u.name"></span>
                   <i class="fas fa-comment-dots text-blue-600"></i>
                </button>
            </template>
            <div x-show="!contactsLoading && !contactsResults.length" class="p-3 text-[11px] text-gray-500">Nenhum resultado.</div>
            <div x-show="contactsLoading" class="p-3 text-[11px] flex items-center gap-2 text-gray-500"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>
         </div>
         <div class="flex justify-end">
            <button @click="closeContactsModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Fechar</button>
         </div>
      </div>
    </div>
</div>
<script>
function chatApp(){
    return {
        userId: {{ auth()->id() }},
        roleId: {{ $roleId ?? 0 }},
        get hasGroupPrivilege(){ return [1,2].includes(this.roleId); },
        get hasTemplatePrivilege(){ return [1,2].includes(this.roleId); },
        get canSeeAllContacts(){ return this.roleId===1; },
        ui:{tab:'conversations'},
        conversations: [], current: null,
        contacts:[], contactsLoading:false, contactSearch:'',
        templates:[], templatesLoading:false,
        templateForm:{title:'',body:'',scope:'personal',styleClass:''}, templateCreating:false,
        templateFilters: { onlyMine: false },
        showTemplateModal:false, applyingTemplate:null,
        messages: [], lastMessageId: null,
        loading:false, messagesLoading:false, sending:false, creating:false,
        filters:{search:''},
        composer:{type:'text',body:'',template_id:null, previewData: null}, attachmentFile:null, attachmentName:null,
        modal:{newConversation:false, invite:false},
        newConv:{title:''}, userSearch:'', userSuggestions:[], participants:[],
        invite:{search:'', suggestions:[], selected:[], sending:false},
        poll:{convInterval:null, listInterval:null, freqConv:20000, freqList:20000},
        mobile:{view:'none'},
        typing:{ throttleMs:2500, lastSent:0, users:new Set() },
        aborters:{ user:null, invite:null, conversations:null, contacts:null, templates:null },
        echoChannel:null,
        compact:false, contactsModal:false, contactsSearch:'', contactsResults:[], contactsLoading:false,
        muted:[], // ids conversas mutadas
        olderLoading:false, hasMoreOlder:false, showScrollBottom:false,
        observer:null,
        init(){
            this.mobile.view=window.innerWidth<768?'list':'none';
            this.compact=localStorage.getItem('chat.compact')==='1';
            try{ this.muted=JSON.parse(localStorage.getItem('chat.muted')||'[]'); }catch(e){ this.muted=[]; }
            this.reloadConversations();
            if(this.canSeeAllContacts) this.loadContacts();
            if(this.hasTemplatePrivilege) this.loadTemplates();
            this.startPollers();
            window.addEventListener('resize',()=>{ if(window.innerWidth>=768 && this.mobile.view==='list' && this.current){ this.mobile.view='conversation'; } });
            this.$nextTick(()=>{ this.setupObserver(); });
        },
        setupObserver(){ if(!('IntersectionObserver' in window)) return; const sentinel=this.$refs.topSentinel; const panel=this.$refs.messagesPanel; if(!sentinel||!panel) return; const opts={root:panel,threshold:0}; this.observer=new IntersectionObserver((entries)=>{ entries.forEach(e=>{ if(e.isIntersecting && this.hasMoreOlder && !this.olderLoading){ this.fetchUpdates(false,true); } }); }); this.observer.observe(sentinel); },
        // Echo subscription handling
        subscribeConversation(){
            if(!window.Echo || !this.current) return;
            if(this.echoChannel){
                this.echoChannel
                    .stopListening('ChatMessageCreated')
                    .stopListening('ChatParticipantsUpdated')
                    .stopListening('ChatTyping');
            }
            this.echoChannel = window.Echo.private('chat.conversation.'+this.current.id)
                .listen('ChatMessageCreated', (e)=>{
                    if(this.current && e.conversation_id===this.current.id){
                        this.fetchUpdates();
                        this.reloadConversationsQuiet();
                    }
                })
                .listen('ChatParticipantsUpdated', (e)=>{
                    if(this.current && e.conversation_id===this.current.id){
                        this.fetchUpdates(true);
                    }
                })
                .listen('ChatTyping', (e)=>{
                    if(this.current && e.conversation_id===this.current.id && e.user_id!==this.userId){
                        this.typing.users.add(e.user_id);
                        setTimeout(()=>this.typing.users.delete(e.user_id),4000);
                    }
                });
        },
        startPollers(){ this.poll.listInterval=setInterval(()=>{ if(!this.current){ this.reloadConversationsQuiet(); } }, this.poll.freqList); },
        startConvPolling(){ if(this.poll.convInterval) clearInterval(this.poll.convInterval); this.poll.convInterval=setInterval(()=>{ if(this.current) this.fetchUpdates(); }, this.poll.freqConv); },
        stopConvPolling(){ if(this.poll.convInterval) clearInterval(this.poll.convInterval); },
        showList(){ this.mobile.view='list'; },
        showConversation(){ this.mobile.view='conversation'; },
        debouncedReload(){ clearTimeout(this._dr); this._dr=setTimeout(()=>this.reloadConversations(),400); },
        debouncedUserSearch(){ clearTimeout(this._dus); this._dus=setTimeout(()=>this.searchUsers(),300); },
        debouncedInviteSearch(){ clearTimeout(this._dis); this._dis=setTimeout(()=>this.searchInviteUsers(),300); },
        debouncedContactFilter(){ clearTimeout(this._dcf); this._dcf=setTimeout(()=>{/* filtro reativo automático */},200); },
        debouncedContactsSearch(){ clearTimeout(this._dcs); this._dcs=setTimeout(()=>this.searchContacts(),300); },
        storageUrl(p){ return p ? ('/storage/'+p) : '#'; },
        formatTime(iso){ if(!iso) return ''; const d=new Date(iso); return d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})+' '+d.toLocaleDateString(); },
        formatDateHeader(iso){ const d=new Date(iso); const today=new Date(); const ytd=new Date(Date.now()-86400000); const key=d.toISOString().slice(0,10); const todayKey=today.toISOString().slice(0,10); const yKey=ytd.toISOString().slice(0,10); if(key===todayKey) return 'Hoje'; if(key===yKey) return 'Ontem'; return d.toLocaleDateString(); },
        dateKey(iso){ if(!iso) return ''; return new Date(iso).toISOString().slice(0,10); },
        isNewDate(i){ if(i===0) return true; return this.dateKey(this.messages[i].created_at)!==this.dateKey(this.messages[i-1].created_at); },
        buildConvUrl(){ const q=this.filters.search.trim(); return q?`{{ route('api.chat.conversations') }}?q=${encodeURIComponent(q)}`:`{{ route('api.chat.conversations') }}`; },
        abortFetch(key){ if(this.aborters[key]){ this.aborters[key].abort(); } this.aborters[key]=new AbortController(); return this.aborters[key]; },
        reloadConversationsQuiet(){ if(this.loading) return; fetch(this.buildConvUrl()).then(r=>r.json()).then(j=>{ this.mergeConversationUpdates(j.data||[]); }); },
        reloadConversations(){ this.loading=true; const ctl=this.abortFetch('conversations'); fetch(this.buildConvUrl(), {signal:ctl.signal}).then(r=>r.json()).then(j=>{ this.conversations=j.data||[]; if(this.current){ const upd=this.conversations.find(c=>c.id===this.current.id); if(upd) this.current=upd; } }).catch(()=>{}).finally(()=>this.loading=false); },
        mergeConversationUpdates(list){ list.forEach(n=>{ const i=this.conversations.findIndex(c=>c.id===n.id); if(i>=0){ this.conversations[i]=n; } else { this.conversations.unshift(n); } }); },
        selectConversation(c){
            if(this.current && this.current.id===c.id) return;
            this.current=c; this.messages=[]; this.lastMessageId=null; this.hasMoreOlder=false; this.olderLoading=false; this.showScrollBottom=false;
            this.composer.template_id=null; this.applyingTemplate=null;
            this.fetchUpdates(true);
            this.showConversation(); this.startConvPolling(); this.subscribeConversation();
        },
        fetchUpdates(initial=false, older=false){
            if(!this.current) return;
            let url = `/api/chat/conversations/${this.current.id}/updates`;
            if(older && this.messages.length){ url += `?before_id=${this.messages[0].id}`; }
            else if(this.lastMessageId && !older && !initial){ url += `?after_id=${this.lastMessageId}`; }
            if(older) this.olderLoading=true; else if(!this.lastMessageId && initial) this.messagesLoading=true;
            fetch(url).then(r=>r.json()).then(j=>{
                const msgs = j.messages||[];
                if(older){
                    if(msgs.length){ this.messages = msgs.concat(this.messages); }
                } else {
                    if(msgs.length){ msgs.forEach(m=>{ if(!this.messages.find(x=>x.id===m.id)){ m._new=!initial; this.messages.push(m); } }); }
                }
                if(this.messages.length){ this.lastMessageId = this.messages[this.messages.length-1].id; }
                this.hasMoreOlder = !!j.has_more_older;
                if(initial || !older){ this.$nextTick(()=>this.scrollMessagesBottom()); }
            }).finally(()=>{ if(older) this.olderLoading=false; this.messagesLoading=false; });
        },
        loadOlder(){ if(this.olderLoading || !this.hasMoreOlder) return; this.fetchUpdates(false,true); },
        handleScroll(){ const el=this.$refs.messagesPanel; if(!el) return; const bottomGap = el.scrollHeight - (el.scrollTop + el.clientHeight); this.showScrollBottom = bottomGap > 150; if(el.scrollTop<80 && this.hasMoreOlder && !this.olderLoading){ this.loadOlder(); } },
        scrollMessagesBottom(force=false){ const el=this.$refs.messagesPanel; if(!el) return; el.scrollTop = el.scrollHeight; if(force) this.showScrollBottom=false; },
        observeScroll(){ if(!this.$refs.messagesPanel) return; this.$refs.messagesPanel.addEventListener('scroll', this.handleScroll); },
        // theme + layout
        toggleCompact(){ this.compact=!this.compact; localStorage.setItem('chat.compact', this.compact?'1':'0'); },
        toggleThemeLocal(){ const root=document.documentElement; const dark=root.classList.contains('dark'); if(dark){ root.classList.remove('dark'); localStorage.setItem('theme','light'); } else { root.classList.add('dark'); localStorage.setItem('theme','dark'); } },
        // mute
        isMuted(id){ return this.muted.includes(id); },
        toggleMute(id){ if(this.isMuted(id)){ this.muted=this.muted.filter(x=>x!==id); } else { this.muted.push(id); } localStorage.setItem('chat.muted', JSON.stringify(this.muted)); },
        // templates
        loadTemplates(){ if(!this.hasTemplatePrivilege) return; this.templatesLoading=true; fetch('{{ route('api.chat.templates') }}').then(r=>r.json()).then(j=>{ this.templates=j.data||[]; }).finally(()=>this.templatesLoading=false); },
        openTemplateModal(){ this.showTemplateModal=true; if(!this.templates.length) this.loadTemplates(); },
        closeTemplateModal(){ this.showTemplateModal=false; },
        createTemplate(){ if(this.templateCreating) return; if(!this.templateForm.title.trim() || !this.templateForm.body.trim()) return alert('Preencha título e corpo'); this.templateCreating=true; const payload={ title:this.templateForm.title.trim(), body:this.templateForm.body, scope:this.templateForm.scope, style: this.templateForm.styleClass? {class:this.templateForm.styleClass.trim()}:null }; fetch('{{ route('api.chat.templates.store') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(payload)}).then(r=>r.json()).then(j=>{ this.templateForm={title:'',body:'',scope:payload.scope,styleClass:''}; this.loadTemplates(); }).finally(()=>this.templateCreating=false); },
        applyTemplate(t){ this.composer.template_id=t.id; if(!this.composer.body.trim()) this.composer.body=t.body; this.applyingTemplate=t; this.closeTemplateModal(); },
        openNewConversation(){ this.modal.newConversation=true; this.userSearch=''; this.userSuggestions=[]; this.participants=[]; this.newConv.title=''; },
        closeModal(){ this.modal.newConversation=false; },
        searchUsers(){ const q=this.userSearch.trim(); if(!q){ this.userSuggestions=[]; return; } const ctl=this.abortFetch('user'); fetch(`/api/chat/users/search?q=${encodeURIComponent(q)}`,{signal:ctl.signal}).then(r=>r.json()).then(j=>{ this.userSuggestions=j.data||[]; }).catch(()=>{}); },
        addParticipant(u){ if(!this.participants.find(p=>p.id===u.id)) this.participants.push(u); },
        removeParticipant(u){ this.participants=this.participants.filter(p=>p.id!==u.id); },
        clearUserSearch(){ this.userSearch=''; this.userSuggestions=[]; },
        createConversation(){
            if(this.creating || !this.participants.length) return;
            if(!this.hasGroupPrivilege && this.participants.length>1){ alert('Você só pode iniciar conversas individuais. Remova participantes extra.'); return; }
            const ids=this.participants.map(p=>p.id); this.creating=true;
            fetch('{{ route('api.chat.conversations.store') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({title:(this.hasGroupPrivilege? (this.newConv.title||null): null),participants:ids})}).then(r=>r.json()).then(j=>{ if(j.conversation_id){ this.closeModal(); this.reloadConversations(); setTimeout(()=>{ const conv=this.conversations.find(c=>c.id===j.conversation_id); if(conv) this.selectConversation(conv); },400); } }).finally(()=>this.creating=false);
        },
        openInvite(){ if(!this.current) return; this.invite={search:'', suggestions:[], selected:[], sending:false}; this.modal.invite=true; },
        closeInvite(){ this.modal.invite=false; },
        searchInviteUsers(){ const q=this.invite.search.trim(); if(!q){ this.invite.suggestions=[]; return; } const ctl=this.abortFetch('invite'); fetch(`/api/chat/users/search?q=${encodeURIComponent(q)}`,{signal:ctl.signal}).then(r=>r.json()).then(j=>{ const existingIds=this.current.participants.map(p=>p.id); this.invite.suggestions=(j.data||[]).filter(u=>!existingIds.includes(u.id)); }).catch(()=>{}); },
        toggleInviteUser(u){ const i=this.invite.selected.findIndex(s=>s.id===u.id); if(i>=0) this.invite.selected.splice(i,1); else this.invite.selected.push(u); },
        sendInvites(){ if(!this.invite.selected.length || this.invite.sending) return; this.invite.sending=true; const ids=this.invite.selected.map(s=>s.id); fetch(`/api/chat/conversations/${this.current.id}/invite`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({users:ids})}).then(()=>{ this.closeInvite(); this.fetchUpdates(true); this.reloadConversationsQuiet(); }).finally(()=>this.invite.sending=false); },
        // Contacts (admin)
        loadContacts(){ if(!this.canSeeAllContacts) return; if(this.contacts.length) return; this.refreshContacts(); },
        refreshContacts(){ if(!this.canSeeAllContacts) return; this.contactsLoading=true; const ctl=this.abortFetch('contacts'); fetch('{{ route('api.chat.users.all') }}',{signal:ctl.signal}).then(r=>r.json()).then(j=>{ this.contacts=j.data||[]; }).catch(()=>{}).finally(()=>this.contactsLoading=false); },
        filteredContacts(){ const q=this.contactSearch.trim().toLowerCase(); if(!q) return this.contacts; return this.contacts.filter(c=>c.name.toLowerCase().includes(q)); },
        openDirect(u){ fetch(`/api/chat/direct/${u.id}`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).then(j=>{ if(j.conversation_id){ this.reloadConversations(); setTimeout(()=>{ const conv=this.conversations.find(c=>c.id===j.conversation_id); if(conv) this.selectConversation(conv); },400); } }); },
        openContactsModal(){ this.contactsModal=true; this.contactsSearch=''; this.contactsResults=[]; },
        closeContactsModal(){ this.contactsModal=false; },
        searchContacts(){ const q=this.contactsSearch.trim(); if(!q){ this.contactsResults=[]; return; } this.contactsLoading=true; fetch(`/api/chat/users/search?q=${encodeURIComponent(q)}`).then(r=>r.json()).then(j=>{ this.contactsResults=j.data||[]; }).finally(()=>this.contactsLoading=false); },
        startDirect(u){ this.closeContactsModal(); fetch(`/api/chat/direct/${u.id}`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).then(j=>{ if(j.conversation_id){ this.reloadConversations(); setTimeout(()=>{ const conv=this.conversations.find(c=>c.id===j.conversation_id); if(conv) this.selectConversation(conv); },400); } }); },
        // --- Utility / previously missing methods ---
        safeIsNewDate(i) {
            if (i === undefined || !this.messages || !this.messages[i] || !this.messages[i].created_at) {
                return false;
            }
            if (i === 0) return true;
            if (!this.messages[i-1] || !this.messages[i-1].created_at) return true;
            return this.dateKey(this.messages[i].created_at) !== this.dateKey(this.messages[i-1].created_at);
        },
        setTemplateStyle(styleClass) {
            this.templateForm.styleClass = styleClass;
        },
        toggleTemplateScope() {
            this.templateFilters.onlyMine = !this.templateFilters.onlyMine;
        },
        filteredTemplates() {
            if (this.templateFilters.onlyMine) {
                return this.templates.filter(t => t.scope === 'personal');
            }
            return this.templates;
        },
        getScopeClass(scope) {
            switch (scope) {
                case 'global': return 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200';
                case 'secretariat': return 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200';
                case 'personal': return 'bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-200';
                default: return 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200';
            }
        },
        getScopeName(scope) {
            switch (scope) {
                case 'global': return 'Global';
                case 'secretariat': return 'Secretaria';
                case 'personal': return 'Pessoal';
                default: return scope;
            }
        },
        participantsSummary(){ if(!this.current) return ''; return (this.current.participants||[]).map(p=> p.role? `${p.name} (${p.role})`:p.name).join(', '); },
        typingNames(){ if(!this.current) return []; const ids=[...this.typing.users.values()].filter(id=>id!==this.userId); return (this.current.participants||[]).filter(p=>ids.includes(p.id)).map(p=>p.name); },
        handleTyping(){ if(!this.current) return; const now=Date.now(); if(now - this.typing.lastSent < this.typing.throttleMs) return; this.typing.lastSent=now; fetch(`/api/chat/conversations/${this.current.id}/typing`,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}); },
        fileAccept(){ switch(this.composer.type){ case 'image': return 'image/*'; case 'audio': return 'audio/*'; default: return '*/*'; } },
        handleFile(e){ const f=e.target.files[0]; if(!f){ this.resetAttachment(); return; } if(f.size>10*1024*1024){ alert('Arquivo excede 10MB'); e.target.value=''; return; } this.attachmentFile=f; this.attachmentName=f.name; this.composer.previewData=null; if(this.composer.type==='image'){ this.compressImage(f); } },
        compressImage(file){ const maxW=1600, maxH=1600, targetKB=900; const reader=new FileReader(); reader.onload=ev=>{ const img=new Image(); img.onload=()=>{ let {width:w,height:h}=img; const ratio=Math.min(1, maxW/w, maxH/h); if(ratio<1){ w=Math.round(w*ratio); h=Math.round(h*ratio); }
                const canvas=document.createElement('canvas'); canvas.width=w; canvas.height=h; const ctx=canvas.getContext('2d'); ctx.drawImage(img,0,0,w,h); let quality=0.85; const attempt=()=>{ canvas.toBlob(blob=>{ if(!blob){ this.composer.previewData=canvas.toDataURL('image/jpeg',quality); return; } if(blob.size/1024>targetKB && quality>0.4){ quality-=0.1; attempt(); return; } this.attachmentFile = new File([blob], file.name.replace(/\.(png|webp)$/i,'.jpg'), {type:blob.type}); const fr=new FileReader(); fr.onload=ev2=>{ this.composer.previewData=ev2.target.result; }; fr.readAsDataURL(this.attachmentFile); },'image/jpeg',quality); };
                attempt();
            }; img.src=ev.target.result; }; reader.readAsDataURL(file); },
        resetAttachment(){ this.attachmentFile=null; this.attachmentName=null; this.composer.previewData=null; if(this.$refs.fileInput) this.$refs.fileInput.value=''; },
        sendMessage(){ if(this.sending || !this.current) return; if(this.composer.type==='text' && !this.composer.template_id && !this.composer.body.trim()) return; const fd=new FormData(); fd.append('conversation_id',this.current.id); fd.append('type',this.composer.type); if(this.composer.type==='text'){ if(this.composer.body) fd.append('body',this.composer.body); if(this.composer.template_id) fd.append('template_id',this.composer.template_id); } else if(this.attachmentFile){ fd.append('attachment',this.attachmentFile); } this.sending=true; fetch('{{ route('api.chat.messages.send') }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:fd}).then(r=>r.json()).then(j=>{ if(j.id){ this.composer.body=''; this.composer.type='text'; this.composer.template_id=null; this.applyingTemplate=null; this.resetAttachment(); this.fetchUpdates(true); this.reloadConversationsQuiet(); } }).finally(()=>this.sending=false); },
    }
}
</script>
@endsection
