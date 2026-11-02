<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Tela de login. Em /c/{slug} a tela mostra o logo e as cores da clínica
     * (link que você entrega para cada cliente).
     */
    public function show(?string $slug = null)
    {
        $brand = $slug ? Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail() : null;

        return view('auth.login', ['brand' => $brand]);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::lower($request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Muitas tentativas. Tente novamente em '.RateLimiter::availableIn($key).' segundos.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        $slug = $request->user()?->clinic?->slug;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $slug ? redirect()->route('login.clinic', $slug) : redirect()->route('login');
    }
}
