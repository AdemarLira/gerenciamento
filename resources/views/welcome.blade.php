<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head')
    <title>{{ config('app.name') }} — Gestão de clínicas e consultórios</title>
    <meta name="description" content="Agenda, pacientes e equipe da sua clínica em um só lugar, com a sua marca.">
</head>
<body>
<div class="landing">
    <nav class="landing-nav">
        <span class="brand-logo" style="width:36px;height:36px"><x-icon name="activity" size="sm"/></span>
        <strong style="font-size:16px">{{ config('app.name') }}</strong>
        <div class="ml-auto row">
            <a href="{{ route('login') }}" class="btn btn-ghost">Entrar</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Começar agora</a>
        </div>
    </nav>

    <header class="hero">
        <span class="pill"><x-icon name="sparkles" size="sm"/> Feito para clínicas e consultórios</span>
        <h1>Sua clínica no controle,<br>com a <em>sua</em> identidade.</h1>
        <p>Agenda por profissional, ficha completa dos pacientes e indicadores do dia — tudo com o logo e as cores da sua marca.</p>
        <div class="cta">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Cadastrar minha clínica <x-icon name="arrow-right" size="sm"/></a>
            <a href="{{ route('login') }}" class="btn btn-lg">Já sou cliente</a>
        </div>
    </header>

    <section class="features">
        <div class="card feature">
            <div class="ico"><x-icon name="calendar"/></div>
            <h3>Agenda inteligente</h3>
            <p>Visão diária e semanal por profissional, com bloqueio automático de horários em conflito.</p>
        </div>
        <div class="card feature">
            <div class="ico"><x-icon name="users"/></div>
            <h3>Pacientes organizados</h3>
            <p>Cadastro com CPF, convênio e observações, além do histórico completo de atendimentos.</p>
        </div>
        <div class="card feature">
            <div class="ico"><x-icon name="palette"/></div>
            <h3>Com a sua marca</h3>
            <p>Logo, cor principal e link de acesso próprio para a equipe de cada clínica.</p>
        </div>
    </section>

    <footer class="footer">© {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</footer>
</div>
</body>
</html>
