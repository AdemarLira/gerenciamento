<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Equipe da clínica (usuários com acesso ao sistema). Somente administradores. */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('clinic_id', $request->user()->clinic_id)->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => 'reception'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['clinic_id'] = $request->user()->clinic_id;
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Usuário criado.');
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeClinic($request, $user);

        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeClinic($request, $user);
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Você não pode remover seu próprio acesso de administrador.']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeClinic($request, $user);
        abort_if($user->is($request->user()), 422, 'Você não pode excluir o próprio usuário.');
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário removido.');
    }

    private function authorizeClinic(Request $request, User $user): void
    {
        abort_unless($user->clinic_id === $request->user()->clinic_id, 404);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ], [], ['name' => 'nome', 'role' => 'perfil', 'password' => 'senha']);
    }
}
