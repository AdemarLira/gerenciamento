<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('pt_BR');
        Paginator::defaultView('partials.pagination');
        Route::resourceVerbs(['create' => 'novo', 'edit' => 'editar']);

        // Disponibiliza a clínica do usuário logado em todas as views.
        View::composer('*', function ($view) {
            if (Auth::hasUser() && ! array_key_exists('currentClinic', $view->getData())) {
                $view->with('currentClinic', Auth::user()->clinic);
            }
        });
    }
}
