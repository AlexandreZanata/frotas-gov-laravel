@extends('layouts.app')
@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <i class="fas fa-bullhorn text-blue-600"></i>
            Mensagens Automáticas
        </h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('chat.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left"></i>
                Voltar para Chat
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto h-[calc(100vh-7rem)] px-2 mt-4 sm:px-4 pb-4" x-data="broadcastMessages()" x-init="init()">
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
            @media (max-width: 767px) {
                .mobile-hidden { display: none !important; }
            }
        </style>

        <div class="grid grid-cols-1 lg:grid-cols-1 gap-4 h-full">
            <!-- Container Principal -->
            <div class="flex flex-col h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex-1 overflow-y-auto chat-scrollbar p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Enviar Mensagens Automáticas</h3>

                    <form @submit.prevent="sendMessages" class="space-y-6">
                        <!-- Seleção de destinatários -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Destinatários</label>
                                <div class="mt-1 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="form.recipientsType" value="users" class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Usuários Específicos</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="form.recipientsType" value="secretariats" class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Secretarias</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Seleção de usuários -->
                            <div x-show="form.recipientsType === 'users'" x-transition>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecionar Usuários</label>
                                <div class="mt-1">
                                    <div class="flex gap-2 mb-2">
                                        <input x-model="userSearch" @input="debouncedSearchUsers" placeholder="Buscar usuários..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-3 py-2">
                                        <button type="button" @click="clearUserSearch" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Resultados da busca -->
                                    <div class="mt-2 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700" x-show="userSuggestions.length > 0">
                                        <template x-for="user in userSuggestions" :key="user.id">
                                            <div class="p-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                                <span class="text-sm text-gray-800 dark:text-gray-200" x-text="getUserLabel(user)"></span>
                                                <button type="button" @click="toggleSelectedUser(user)" class="px-3 py-1.5 rounded-lg text-xs transition-colors duration-200"
                                                        :class="isUserSelected(user.id) ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-800' : 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800'">
                                                    <i class="fas" :class="isUserSelected(user.id) ? 'fa-minus' : 'fa-plus'"></i>
                                                    <span x-text="isUserSelected(user.id) ? 'Remover' : 'Adicionar'"></span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Usuários selecionados -->
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usuários Selecionados (<span x-text="form.selectedUsers.length"></span>)</label>
                                        <div class="mt-1 flex flex-wrap gap-2" x-show="form.selectedUsers.length > 0">
                                            <template x-for="user in form.selectedUsers" :key="user.id">
                                                <div class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs flex items-center transition-colors duration-200">
                                                    <span x-text="getUserLabel(user)"></span>
                                                    <button type="button" @click="removeSelectedUser(user.id)" class="ml-2 text-blue-500 hover:text-blue-700 transition-colors duration-200">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="form.selectedUsers.length === 0" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Nenhum usuário selecionado
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seleção de secretarias -->
                            <div x-show="form.recipientsType === 'secretariats'" x-transition>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecionar Secretarias</label>
                                <div class="mt-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    <template x-for="secretariat in secretariats" :key="secretariat.id">
                                        <label class="inline-flex items-center p-3 border rounded-lg border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 cursor-pointer">
                                            <input type="checkbox" :value="secretariat.id" x-model="form.selectedSecretariats" class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 rounded">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300" x-text="secretariat.name"></span>
                                        </label>
                                    </template>
                                </div>
                                <div x-show="form.selectedSecretariats.length === 0" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Nenhuma secretaria selecionada
                                </div>
                            </div>

                            <!-- Tipo de entrega -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Entrega</label>
                                <div class="mt-1 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="form.deliveryType" value="group" class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Grupo (uma conversa com todos)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="form.deliveryType" value="individual" class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Individual (conversa com cada um)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagem -->
                        <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Mensagem</label>
                                <div class="mt-1 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="form.messageType" value="text" class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Texto Simples</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" x-model="form.messageType" value="template" class="form-radio h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Template</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Texto simples -->
                            <div x-show="form.messageType === 'text'" x-transition>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem</label>
                                <div class="mt-1">
                                    <textarea id="message" x-model="form.message" rows="4" class="shadow-sm mt-1 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-lg text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200 p-3" placeholder="Digite sua mensagem..."></textarea>
                                </div>
                            </div>

                            <!-- Template -->
                            <div x-show="form.messageType === 'template'" x-transition>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selecionar Template</label>
                                <div class="mt-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template x-for="template in templates" :key="template.id">
                                        <div class="border rounded-lg p-4 cursor-pointer transition-all duration-200 hover:shadow-md"
                                             :class="form.templateId === template.id ? 'ring-2 ring-blue-500 border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                             @click="selectTemplate(template)">
                                            <div class="flex justify-between items-center mb-2">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="template.title"></h4>
                                                <span class="text-xs px-2 py-1 rounded-full" :class="getScopeClass(template.scope)" x-text="getScopeName(template.scope)"></span>
                                            </div>
                                            <!-- Previsão com cores do template -->
                                            <div class="mt-2 rounded-lg p-3 text-xs" :class="template.style?.class || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'">
                                                <div x-text="template.body.length > 100 ? template.body.substring(0, 100) + '...' : template.body"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="templates.length === 0" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum template disponível
                                </div>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex flex-col sm:flex-row justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <a href="{{ route('chat.index') }}" class="inline-flex justify-center py-2.5 px-4 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 order-2 sm:order-1">
                                Cancelar
                            </a>
                            <button type="submit" :disabled="loading || !isFormValid" class="inline-flex justify-center items-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 order-1 sm:order-2">
                                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                                Enviar Mensagens
                            </button>
                        </div>

                        <!-- Alertas -->
                        <div x-show="alert.show" class="rounded-lg p-4 mt-4 transition-all duration-300" :class="alert.type === 'success' ? 'bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800'">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas" :class="alert.type === 'success' ? 'fa-check-circle text-green-400 dark:text-green-300' : 'fa-exclamation-circle text-red-400 dark:text-red-300'"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium" :class="alert.type === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'" x-text="alert.message"></p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <div class="-mx-1.5 -my-1.5">
                                        <button type="button" @click="alert.show = false" class="inline-flex rounded-lg p-1.5 transition-colors duration-200" :class="alert.type === 'success' ? 'bg-green-50 dark:bg-green-900/30 text-green-500 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/50' : 'bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50'">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function broadcastMessages() {
            return {
                secretariats: @json($secretariats),
                templates: @json($templates),
                userRole: {{ $user->role_id }},
                userSecretariatId: {{ $user->secretariat_id ?? 'null' }},

                form: {
                    recipientsType: 'users',
                    selectedUsers: [],
                    selectedSecretariats: [],
                    deliveryType: 'group',
                    messageType: 'text',
                    message: '',
                    templateId: null
                },

                userSearch: '',
                userSuggestions: [],
                searchTimeout: null,
                loading: false,

                alert: {
                    show: false,
                    type: 'success',
                    message: ''
                },

                init() {
                    // Se tiver apenas 1 secretaria disponível, selecionar automaticamente
                    if (this.secretariats.length === 1) {
                        this.form.selectedSecretariats = [this.secretariats[0].id];
                    }
                },

                get isFormValid() {
                    // Validar destinatários
                    if (this.form.recipientsType === 'users' && this.form.selectedUsers.length === 0) {
                        return false;
                    }

                    if (this.form.recipientsType === 'secretariats' && this.form.selectedSecretariats.length === 0) {
                        return false;
                    }

                    // Validar mensagem
                    if (this.form.messageType === 'text' && !this.form.message.trim()) {
                        return false;
                    }

                    if (this.form.messageType === 'template' && !this.form.templateId) {
                        return false;
                    }

                    return true;
                },

                debouncedSearchUsers() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.searchUsers();
                    }, 300);
                },

                searchUsers() {
                    const query = this.userSearch.trim();
                    if (!query) {
                        this.userSuggestions = [];
                        return;
                    }

                    fetch(`/api/chat/broadcast/search-users?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(result => {
                            this.userSuggestions = result.data || [];
                        });
                },

                clearUserSearch() {
                    this.userSearch = '';
                    this.userSuggestions = [];
                },

                getUserLabel(user) {
                    const rolePart = user.role ? ` (${user.role})` : '';
                    return `${user.name}${rolePart}`;
                },

                isUserSelected(userId) {
                    return this.form.selectedUsers.some(u => u.id === userId);
                },

                toggleSelectedUser(user) {
                    if (this.isUserSelected(user.id)) {
                        this.removeSelectedUser(user.id);
                    } else {
                        this.form.selectedUsers.push(user);
                    }
                },

                removeSelectedUser(userId) {
                    this.form.selectedUsers = this.form.selectedUsers.filter(u => u.id !== userId);
                },

                selectTemplate(template) {
                    this.form.templateId = template.id;
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

                sendMessages() {
                    if (!this.isFormValid || this.loading) {
                        return;
                    }

                    this.loading = true;

                    // Montar dados para envio
                    const payload = {
                        recipients_type: this.form.recipientsType,
                        users: this.form.recipientsType === 'users' ? this.form.selectedUsers.map(u => u.id) : [],
                        secretariats: this.form.recipientsType === 'secretariats' ? this.form.selectedSecretariats : [],
                        delivery_type: this.form.deliveryType,
                        message_type: this.form.messageType,
                        message: this.form.messageType === 'text' ? this.form.message : '',
                        template_id: this.form.messageType === 'template' ? this.form.templateId : null
                    };

                    fetch('/api/chat/broadcast/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                this.showAlert('success', result.message);
                                // Limpar formulário
                                this.form.selectedUsers = [];
                                this.form.selectedSecretariats = [];
                                this.form.message = '';
                                this.form.templateId = null;
                            } else {
                                this.showAlert('error', result.message || 'Erro ao enviar mensagens');
                            }
                        })
                        .catch(error => {
                            this.showAlert('error', 'Ocorreu um erro ao processar a requisição');
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                showAlert(type, message) {
                    this.alert = {
                        show: true,
                        type,
                        message
                    };

                    // Auto-fechar alerta após 8 segundos se for sucesso
                    if (type === 'success') {
                        setTimeout(() => {
                            this.alert.show = false;
                        }, 8000);
                    }
                }
            };
        }
    </script>
@endsection
