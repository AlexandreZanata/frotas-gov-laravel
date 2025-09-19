<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // Importe o Model Role
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Importe para validação
use App\Models\UserDataBackup;
use Illuminate\Support\Facades\DB;
use TCPDF;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carrega usuários com o relacionamento 'role' para evitar N+1 queries
        $users = User::with('role')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all(); // Busca todos os perfis
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|unique:users,cpf',
            'role_id' => 'required|integer|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $validatedData['password'] = Hash::make('Frotas@govSorriso');

        $user = User::create($validatedData);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'table_name' => 'users',
            'record_id' => $user->id,
            'new_value' => json_encode($user->toArray()),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $oldData = $user->getOriginal();

        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'cpf' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|integer|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($validatedData);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'table_name' => 'users',
            'record_id' => $user->id,
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($user->getChanges()),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     * @param User $user
     * @param $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        // Log para iniciar o rastreamento
        Log::info("Iniciando processo de exclusão para o usuário: {$user->name} (ID: {$user->id})");

        if ($user->id === Auth::id()) {
            Log::warning("Tentativa de autoexclusão bloqueada para o usuário ID: " . Auth::id());
            return back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        try {
            DB::beginTransaction();

            if ($request->input('backup') === 'true') {
                Log::info("Backup solicitado para o usuário ID: {$user->id}.");
                $this->backupAndStoreUserData($user);
            }

            Log::info("Tentando deletar o usuário ID: {$user->id}.");
            $user->delete(); // O evento 'deleting' no Model User será acionado aqui.

            DB::commit();
            Log::info("Usuário ID: {$user->id} excluído com sucesso.");

            return redirect()->route('admin.users.index')->with('success', 'Usuário e todos os seus dados foram excluídos com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log detalhado do erro
            Log::error("ERRO ao excluir o usuário ID: {$user->id}. Mensagem: " . $e->getMessage() . " No arquivo: " . $e->getFile() . " Linha: " . $e->getLine());

            // **A MUDANÇA MAIS IMPORTANTE ESTÁ AQUI**
            // Retorna para a página anterior com a mensagem de erro detalhada para o admin
            return back()->with('error', 'Falha na exclusão: ' . $e->getMessage());
        }
    }



    /**
     * Reset user's password to default.
     */
    public function sendPasswordResetLink(Request $request, User $user)
    {
        // Usa a funcionalidade nativa do Laravel para enviar o link
        $status = Password::sendResetLink($user->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            // --- Auditoria ---
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'password_reset_link_sent',
                'table_name' => 'users',
                'record_id' => $user->id,
                'new_value' => json_encode(['email' => $user->email]),
            ]);
            return back()->with('success', 'Link para redefinição de senha enviado com sucesso.');
        }

        return back()->with('error', 'Não foi possível enviar o link. Tente novamente.');
    }

    /**
     * Mostra o histórico de alterações de um usuário.
     */
    public function history(User $user)
    {
        $logs = AuditLog::where('table_name', 'users')
            ->where('record_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('admin.users.history', compact('user', 'logs'));
    }

    private function backupAndStoreUserData(User $user)
    {
        Log::info("Dentro de backupAndStoreUserData para o usuário ID: {$user->id}.");

        // 1. Coletar dados
        $relations = ['runs', 'checklists', 'fuelings', 'vehicleTransfers'];
        $dataToBackup = ['user_details' => $user->toArray()];

        foreach ($relations as $relation) {
            // Verifica se a relação existe antes de chamá-la
            if (method_exists($user, $relation)) {
                $relatedData = $user->{$relation}()->get()->toArray();
                Log::info("Coletando relação '{$relation}': " . count($relatedData) . " registros encontrados.");
                if(!empty($relatedData)) {
                    $dataToBackup[$relation] = $relatedData;
                }
            } else {
                Log::warning("A relação '{$relation}' não existe no Model User.");
            }
        }

        // --- PONTO DE DEBUG ---
        // Descomente a linha abaixo para parar a execução e ver os dados que foram coletados.
        // Se o script parar aqui, significa que a coleta de dados funcionou.
        // dd($dataToBackup);

        // 2. Gerar PDF
        Log::info("Iniciando geração do PDF.");
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // ... (configurações do PDF) ...
        $pdf->AddPage();
        $html = "<h1>Backup de Dados - {$user->name}</h1><pre>" . json_encode($dataToBackup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdfContent = $pdf->Output('backup.pdf', 'S');
        Log::info("PDF gerado com sucesso.");

        // 3. Salvar no Banco
        UserDataBackup::create([
            'user_id' => Auth::id(),
            'deleted_user_name' => $user->name,
            'deleted_user_email' => $user->email,
            'report_summary' => 'Backup gerado em ' . now(),
            'pdf_content' => $pdfContent
        ]);
        Log::info("Backup salvo no banco de dados.");
    }
}
