<?php
namespace App\Http\Controllers;

use App\Models\{User, Secretariat, ChatConversation, ChatMessageTemplate, ChatMessage};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatBroadcastController extends Controller
{
    /**
     * Mostra a interface para envio de mensagens automáticas
     */
    public function index()
    {
        // Apenas administradores (role_id=1) e gestores (role_id=2) podem acessar
        $user = Auth::user();
        if (!in_array($user->role_id, [1, 2])) {
            abort(403, 'Acesso não autorizado');
        }

        // Secretarias que o usuário pode enviar mensagens
        // Admin (role 1) pode ver todas as secretarias
        // Gestor (role 2) pode ver apenas a própria secretaria
        $secretariats = $user->role_id == 1
            ? Secretariat::orderBy('name')->get()
            : Secretariat::where('id', $user->secretariat_id)->get();

        // Templates disponíveis para o usuário
        $templates = ChatMessageTemplate::where(function($q) use ($user) {
            // Templates globais
            $q->where('scope', 'global');

            // Templates da secretaria (se usuário tiver secretaria)
            if ($user->secretariat_id) {
                $q->orWhere(function($q2) use ($user) {
                    $q2->where('scope', 'secretariat')
                       ->where('secretariat_id', $user->secretariat_id);
                });
            }

            // Templates pessoais
            $q->orWhere(function($q2) use ($user) {
                $q2->where('scope', 'personal')
                   ->where('created_by', $user->id);
            });
        })->orderBy('title')->get();

        return view('chat.broadcast', [
            'secretariats' => $secretariats,
            'templates' => $templates,
            'user' => $user
        ]);
    }

    /**
     * Executa o envio de mensagens automáticas
     */
    public function send(Request $request)
    {
        try {
            // Validação básica
            $data = $request->validate([
                'recipients_type' => ['required', 'in:users,secretariats'],
                'users' => ['required_if:recipients_type,users', 'array'],
                'users.*' => ['integer'],
                'secretariats' => ['required_if:recipients_type,secretariats', 'array'],
                'secretariats.*' => ['integer'],
                'delivery_type' => ['required', 'in:individual,group'],
                'message_type' => ['required', 'in:text,template'],
                'template_id' => ['nullable', 'integer'],
                'message' => ['nullable', 'string', 'max:5000'],
            ]);

            // Verificar permissões
            $user = Auth::user();
            if (!in_array($user->role_id, [1, 2])) {
                return response()->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
            }

            // Se for role 2, verificar se está tentando enviar para outra secretaria
            if ($user->role_id == 2) {
                if ($data['recipients_type'] == 'secretariats') {
                    // Verificar se todas as secretarias selecionadas são a do usuário
                    foreach ($data['secretariats'] as $secretariatId) {
                        if ($secretariatId != $user->secretariat_id) {
                            return response()->json(['success' => false, 'message' => 'Não autorizado a enviar para outras secretarias'], 403);
                        }
                    }
                }
            }

            // Obter lista de usuários para envio
            $userIds = [];

            if ($data['recipients_type'] == 'users') {
                // Envio para usuários específicos
                $userIds = $data['users'];

                // Se role 2, filtrar apenas usuários da mesma secretaria
                if ($user->role_id == 2) {
                    $allowedUsers = User::whereIn('id', $userIds)
                        ->where('secretariat_id', $user->secretariat_id)
                        ->pluck('id')->toArray();
                    $userIds = $allowedUsers;
                }
            } else {
                // Envio para secretarias
                $userIds = User::whereIn('secretariat_id', $data['secretariats'])
                    ->where('id', '!=', $user->id) // Não enviar para si mesmo
                    ->pluck('id')
                    ->toArray();
            }

            // Se não houver destinatários, retornar erro
            if (empty($userIds)) {
                return response()->json(['success' => false, 'message' => 'Nenhum destinatário encontrado'], 422);
            }

            // Preparar mensagem
            $messageBody = '';
            $templateId = null;
            $styleClass = null;

            if ($data['message_type'] == 'template' && !empty($data['template_id'])) {
                $template = ChatMessageTemplate::find($data['template_id']);

                if (!$template) {
                    return response()->json(['success' => false, 'message' => 'Template não encontrado'], 404);
                }

                // Verificar se tem acesso ao template
                if ($template->scope == 'secretariat' && $template->secretariat_id != $user->secretariat_id && $user->role_id == 2) {
                    return response()->json(['success' => false, 'message' => 'Template não autorizado'], 403);
                }
                if ($template->scope == 'personal' && $template->created_by != $user->id) {
                    return response()->json(['success' => false, 'message' => 'Template não autorizado'], 403);
                }

                $messageBody = $template->body;
                $templateId = $template->id;
                $styleClass = isset($template->style['class']) ? $template->style['class'] : null;
            } else {
                if (empty($data['message'])) {
                    return response()->json(['success' => false, 'message' => 'Mensagem não pode ser vazia'], 422);
                }
                $messageBody = $data['message'];
            }

            DB::beginTransaction();

            $conversationsCreated = 0;
            $messagesSent = 0;

            if ($data['delivery_type'] == 'group') {
                // Criar um grupo com todos os destinatários
                $conversation = ChatConversation::create([
                    'title' => 'Mensagem automática: ' . substr($messageBody, 0, 30) . '...',
                    'is_group' => true,
                    'created_by' => $user->id,
                ]);

                // Adicionar o remetente
                $conversation->participants()->create([
                    'user_id' => $user->id,
                    'invited_at' => now(),
                    'accepted_at' => now(),
                    'is_admin' => true
                ]);

                // Adicionar todos os destinatários
                foreach ($userIds as $userId) {
                    $conversation->participants()->create([
                        'user_id' => $userId,
                        'invited_at' => now(),
                        'accepted_at' => now()
                    ]);
                }

                // Enviar a mensagem
                $conversation->messages()->create([
                    'user_id' => $user->id,
                    'type' => 'text',
                    'body' => $messageBody,
                    'template_id' => $templateId,
                    'style_class' => $styleClass,
                    'is_broadcast' => true
                ]);

                $conversationsCreated = 1;
                $messagesSent = 1;
            } else {
                // Criar conversa individual com cada destinatário
                foreach ($userIds as $userId) {
                    // Verificar se já existe conversa direta
                    $conversation = ChatConversation::where('is_group', false)
                        ->whereHas('participants', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->whereHas('participants', function($q) use ($userId) {
                            $q->where('user_id', $userId);
                        })
                        ->first();

                    // Se não existe, criar nova
                    if (!$conversation) {
                        $conversation = ChatConversation::create([
                            'is_group' => false,
                            'created_by' => $user->id,
                        ]);

                        // Adicionar participantes
                        $conversation->participants()->create([
                            'user_id' => $user->id,
                            'invited_at' => now(),
                            'accepted_at' => now(),
                            'is_admin' => true
                        ]);

                        $conversation->participants()->create([
                            'user_id' => $userId,
                            'invited_at' => now(),
                            'accepted_at' => now()
                        ]);

                        $conversationsCreated++;
                    }

                    // Enviar a mensagem
                    $conversation->messages()->create([
                        'user_id' => $user->id,
                        'type' => 'text',
                        'body' => $messageBody,
                        'template_id' => $templateId,
                        'style_class' => $styleClass,
                        'is_broadcast' => true
                    ]);

                    $messagesSent++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mensagem enviada com sucesso. Conversas criadas: $conversationsCreated. Mensagens enviadas: $messagesSent."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tratamento específico para erros de validação
            Log::error('Erro de validação no broadcast: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Para qualquer outra exceção
            DB::rollBack();
            Log::error('Erro no broadcast: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca usuários para selecionar destinatários
     */
    public function searchUsers(Request $request)
    {
        try {
            $user = Auth::user();
            if (!in_array($user->role_id, [1, 2])) {
                return response()->json(['data' => []], 403);
            }

            $query = $request->get('q', '');
            $userQuery = User::with('role:id,name')
                            ->where('id', '!=', $user->id);

            // Role 2 só pode ver usuários da própria secretaria
            if ($user->role_id == 2) {
                $userQuery->where('secretariat_id', $user->secretariat_id);
            }

            if (!empty($query)) {
                $userQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                      ->orWhere('email', 'like', "%$query%")
                      ->orWhere('cpf', 'like', "%$query%");
                });
            }

            $users = $userQuery->orderBy('name')
                              ->limit(50)
                              ->get(['id', 'name', 'role_id', 'secretariat_id']);

            return response()->json([
                'data' => $users->map(function($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'role' => $u->role?->name,
                        'secretariat_id' => $u->secretariat_id
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na busca de usuários para broadcast: ' . $e->getMessage());
            return response()->json(['data' => []], 500);
        }
    }
}
