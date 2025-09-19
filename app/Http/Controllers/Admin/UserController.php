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
     */
    public function destroy(User $user)
    {
        // Impede que o admin se auto-exclua
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $oldData = $user->toArray();
        $user->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'table_name' => 'users',
            'record_id' => $user->id,
            'old_value' => json_encode($oldData),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso.');
    }

    /**
     * Reset user's password to default.
     */
    public function resetPassword(Request $request, User $user)
    {
        $defaultPassword = 'Frotas@govSorriso';
        $oldPasswordHash = $user->password;

        $user->password = Hash::make($defaultPassword);
        $user->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'password_reset',
            'table_name' => 'users',
            'record_id' => $user->id,
            'old_value' => json_encode(['password' => $oldPasswordHash]),
            'new_value' => json_encode(['password' => $user->password]),
        ]);

        return back()->with('success', 'Senha do usuário resetada com sucesso.');
    }
}
