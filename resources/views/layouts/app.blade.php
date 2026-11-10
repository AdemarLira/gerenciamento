<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head', ['themeClinic' => $currentClinic])
    <title>@yield('title', 'Painel') · {{ $currentClinic->name }}</title>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-logo">
                @if($currentClinic->logoUrl())
                    <img src="{{ $currentClinic->logoUrl() }}" alt="">
                @else
                    {{ $currentClinic->initials() }}
                @endif
            </span>
            <span style="min-width:0">
                <div class="brand-name truncate">{{ $currentClinic->name }}</div>
                <div class="brand-sub">Gestão da clínica</div>
            </span>
        </a>

        <div class="nav-label">Principal</div>
        <a href="{{ route('dashboard') }}" @class(['nav-link', 'active' => request()->routeIs('dashboard')])>
            <x-icon name="home"/> Painel
        </a>
        <a href="{{ route('appointments.index') }}" @class(['nav-link', 'active' => request()->routeIs('appointments.*')])>
            <x-icon name="calendar"/> Agenda
        </a>
        <a href="{{ route('patients.index') }}" @class(['nav-link', 'active' => request()->routeIs('patients.*')])>
            <x-icon name="users"/> Pacientes
        </a>
        @php $newRequests = \App\Models\BookingRequest::where('status', 'new')->count(); @endphp
        <a href="{{ route('requests.index') }}" @class(['nav-link', 'active' => request()->routeIs('requests.*')])>
            <x-icon name="message"/> Solicitações
            @if($newRequests)<span class="nav-badge">{{ $newRequests }}</span>@endif
        </a>
        <a href="{{ $currentClinic->siteUrl() }}" target="_blank" rel="noopener" class="nav-link">
            <x-icon name="eye"/> Ver meu site <x-icon name="external" size="sm" class="ml-auto muted"/>
        </a>

        @if(auth()->user()->isAdmin())
            <div class="nav-label">Administração</div>
            <a href="{{ route('site.edit') }}" @class(['nav-link', 'active' => request()->routeIs('site.edit')])>
                <x-icon name="sparkles"/> Editar site
            </a>
            <a href="{{ route('professionals.index') }}" @class(['nav-link', 'active' => request()->routeIs('professionals.*')])>
                <x-icon name="stethoscope"/> Profissionais
            </a>
            <a href="{{ route('users.index') }}" @class(['nav-link', 'active' => request()->routeIs('users.*')])>
                <x-icon name="shield"/> Equipe
            </a>
            <a href="{{ route('settings.edit') }}" @class(['nav-link', 'active' => request()->routeIs('settings.*')])>
                <x-icon name="palette"/> Personalizar
            </a>
        @endif

        <div class="sidebar-footer">
            @if($support = \App\Support\WhatsApp::sales('Olá! Sou da '.$currentClinic->name.' e preciso de ajuda com o sistema.'))
                <a href="{{ $support }}" target="_blank" rel="noopener" class="nav-link mb-2"><x-whatsapp-icon size="18" class="btn-wa-soft"/> Suporte no WhatsApp</a>
            @endif
            <div class="user-chip">
                <x-avatar :initials="auth()->user()->initials()" size="sm"/>
                <div class="meta">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="role">{{ auth()->user()->roleLabel() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm btn-icon" title="Sair" aria-label="Sair"><x-icon name="log-out" size="sm"/></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="btn btn-ghost btn-icon menu-toggle" data-toggle-nav aria-label="Abrir menu"><x-icon name="menu"/></button>
            <div class="title">
                <h1>@yield('heading')</h1>
                @hasSection('subheading')<p>@yield('subheading')</p>@endif
            </div>
            <div class="actions">
                @yield('actions')
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success"><x-icon name="check-circle"/> <div>{{ session('success') }}</div></div>
            @endif
            @if($errors->any())
                <div class="alert alert-error"><x-icon name="alert"/>
                    <div>{{ $errors->count() > 1 ? 'Verifique os campos destacados abaixo.' : $errors->first() }}</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/app.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
