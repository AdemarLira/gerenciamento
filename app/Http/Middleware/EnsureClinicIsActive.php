<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Bloqueia o acesso se a clínica estiver desativada (ex.: mensalidade em atraso). */
class EnsureClinicIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = $request->user()?->clinic;

        if ($clinic && ! $clinic->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'O acesso desta clínica está suspenso. Entre em contato com o suporte.']);
        }

        return $next($request);
    }
}
