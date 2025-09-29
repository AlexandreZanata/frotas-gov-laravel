@extends('layouts.app')
@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <i class="fas fa-comments text-blue-600"></i>
            Chat
        </h2>
        <div class="flex items-center gap-2">
            @if(auth()->user()->role_id <= 2)
                <a href="{{ route('chat.broadcast') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors duration-200">
                    <i class="fas fa-bullhorn"></i>
                    Mensagens Automáticas
                </a>
            @endif
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto h-[calc(100vh-7rem)] px-2 mt-4 sm:px-4 pb-4" x-data="chatApp()" x-init="init()">
        <style>
            .chat-scrollbar {
                scrollbar-width: thin;
                scrollbar-color: rgb(156 163 175) transparent;
            }
            .chat-scrollbar::-webkit-scrollbar {
                width: 6px;
            }
            .chat-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }
            .chat-scrollbar::-webkit-scrollbar-thumb {
                background-color: rgb(156 163 175);
                border-radius: 3px;
            }
            .dark .chat-scrollbar::-webkit-scrollbar-thumb {
                background-color: rgb(75 85 99);
            }
            .message-entering {
                animation: messageSlideIn 0.3s ease-out;
            }
            @keyframes messageSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .typing-indicator {
                animation: typingPulse 1.5s infinite;
            }
            @keyframes typingPulse {
                0%, 100% { opacity: 0.4; }
                50% { opacity: 1; }
            }
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

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 h-full">
            <!-- Sidebar - Lista de Conversas -->
            <div class="lg:col-span-1 flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                 :class="mobile.view === 'list' ? 'block' : 'hidden lg:flex'">
                <!-- Cabeçalho da Sidebar -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                @click="showList()">
                            <i class="fas fa-arrow-left text-gray-600 dark:text-gray-400"></i>
                        </button>
                        <div class="flex-1 relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input x-model="filters.search"
                                   @input="debouncedReload()"
                                   placeholder="Buscar conversa..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                        </div>
                        <div class="flex gap-1">
                            <button @click="openNewConversation()"
                                    class="p-2.5 text-green-600 hover:text-green-700 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors duration-200"
                                    title="Nova conversa">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button @click="openContactsModal()"
                                    class="p-2.5 text-blue-600 hover:text-blue-700 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors duration-200"
                                    title="Contatos">
                                <i class="fas fa-user"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Abas -->

                </div>
                <!-- Lista de Conversas/Contatos -->
                <div class="flex-1 overflow-y-auto chat-scrollbar">
                    <!-- Conversas -->
                    <template x-if="ui.tab === 'conversations'">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="c in conversations" :key="c.id">
                                <button @click="selectConversation(c)"
                                        class="w-full text-left p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 relative"
                                        :class="current && current.id === c.id ? 'bg-blue-50 dark:bg-blue-900/20 border-r-2 border-blue-500' : ''">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <div class="w-3 h-3 rounded-full"
                                                 :class="c.is_group ? 'bg-green-500' : 'bg-blue-500'"></div>
                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"
                                                  x-text="c.title"></span>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                            <i x-show="isMuted(c.id)"
                                               class="fas fa-bell-slash text-xs text-gray-400"></i>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap"
                                                  x-text="formatTime(c.updated_at)"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 mb-2 text-left"
                                       x-text="c.last_message || 'Nenhuma mensagem'"></p>
                                    <div class="flex items-center justify-between">
                                        <div class="flex -space-x-1" x-show="c.participants && c.participants.length > 0">
                                            <template x-for="(p, index) in c.participants.slice(0, 3)" :key="p.id">
                                                <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs text-white font-medium"
                                                     :class="index > 0 ? 'opacity-80' : ''"
                                                     x-text="p.name.charAt(0).toUpperCase()"></div>
                                            </template>
                                            <div x-show="c.participants.length > 3"
                                                 class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs text-gray-600 dark:text-gray-300">
                                                +<span x-text="c.participants.length - 3"></span>
                                            </div>
                                        </div>
                                        <span x-show="c.unread > 0"
                                              class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full min-w-[20px] text-center"
                                              x-text="c.unread"></span>
                                    </div>
                                </button>
                            </template>
                            <div x-show="!loading && conversations.length === 0"
                                 class="p-8 text-center">
                                <i class="fas fa-comments text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma conversa encontrada</p>
                            </div>
                            <div x-show="loading"
                                 class="p-8 text-center">
                                <i class="fas fa-spinner fa-spin text-blue-500 text-lg"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Carregando conversas...</p>
                            </div>
                        </div>
                    </template>
                    <!-- Contatos -->
                    <template x-if="ui.tab === 'contacts'">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div class="p-3">
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <input x-model="contactSearch"
                                           @input="debouncedContactFilter()"
                                           placeholder="Filtrar contatos..."
                                           class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                                </div>
                            </div>
                            <template x-for="u in filteredContacts()" :key="u.id">
                                <button @click="openDirect(u)"
                                        class="w-full text-left p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-medium"
                                             x-text="u.name.charAt(0).toUpperCase()"></div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 text-left"
                                               x-text="u.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 text-left"
                                               x-text="u.role || 'Usuário'"></p>
                                        </div>
                                    </div>
                                    <span x-show="u.unread > 0"
                                          class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full min-w-[20px] text-center"
                                          x-text="u.unread"></span>
                                </button>
                            </template>
                            <div x-show="!contactsLoading && contacts.length === 0"
                                 class="p-8 text-center">
                                <i class="fas fa-users text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum contato encontrado</p>
                            </div>
                            <div x-show="contactsLoading"
                                 class="p-8 text-center">
                                <i class="fas fa-spinner fa-spin text-blue-500 text-lg"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Carregando contatos...</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Área de Conversa -->
            <div class="lg:col-span-3 flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                 :class="mobile.view === 'conversation' || mobile.view === 'none' ? 'block' : 'hidden lg:flex'">
                <!-- Cabeçalho da Conversa -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                     x-show="current" x-cloak>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button class="lg:hidden p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                    @click="showList()">
                                <i class="fas fa-arrow-left text-gray-600 dark:text-gray-400"></i>
                            </button>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold"
                                 x-text="current.title.charAt(0).toUpperCase()"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                    <span x-text="current.title"></span>
                                    <span x-show="current && current.unread > 0"
                                          class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full"
                                          x-text="current.unread"></span>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300"
                                   x-text="participantsSummary()"></p>
                                <p class="text-xs text-blue-500 font-medium mt-1"
                                   x-show="typingNames().length"
                                   x-text="typingNames().join(', ') + ' está digitando...'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openInvite()"
                                    x-show="current && current.is_group"
                                    class="p-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                                    title="Adicionar participantes">
                                <i class="fas fa-user-plus"></i>
                            </button>
                            <button @click="current && toggleMute(current.id)"
                                    class="p-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                                    :title="current && isMuted(current.id) ? 'Desmutar conversa' : 'Mutuar conversa'">
                                <i class="fas" :class="current && isMuted(current.id) ? 'fa-bell-slash' : 'fa-bell'"></i>
                            </button>
                            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 text-xs font-medium rounded-full flex items-center gap-1">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            Online
                        </span>
                        </div>
                    </div>
                </div>

                <!-- Placeholder quando não há conversa selecionada -->
                <div x-show="!current"
                     class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                    <i class="fas fa-comments text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-400 mb-2">Selecione uma conversa</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Escolha uma conversa existente ou inicie uma nova</p>
                    <button @click="openNewConversation()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus"></i>
                        Nova Conversa
                    </button>
                </div>

                <!-- Área de Mensagens -->
                <div class="flex-1 overflow-y-auto chat-scrollbar bg-gray-50 dark:bg-gray-900/30 p-4"
                     x-ref="messagesPanel"
                     x-show="current" x-cloak>
                    <div x-ref="topSentinel" class="h-2"></div>
                    <div x-show="olderLoading"
                         class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-blue-500 text-lg"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Carregando mensagens anteriores...</p>
                    </div>
                    <template x-for="(m, i) in messages" :key="m.id">
                        <div class="message-entering">
                            <!-- Separador de data -->
                            <template x-if="safeIsNewDate(i)">
                                <div class="flex items-center my-6" x-cloak>
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                    <span class="mx-4 px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-medium rounded-full"
                                          x-text="formatDateHeader(m.created_at)"></span>
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                                </div>
                            </template>
                            <!-- Mensagem -->
                            <div class="flex gap-3 mb-4" :class="m.user.id === userId ? 'justify-end' : 'justify-start'">
                                <!-- Avatar do remetente -->
                                <div class="flex-shrink-0" x-show="m.user.id !== userId">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-medium"
                                         x-text="m.user.name.charAt(0).toUpperCase()"></div>
                                </div>
                                <!-- Conteúdo da mensagem -->
                                <div class="max-w-[70%]"
                                     :class="m.user.id === userId ? 'order-1' : 'order-2'">
                                    <!-- Informações do remetente -->
                                    <div class="mb-1 text-xs"
                                         :class="m.is_system ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400'"
                                         x-text="m.is_system ? 'Sistema' : (m.user.id === userId ? 'Você' : m.user.name + (m.user.role ? ' (' + m.user.role + ')' : ''))"></div>
                                    <!-- Mensagem de texto -->
                                    <template x-if="m.type === 'text'">
                                        <div :class="['rounded-2xl px-4 py-2 shadow-sm', m.is_system ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200' : (m.style_class ? m.style_class : (m.user.id === userId ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600'))]">
                                            <p class="text-sm whitespace-pre-wrap break-words" x-text="m.body"></p>
                                        </div>
                                    </template>
                                    <!-- Outros tipos de mensagem -->
                                    <template x-if="m.type !== 'text'">
                                        <div :class="['rounded-xl p-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm']">
                                            <div class="flex items-center gap-2 mb-2">
                                                <i class="fas" :class="getFileTypeIcon(m.type)"></i>
                                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200"
                                                      x-text="m.type.charAt(0).toUpperCase() + m.type.slice(1)"></span>
                                            </div>
                                            <template x-if="m.attachment_meta && m.type === 'image'">
                                                <img :src="storageUrl(m.attachment_meta.path)"
                                                     class="max-w-full max-h-64 rounded-lg object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                                     @click="openImageModal(m.attachment_meta.path)">
                                            </template>
                                            <template x-if="m.type === 'audio'">
                                                <audio :src="storageUrl(m.attachment_meta.path)"
                                                       controls
                                                       class="w-full rounded-lg"></audio>
                                            </template>
                                            <template x-if="m.type === 'file'">
                                                <a :href="storageUrl(m.attachment_meta.path)"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                                    <i class="fas fa-download"></i>
                                                    <span class="text-sm" x-text="m.attachment_meta.name || 'Download'"></span>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                    <!-- Timestamp -->
                                    <div class="mt-1 text-xs text-gray-400 text-right"
                                         x-text="formatTime(m.created_at)"></div>
                                </div>
                                <!-- Avatar do usuário atual -->
                                <div class="flex-shrink-0" x-show="m.user.id === userId">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                        V
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="messagesLoading"
                         class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-blue-500 text-lg"></i>
                    </div>
                    <!-- Botão para rolar para baixo -->
                    <button x-show="showScrollBottom"
                            @click="scrollMessagesBottom(true)"
                            class="fixed bottom-20 right-6 w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-110"
                            title="Ir para a mensagem mais recente">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                </div>

                <!-- Editor de Mensagens -->
                <form x-show="current" x-cloak
                      @submit.prevent="sendMessage"
                      class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <!-- Ações rápidas -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="openTemplateModal()"
                                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                                    title="Usar template">
                                <i class="fas fa-file-signature"></i>
                            </button>
                            <label class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 cursor-pointer"
                                   title="Anexar arquivo">
                                <i class="fas fa-paperclip"></i>
                                <input type="file"
                                       x-ref="fileInput"
                                       class="hidden"
                                       :accept="fileAccept()"
                                       @change="handleFile">
                            </label>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <span x-show="attachmentName" x-text="attachmentName" class="mr-2"></span>
                            Máx. 10MB
                        </div>
                    </div>
                    <!-- Área de composição -->
                    <div class="flex gap-3">
                        <div class="flex-1 relative">
                        <textarea x-model="composer.body"
                                  @input="handleTyping()"
                                  placeholder="Digite sua mensagem..."
                                  rows="1"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-xl resize-none text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors"></textarea>
                            <!-- Preview de imagem -->
                            <div x-show="composer.previewData"
                                 class="absolute bottom-16 left-0 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-2">
                                <img :src="composer.previewData"
                                     class="max-h-32 rounded-lg">
                                <button type="button"
                                        @click="resetAttachment()"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit"
                                :disabled="sending || (composer.type === 'text' && !composer.template_id && !composer.body.trim())"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-200 hover:scale-105 flex items-center gap-2">
                            <i class="fas" :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                            <span class="hidden sm:inline">Enviar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Nova Conversa -->
        <div x-show="modal.newConversation" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-hidden border border-gray-200 dark:border-gray-700">
                <!-- Cabeçalho -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Nova Conversa</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Inicie uma nova conversa</p>
                        </div>
                    </div>
                </div>
                <!-- Conteúdo -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Título da conversa</label>
                        <input x-model="newConv.title"
                               placeholder="Opcional - deixe em branco para usar nomes dos participantes"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adicionar participantes</label>
                        <div class="flex gap-2 mb-3">
                            <div class="flex-1 relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input x-model="userSearch"
                                       @input="debouncedUserSearch()"
                                       placeholder="Buscar usuários..."
                                       class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                            </div>
                        </div>
                        <!-- Sugestões de usuários -->
                        <div class="max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-100 dark:divide-gray-700 mb-3"
                             x-show="userSuggestions.length > 0">
                            <template x-for="u in userSuggestions" :key="u.id">
                                <button type="button"
                                        @click="addParticipant(u)"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-medium"
                                             x-text="u.name.charAt(0).toUpperCase()"></div>
                                        <span class="text-sm text-gray-800 dark:text-gray-200" x-text="u.name"></span>
                                    </div>
                                    <i class="fas fa-plus text-green-500" x-show="!participants.find(p => p.id === u.id)"></i>
                                    <i class="fas fa-check text-blue-500" x-show="participants.find(p => p.id === u.id)"></i>
                                </button>
                            </template>
                        </div>
                        <!-- Participantes selecionados -->
                        <div x-show="participants.length > 0">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Participantes selecionados (<span x-text="participants.length"></span>)
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="p in participants" :key="p.id">
                                    <div class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-full flex items-center gap-2 text-sm">
                                        <span x-text="p.name"></span>
                                        <button type="button"
                                                @click="removeParticipant(p)"
                                                class="w-4 h-4 bg-blue-200 dark:bg-blue-800 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 hover:bg-blue-300 dark:hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-show="participants.length === 0">
                            Busque e selecione ao menos 1 participante
                        </p>
                    </div>
                </div>
                <!-- Rodapé -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                    <button @click="closeModal()"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        Cancelar
                    </button>
                    <button @click="createConversation()"
                            :disabled="creating || participants.length === 0"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <i class="fas" :class="creating ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                        Criar Conversa
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Imagem -->
        <div x-show="imageModal.open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm">
            <div class="relative max-w-4xl max-h-full">
                <button @click="imageModal.open = false"
                        class="absolute -top-4 -right-4 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors z-10">
                    <i class="fas fa-times"></i>
                </button>
                <img :src="storageUrl(imageModal.src)"
                     class="max-w-full max-h-full object-contain rounded-lg">
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
                            <button type="button" class="text-white/80 hover:text-white"><i class="fas fa-times"></i></button>
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
                            <p class="text-[11px] text-gray-500 mb-1">Preview:</p>
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
        function chatApp() {
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
                modal:{newConversation:false, invite:false}, newConv:{title:''}, userSearch:'', userSuggestions:[], participants:[],
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
                imageModal: {
                    open: false,
                    src: ''
                },

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

                setupObserver(){
                    if(!('IntersectionObserver' in window)) return;
                    const sentinel=this.$refs.topSentinel;
                    const panel=this.$refs.messagesPanel;
                    if(!sentinel||!panel) return;
                    const opts={root:panel,threshold:0};
                    this.observer=new IntersectionObserver((entries)=>{
                        entries.forEach(e=>{
                            if(e.isIntersecting && this.hasMoreOlder && !this.olderLoading){
                                this.fetchUpdates(false,true);
                            }
                        });
                    });
                    this.observer.observe(sentinel);
                },

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

                startPollers(){
                    this.poll.listInterval=setInterval(()=>{
                        if(!this.current){
                            this.reloadConversationsQuiet();
                        }
                    }, this.poll.freqList);
                },

                startConvPolling(){
                    if(this.poll.convInterval) clearInterval(this.poll.convInterval);
                    this.poll.convInterval=setInterval(()=>{
                        if(this.current) this.fetchUpdates();
                    }, this.poll.freqConv);
                },

                stopConvPolling(){
                    if(this.poll.convInterval) clearInterval(this.poll.convInterval);
                },

                showList(){
                    this.mobile.view='list';
                },

                showConversation(){
                    this.mobile.view='conversation';
                },

                debouncedReload(){
                    clearTimeout(this._dr);
                    this._dr=setTimeout(()=>this.reloadConversations(),400);
                },

                debouncedUserSearch(){
                    clearTimeout(this._dus);
                    this._dus=setTimeout(()=>this.searchUsers(),300);
                },

                debouncedInviteSearch(){
                    clearTimeout(this._dis);
                    this._dis=setTimeout(()=>this.searchInviteUsers(),300);
                },

                debouncedContactFilter(){
                    clearTimeout(this._dcf);
                    this._dcf=setTimeout(()=>{},200);
                },

                debouncedContactsSearch(){
                    clearTimeout(this._dcs);
                    this._dcs=setTimeout(()=>this.searchContacts(),300);
                },

                storageUrl(p){
                    return p ? ('/storage/'+p) : '#';
                },

                formatTime(iso){
                    if(!iso) return '';
                    const d=new Date(iso);
                    return d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})+' '+d.toLocaleDateString();
                },

                formatDateHeader(iso){
                    const d=new Date(iso);
                    const today=new Date();
                    const ytd=new Date(Date.now()-86400000);
                    const key=d.toISOString().slice(0,10);
                    const todayKey=today.toISOString().slice(0,10);
                    const yKey=ytd.toISOString().slice(0,10);
                    if(key===todayKey) return 'Hoje';
                    if(key===yKey) return 'Ontem';
                    return d.toLocaleDateString();
                },

                dateKey(iso){
                    if(!iso) return '';
                    return new Date(iso).toISOString().slice(0,10);
                },

                safeIsNewDate(i) {
                    if (i === undefined || !this.messages || !this.messages[i] || !this.messages[i].created_at) {
                        return false;
                    }
                    if (i === 0) return true;
                    if (!this.messages[i-1] || !this.messages[i-1].created_at) return true;
                    return this.dateKey(this.messages[i].created_at) !== this.dateKey(this.messages[i-1].created_at);
                },

                isNewDate(i){
                    if(i===0) return true;
                    return this.dateKey(this.messages[i].created_at)!==this.dateKey(this.messages[i-1].created_at);
                },

                buildConvUrl(){
                    const q=this.filters.search.trim();
                    return q?`{{ route('api.chat.conversations') }}?q=${encodeURIComponent(q)}`:`{{ route('api.chat.conversations') }}`;
                },

                abortFetch(key){
                    if(this.aborters[key]){
                        this.aborters[key].abort();
                    }
                    this.aborters[key]=new AbortController();
                    return this.aborters[key];
                },

                reloadConversationsQuiet(){
                    if(this.loading) return;
                    fetch(this.buildConvUrl()).then(r=>r.json()).then(j=>{
                        this.mergeConversationUpdates(j.data||[]);
                    });
                },

                reloadConversations(){
                    this.loading=true;
                    const ctl=this.abortFetch('conversations');
                    fetch(this.buildConvUrl(), {signal:ctl.signal}).then(r=>r.json()).then(j=>{
                        this.conversations=j.data||[];
                        if(this.current){
                            const upd=this.conversations.find(c=>c.id===this.current.id);
                            if(upd) this.current=upd;
                        }
                    }).catch(()=>{}).finally(()=>this.loading=false);
                },

                mergeConversationUpdates(list){
                    list.forEach(n=>{
                        const i=this.conversations.findIndex(c=>c.id===n.id);
                        if(i>=0){
                            this.conversations[i]=n;
                        } else {
                            this.conversations.unshift(n);
                        }
                    });
                },

                selectConversation(c){
                    if(this.current && this.current.id===c.id) return;
                    this.current=c;
                    this.messages=[];
                    this.lastMessageId=null;
                    this.hasMoreOlder=false;
                    this.olderLoading=false;
                    this.showScrollBottom=false;
                    this.composer.template_id=null;
                    this.applyingTemplate=null;
                    this.fetchUpdates(true);
                    this.showConversation();
                    this.startConvPolling();
                    this.subscribeConversation();
                },

                fetchUpdates(initial=false, older=false){
                    if(!this.current) return;
                    let url = `/api/chat/conversations/${this.current.id}/updates`;
                    if(older && this.messages.length){
                        url += `?before_id=${this.messages[0].id}`;
                    }
                    else if(this.lastMessageId && !older && !initial){
                        url += `?after_id=${this.lastMessageId}`;
                    }
                    if(older) this.olderLoading=true;
                    else if(!this.lastMessageId && initial) this.messagesLoading=true;
                    fetch(url).then(r=>r.json()).then(j=>{
                        const msgs = j.messages||[];
                        if(older){
                            if(msgs.length){
                                this.messages = msgs.concat(this.messages);
                            }
                        } else {
                            if(msgs.length){
                                msgs.forEach(m=>{
                                    if(!this.messages.find(x=>x.id===m.id)){
                                        m._new=!initial;
                                        this.messages.push(m);
                                    }
                                });
                            }
                        }
                        if(this.messages.length){
                            this.lastMessageId = this.messages[this.messages.length-1].id;
                        }
                        this.hasMoreOlder = !!j.has_more_older;
                        if(initial || !older){
                            this.$nextTick(()=>this.scrollMessagesBottom());
                        }
                    }).finally(()=>{
                        if(older) this.olderLoading=false;
                        this.messagesLoading=false;
                    });
                },

                loadOlder(){
                    if(this.olderLoading || !this.hasMoreOlder) return;
                    this.fetchUpdates(false,true);
                },

                handleScroll(){
                    const el=this.$refs.messagesPanel;
                    if(!el) return;
                    const bottomGap = el.scrollHeight - (el.scrollTop + el.clientHeight);
                    this.showScrollBottom = bottomGap > 150;
                    if(el.scrollTop<80 && this.hasMoreOlder && !this.olderLoading){
                        this.loadOlder();
                    }
                },

                scrollMessagesBottom(force=false){
                    const el=this.$refs.messagesPanel;
                    if(!el) return;
                    el.scrollTop = el.scrollHeight;
                    if(force) this.showScrollBottom=false;
                },

                observeScroll(){
                    if(!this.$refs.messagesPanel) return;
                    this.$refs.messagesPanel.addEventListener('scroll', this.handleScroll);
                },

                toggleCompact(){
                    this.compact=!this.compact;
                    localStorage.setItem('chat.compact', this.compact?'1':'0');
                },

                toggleThemeLocal(){
                    const root=document.documentElement;
                    const dark=root.classList.contains('dark');
                    if(dark){
                        root.classList.remove('dark');
                        localStorage.setItem('theme','light');
                    } else {
                        root.classList.add('dark');
                        localStorage.setItem('theme','dark');
                    }
                },

                isMuted(id){
                    return this.muted.includes(id);
                },

                toggleMute(id){
                    if(this.isMuted(id)){
                        this.muted=this.muted.filter(x=>x!==id);
                    } else {
                        this.muted.push(id);
                    }
                    localStorage.setItem('chat.muted', JSON.stringify(this.muted));
                },

                loadTemplates(){
                    if(!this.hasTemplatePrivilege) return;
                    this.templatesLoading=true;
                    fetch('{{ route('api.chat.templates') }}').then(r=>r.json()).then(j=>{
                        this.templates=j.data||[];
                    }).finally(()=>this.templatesLoading=false);
                },

                openTemplateModal(){
                    this.showTemplateModal=true;
                    if(!this.templates.length) this.loadTemplates();
                },

                closeTemplateModal(){
                    this.showTemplateModal=false;
                },

                createTemplate(){
                    if(this.templateCreating) return;
                    if(!this.templateForm.title.trim() || !this.templateForm.body.trim()) return alert('Preencha título e corpo');
                    this.templateCreating=true;
                    const payload={
                        title:this.templateForm.title.trim(),
                        body:this.templateForm.body,
                        scope:this.templateForm.scope,
                        style: this.templateForm.styleClass? {class:this.templateForm.styleClass.trim()}:null
                    };
                    fetch('{{ route('api.chat.templates.store') }}',{
                        method:'POST',
                        headers:{
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        },
                        body:JSON.stringify(payload)
                    }).then(r=>r.json()).then(j=>{
                        this.templateForm={title:'',body:'',scope:payload.scope,styleClass:''};
                        this.loadTemplates();
                    }).finally(()=>this.templateCreating=false);
                },

                applyTemplate(t){
                    this.composer.template_id=t.id;
                    if(!this.composer.body.trim()) this.composer.body=t.body;
                    this.applyingTemplate=t;
                    this.closeTemplateModal();
                },

                openNewConversation(){
                    this.modal.newConversation=true;
                    this.userSearch='';
                    this.userSuggestions=[];
                    this.participants=[];
                    this.newConv.title='';
                },

                closeModal(){
                    this.modal.newConversation=false;
                },

                searchUsers(){
                    const q=this.userSearch.trim();
                    if(!q){
                        this.userSuggestions=[];
                        return;
                    }
                    const ctl=this.abortFetch('user');
                    fetch(`/api/chat/users/search?q=${encodeURIComponent(q)}`,{signal:ctl.signal}).then(r=>r.json()).then(j=>{
                        this.userSuggestions=j.data||[];
                    }).catch(()=>{});
                },

                addParticipant(u){
                    if(!this.participants.find(p=>p.id===u.id)) this.participants.push(u);
                },

                removeParticipant(u){
                    this.participants=this.participants.filter(p=>p.id!==u.id);
                },

                clearUserSearch(){
                    this.userSearch='';
                    this.userSuggestions=[];
                },

                createConversation(){
                    if(this.creating || !this.participants.length) return;
                    if(!this.hasGroupPrivilege && this.participants.length>1){
                        alert('Você só pode iniciar conversas individuais. Remova participantes extra.');
                        return;
                    }
                    const ids=this.participants.map(p=>p.id);
                    this.creating=true;
                    fetch('{{ route('api.chat.conversations.store') }}',{
                        method:'POST',
                        headers:{
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        },
                        body:JSON.stringify({
                            title:(this.hasGroupPrivilege? (this.newConv.title||null): null),
                            participants:ids
                        })
                    }).then(r=>r.json()).then(j=>{
                        if(j.conversation_id){
                            this.closeModal();
                            this.reloadConversations();
                            setTimeout(()=>{
                                const conv=this.conversations.find(c=>c.id===j.conversation_id);
                                if(conv) this.selectConversation(conv);
                            },400);
                        }
                    }).finally(()=>this.creating=false);
                },

                openInvite(){
                    if(!this.current) return;
                    this.invite={search:'', suggestions:[], selected:[], sending:false};
                    this.modal.invite=true;
                },

                closeInvite(){
                    this.modal.invite=false;
                },

                searchInviteUsers(){
                    const q=this.invite.search.trim();
                    if(!q){
                        this.invite.suggestions=[];
                        return;
                    }
                    const ctl=this.abortFetch('invite');
                    fetch(`/api/chat/users/search?q=${encodeURIComponent(q)}`,{signal:ctl.signal}).then(r=>r.json()).then(j=>{
                        const existingIds=this.current.participants.map(p=>p.id);
                        this.invite.suggestions=(j.data||[]).filter(u=>!existingIds.includes(u.id));
                    }).catch(()=>{});
                },

                toggleInviteUser(u){
                    const i=this.invite.selected.findIndex(s=>s.id===u.id);
                    if(i>=0) this.invite.selected.splice(i,1);
                    else this.invite.selected.push(u);
                },

                sendInvites(){
                    if(!this.invite.selected.length || this.invite.sending) return;
                    this.invite.sending=true;
                    const ids=this.invite.selected.map(s=>s.id);
                    fetch(`/api/chat/conversations/${this.current.id}/invite`,{
                        method:'POST',
                        headers:{
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        },
                        body:JSON.stringify({users:ids})
                    }).then(()=>{
                        this.closeInvite();
                        this.fetchUpdates(true);
                        this.reloadConversationsQuiet();
                    }).finally(()=>this.invite.sending=false);
                },

                loadContacts(){
                    if(!this.canSeeAllContacts) return;
                    if(this.contacts.length) return;
                    this.refreshContacts();
                },

                refreshContacts(){
                    if(!this.canSeeAllContacts) return;
                    this.contactsLoading=true;
                    const ctl=this.abortFetch('contacts');
                    fetch('{{ route('api.chat.users.all') }}',{signal:ctl.signal}).then(r=>r.json()).then(j=>{
                        this.contacts=j.data||[];
                    }).catch(()=>{}).finally(()=>this.contactsLoading=false);
                },

                filteredContacts(){
                    const q=this.contactSearch.trim().toLowerCase();
                    if(!q) return this.contacts;
                    return this.contacts.filter(c=>c.name.toLowerCase().includes(q));
                },

                openDirect(u){
                    fetch(`/api/chat/direct/${u.id}`,{
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        }
                    }).then(r=>r.json()).then(j=>{
                        if(j.conversation_id){
                            this.reloadConversations();
                            setTimeout(()=>{
                                const conv=this.conversations.find(c=>c.id===j.conversation_id);
                                if(conv) this.selectConversation(conv);
                            },400);
                        }
                    });
                },

                openContactsModal(){
                    this.contactsModal=true;
                    this.contactsSearch='';
                    this.contactsResults=[];
                },

                closeContactsModal(){
                    this.contactsModal=false;
                },

                searchContacts(){
                    const q=this.contactsSearch.trim();
                    if(!q){
                        this.contactsResults=[];
                        return;
                    }
                    this.contactsLoading=true;
                    fetch(`/api/chat/users/search?q=${encodeURIComponent(q)}`).then(r=>r.json()).then(j=>{
                        this.contactsResults=j.data||[];
                    }).finally(()=>this.contactsLoading=false);
                },

                startDirect(u){
                    this.closeContactsModal();
                    fetch(`/api/chat/direct/${u.id}`,{
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        }
                    }).then(r=>r.json()).then(j=>{
                        if(j.conversation_id){
                            this.reloadConversations();
                            setTimeout(()=>{
                                const conv=this.conversations.find(c=>c.id===j.conversation_id);
                                if(conv) this.selectConversation(conv);
                            },400);
                        }
                    });
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

                participantsSummary(){
                    if(!this.current) return '';
                    return (this.current.participants||[]).map(p=> p.role? `${p.name} (${p.role})`:p.name).join(', ');
                },

                typingNames(){
                    if(!this.current) return [];
                    const ids=[...this.typing.users.values()].filter(id=>id!==this.userId);
                    return (this.current.participants||[]).filter(p=>ids.includes(p.id)).map(p=>p.name);
                },

                handleTyping(){
                    if(!this.current) return;
                    const now=Date.now();
                    if(now - this.typing.lastSent < this.typing.throttleMs) return;
                    this.typing.lastSent=now;
                    fetch(`/api/chat/conversations/${this.current.id}/typing`,{
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        }
                    });
                },

                fileAccept(){
                    switch(this.composer.type){
                        case 'image': return 'image/*';
                        case 'audio': return 'audio/*';
                        default: return '*/*';
                    }
                },

                handleFile(e){
                    const f=e.target.files[0];
                    if(!f){
                        this.resetAttachment();
                        return;
                    }
                    if(f.size>10*1024*1024){
                        alert('Arquivo excede 10MB');
                        e.target.value='';
                        return;
                    }
                    this.attachmentFile=f;
                    this.attachmentName=f.name;
                    this.composer.previewData=null;
                    if(this.composer.type==='image'){
                        this.compressImage(f);
                    }
                },

                compressImage(file){
                    const maxW=1600, maxH=1600, targetKB=900;
                    const reader=new FileReader();
                    reader.onload=ev=>{
                        const img=new Image();
                        img.onload=()=>{
                            let {width:w,height:h}=img;
                            const ratio=Math.min(1, maxW/w, maxH/h);
                            if(ratio<1){
                                w=Math.round(w*ratio);
                                h=Math.round(h*ratio);
                            }
                            const canvas=document.createElement('canvas');
                            canvas.width=w;
                            canvas.height=h;
                            const ctx=canvas.getContext('2d');
                            ctx.drawImage(img,0,0,w,h);
                            let quality=0.85;
                            const attempt=()=>{
                                canvas.toBlob(blob=>{
                                    if(!blob){
                                        this.composer.previewData=canvas.toDataURL('image/jpeg',quality);
                                        return;
                                    }
                                    if(blob.size/1024>targetKB && quality>0.4){
                                        quality-=0.1;
                                        attempt();
                                        return;
                                    }
                                    this.attachmentFile = new File([blob], file.name.replace(/\.(png|webp)$/i,'.jpg'), {type:blob.type});
                                    const fr=new FileReader();
                                    fr.onload=ev2=>{
                                        this.composer.previewData=ev2.target.result;
                                    };
                                    fr.readAsDataURL(this.attachmentFile);
                                },'image/jpeg',quality);
                            };
                            attempt();
                        };
                        img.src=ev.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                resetAttachment(){
                    this.attachmentFile=null;
                    this.attachmentName=null;
                    this.composer.previewData=null;
                    if(this.$refs.fileInput) this.$refs.fileInput.value='';
                },

                sendMessage(){
                    if(this.sending || !this.current) return;
                    if(this.composer.type==='text' && !this.composer.template_id && !this.composer.body.trim()) return;
                    const fd=new FormData();
                    fd.append('conversation_id',this.current.id);
                    fd.append('type',this.composer.type);
                    if(this.composer.type==='text'){
                        if(this.composer.body) fd.append('body',this.composer.body);
                        if(this.composer.template_id) fd.append('template_id',this.composer.template_id);
                    } else if(this.attachmentFile){
                        fd.append('attachment',this.attachmentFile);
                    }
                    this.sending=true;
                    fetch('{{ route('api.chat.messages.send') }}',{
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
                        },
                        body:fd
                    }).then(r=>r.json()).then(j=>{
                        if(j.id){
                            this.composer.body='';
                            this.composer.type='text';
                            this.composer.template_id=null;
                            this.applyingTemplate=null;
                            this.resetAttachment();
                            this.fetchUpdates(true);
                            this.reloadConversationsQuiet();
                        }
                    }).finally(()=>this.sending=false);
                },

                getFileTypeIcon(type) {
                    const icons = {
                        image: 'fa-image text-green-500',
                        audio: 'fa-music text-purple-500',
                        file: 'fa-file text-blue-500'
                    };
                    return icons[type] || 'fa-file text-gray-500';
                },

                openImageModal(src) {
                    this.imageModal.src = src;
                    this.imageModal.open = true;
                }
            }
        }
    </script>
@endsection
