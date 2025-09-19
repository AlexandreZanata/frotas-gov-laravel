<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Secretariat; // Garanta que esta linha está presente
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View; // Garanta que esta linha está presente

class RegisteredUserController extends Controller
{
    /**
     * Mostra a view de registro.
     */
    public function create(): View
    {
        // CORREÇÃO: Busca as secretarias e envia para a view
        $secretariats = Secretariat::orderBy('name')->get();

        return view('auth.register', ['secretariats' => $secretariats]);
    }

    /**
     * Lida com a requisição de registro.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'cpf' => ['required', 'string', 'max:17', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'secretariat_id' => ['required', 'exists:secretariats,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'cpf' => $request->cpf,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'secretariat_id' => $request->secretariat_id,
            'role_id' => 4,
            'status' => 'inactive',
        ]);

        event(new Registered($user));

        $statusMessage = 'Você foi cadastrado com sucesso! Aguarde o administrador ativar sua conta.';

        return redirect()->route('login')->with('status', $statusMessage);
    }
}
