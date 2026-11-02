<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::get('/c/{slug}', [LoginController::class, 'show'])->name('login.clinic');   // login com a marca da clínica
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/cadastro', [RegisterController::class, 'show'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'clinic.active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/painel', DashboardController::class)->name('dashboard');

    Route::resource('pacientes', PatientController::class)
        ->parameters(['pacientes' => 'patient'])
        ->names('patients');

    Route::get('/agenda', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/agenda/nova', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/agenda', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/agenda/{appointment}/editar', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/agenda/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/agenda/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::delete('/agenda/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    Route::resource('profissionais', ProfessionalController::class)
        ->except('show')
        ->parameters(['profissionais' => 'professional'])
        ->names('professionals')
        ->middleware('role:admin');

    Route::middleware('role:admin')->group(function () {
        Route::get('/personalizar', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/personalizar', [SettingsController::class, 'update'])->name('settings.update');

        Route::resource('equipe', UserController::class)
            ->except('show')
            ->parameters(['equipe' => 'user'])
            ->names('users');
    });
});
