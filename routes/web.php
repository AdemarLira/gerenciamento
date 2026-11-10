<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteEditorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Site público da clínica (landing page personalizável).
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/s/{slug}', [SiteController::class, 'show'])->name('site.show');
Route::post('/s/{slug}/agendar', [SiteController::class, 'request'])->middleware('throttle:6,1')->name('site.request');

// Página de vendas do sistema (para oferecer a outros profissionais).
Route::view('/plataforma', 'welcome')->name('platform');

// Agendamento online com escolha de horário (opcional).
Route::get('/agendar/{slug}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/agendar/{slug}', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');

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

    // Pedidos de agendamento vindos do site.
    Route::get('/solicitacoes', [BookingRequestController::class, 'index'])->name('requests.index');
    Route::patch('/solicitacoes/{bookingRequest}', [BookingRequestController::class, 'update'])->name('requests.update');
    Route::post('/solicitacoes/{bookingRequest}/agendar', [BookingRequestController::class, 'schedule'])->name('requests.schedule');

    Route::resource('profissionais', ProfessionalController::class)
        ->except('show')
        ->parameters(['profissionais' => 'professional'])
        ->names('professionals')
        ->middleware('role:admin');

    Route::middleware('role:admin')->group(function () {
        Route::get('/personalizar', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/personalizar', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/site', [SiteEditorController::class, 'edit'])->name('site.edit');
        Route::put('/site', [SiteEditorController::class, 'update'])->name('site.update');
        Route::post('/site/restaurar', [SiteEditorController::class, 'reset'])->name('site.reset');

        Route::resource('equipe', UserController::class)
            ->except('show')
            ->parameters(['equipe' => 'user'])
            ->names('users');
    });
});
