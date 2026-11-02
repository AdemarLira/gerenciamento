<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head', ['themeClinic' => $brand ?? null])
    <title>@yield('title') · {{ ($brand ?? null)?->name ?? config('app.name') }}</title>
</head>
<body>
<div class="auth">
    <section class="auth-art">
        <div class="row" style="gap:12px">
            <span class="brand-logo">
                @if(($brand ?? null)?->logoUrl())
                    <img src="{{ $brand->logoUrl() }}" alt="">
                @elseif($brand ?? null)
                    {{ $brand->initials() }}
                @else
                    <x-icon name="activity"/>
                @endif
            </span>
            <span class="brand-name">{{ ($brand ?? null)?->name ?? config('app.name') }}</span>
        </div>

        <div>
            @if($brand ?? null)
                <h1>{{ $brand->welcome_message ?: 'Bem-vindo de volta à sua clínica.' }}</h1>
                <p class="lead">Acesse a agenda, os pacientes e o painel da {{ $brand->name }}.</p>
            @else
                <h1>A agenda da sua clínica, <em>organizada</em> e com a sua cara.</h1>
                <p class="lead">Consultas, pacientes e equipe em um só lugar — com o logo e as cores da sua marca.</p>
            @endif
        </div>

        <div class="auth-feats">
            <div class="auth-feat"><x-icon name="calendar"/> Agenda por profissional, sem conflito de horários</div>
            <div class="auth-feat"><x-icon name="users"/> Ficha do paciente com histórico de consultas</div>
            <div class="auth-feat"><x-icon name="palette"/> Personalização completa da marca</div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-box">
            @yield('content')
        </div>
    </section>
</div>
<script src="{{ asset('js/app.js') }}?v=1"></script>
</body>
</html>
