<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

/** Cadastro de uma nova clínica (novo cliente) + usuário administrador. */
class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'clinic_name' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'clinic_name' => 'nome da clínica',
            'name' => 'seu nome',
            'password' => 'senha',
        ]);

        $user = DB::transaction(function () use ($data) {
            $clinic = Clinic::create(['name' => $data['clinic_name'], 'email' => $data['email']]);

            return User::create([
                'clinic_id' => $clinic->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'admin',
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('settings.edit')
            ->with('success', 'Clínica criada! Personalize o logo e as cores para começar.');
    }
}
