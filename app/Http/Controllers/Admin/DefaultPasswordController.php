<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DefaultPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefaultPasswordController extends Controller
{
    public function index()
    {
        $passwords = DefaultPassword::latest()->paginate(10);
        return view('admin.default-passwords.index', compact('passwords'));
    }

    public function create()
    {
        return view('admin.default-passwords.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'password_plain' => 'required|string|min:6',
        ]);

        DefaultPassword::create([
            'name' => $validated['name'],
            'password_plain' => $validated['password_plain'], // O Model vai cuidar do Hash
            'is_active' => $request->has('is_active'),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.default-passwords.index')->with('success', 'Senha padrão criada com sucesso.');
    }

    public function edit(DefaultPassword $defaultPassword)
    {
        return view('admin.default-passwords.edit', compact('defaultPassword'));
    }

    public function update(Request $request, DefaultPassword $defaultPassword)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'password_plain' => 'nullable|string|min:6', // Senha é opcional na atualização
        ]);

        $defaultPassword->name = $validated['name'];
        if (!empty($validated['password_plain'])) {
            $defaultPassword->password_plain = $validated['password_plain'];
        }
        $defaultPassword->is_active = $request->has('is_active');
        $defaultPassword->save();

        return redirect()->route('admin.default-passwords.index')->with('success', 'Senha padrão atualizada com sucesso.');
    }

    public function destroy(DefaultPassword $defaultPassword)
    {
        $defaultPassword->delete();
        return redirect()->route('admin.default-passwords.index')->with('success', 'Senha padrão excluída com sucesso.');
    }
}
