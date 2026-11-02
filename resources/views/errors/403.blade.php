<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head', ['themeClinic' => auth()->user()?->clinic])
    <title>Acesso restrito</title>
</head>
<body>
<div class="landing" style="display:grid;place-items:center;text-align:center;padding:24px">
    <div>
        <div style="font-family:var(--font-display);font-size:96px;line-height:1;color:var(--accent)">403</div>
        <h1 style="font-size:24px;margin-top:12px">Acesso restrito</h1>
        <p class="muted mt-1">Você não tem permissão para acessar esta área.</p>
        <a href="{{ url('/') }}" class="btn btn-primary mt-3">Voltar ao início</a>
    </div>
</div>
</body>
</html>
