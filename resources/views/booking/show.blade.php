@php
    $q = fn (array $extra = []) => route('booking.show', array_merge(
        ['slug' => $clinic->slug],
        array_filter(['profissional' => $pro?->id, 'data' => $date->toDateString()]),
        $extra
    ));
    $waClinic = $clinic->whatsappUrl('Olá! Gostaria de falar sobre um agendamento na '.$clinic->name.'.');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head', ['themeClinic' => $clinic])
    <title>Agende sua consulta · {{ $clinic->name }}</title>
    <meta name="description" content="Agende sua consulta online na {{ $clinic->name }}.">
</head>
<body>
<div class="bk">
    <header class="bk-head">
        <span class="brand-logo">
            @if($clinic->logoUrl())<img src="{{ $clinic->logoUrl() }}" alt="">@else{{ $clinic->initials() }}@endif
        </span>
        <div style="min-width:0">
            <h1 class="truncate">{{ $clinic->name }}</h1>
            <p class="muted small truncate">
                @if($clinic->address)<x-icon name="map-pin" size="sm"/> {{ $clinic->address }}@else Agendamento online @endif
            </p>
        </div>
        @if($waClinic)
            <a href="{{ $waClinic }}" target="_blank" rel="noopener" class="btn btn-wa ml-auto hide-xs"><x-whatsapp-icon size="16"/> WhatsApp</a>
        @endif
    </header>

    @if($booked)
        @php $when = \Illuminate\Support\Carbon::parse($booked['starts_at']); @endphp
        <section class="card bk-done">
            <div class="bk-done-ico"><x-icon name="check" size="lg"/></div>
            <h2>Consulta agendada!</h2>
            <p class="muted">Obrigado, {{ \Illuminate\Support\Str::of($booked['name'])->explode(' ')->first() }}. Seu horário está reservado.</p>

            <div class="bk-summary">
                <div><span class="muted small">Quando</span><strong>{{ ucfirst($when->translatedFormat('l, d \d\e F')) }} às {{ $when->format('H:i') }}</strong></div>
                <div><span class="muted small">Com</span><strong>{{ $booked['professional'] }}</strong>@if($booked['specialty'])<span class="muted small">{{ $booked['specialty'] }}</span>@endif</div>
                @if($clinic->address)<div><span class="muted small">Onde</span><strong>{{ $clinic->address }}</strong></div>@endif
            </div>

            @if($wa = $clinic->whatsappUrl('Olá! Sou '.$booked['name'].' e acabei de agendar uma consulta com '.$booked['professional'].' para '.$when->translatedFormat('d/m').' às '.$when->format('H:i').'. Gostaria de confirmar.'))
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-wa btn-lg btn-block mt-3"><x-whatsapp-icon size="18"/> Confirmar pelo WhatsApp</a>
            @endif
            <a href="{{ route('booking.show', $clinic->slug) }}" class="btn btn-ghost btn-block mt-2">Fazer outro agendamento</a>
        </section>
    @elseif($professionals->isEmpty())
        <section class="card">
            <div class="empty">
                <div class="ico"><x-icon name="calendar"/></div>
                <h3>Agendamento online indisponível</h3>
                <p>No momento não há horários abertos. Fale com a clínica para marcar sua consulta.</p>
                @if($waClinic)<a href="{{ $waClinic }}" target="_blank" rel="noopener" class="btn btn-wa mt-2"><x-whatsapp-icon size="16"/> Falar pelo WhatsApp</a>@endif
            </div>
        </section>
    @else
        <div class="bk-intro">
            <h2>Agende sua consulta</h2>
            <p class="muted">Escolha o profissional, o dia e o horário. Leva menos de um minuto.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-error"><x-icon name="alert"/><div>{{ $errors->first() }}</div></div>
        @endif

        {{-- 1. Profissional --}}
        <section class="card bk-step">
            <div class="bk-step-title"><span class="bk-n">1</span> Profissional</div>
            <div class="bk-pros">
                @foreach($professionals as $p)
                    <a href="{{ $q(['profissional' => $p->id]) }}#data" @class(['bk-pro', 'active' => $pro?->id === $p->id])>
                        <x-avatar :initials="$p->initials()" :color="$p->color"/>
                        <span style="min-width:0">
                            <span class="strong truncate" style="display:block">{{ $p->name }}</span>
                            <span class="muted small truncate" style="display:block">{{ $p->specialty ?: 'Clínico' }}</span>
                        </span>
                        @if($pro?->id === $p->id)<x-icon name="check-circle" class="bk-check"/>@endif
                    </a>
                @endforeach
            </div>
        </section>

        @if($pro)
            {{-- 2. Data --}}
            <section class="card bk-step" id="data">
                <div class="bk-step-title"><span class="bk-n">2</span> Data <span class="muted small ml-auto">{{ ucfirst($date->translatedFormat('F \d\e Y')) }}</span></div>
                <div class="bk-days">
                    @foreach($days as $d)
                        <a href="{{ $q(['data' => $d->toDateString()]) }}#horarios" @class(['bk-day', 'active' => $d->isSameDay($date)])>
                            <small>{{ $d->isToday() ? 'hoje' : $d->translatedFormat('D') }}</small>
                            <b>{{ $d->format('d') }}</b>
                            <small>{{ $d->translatedFormat('M') }}</small>
                        </a>
                    @endforeach
                </div>
            </section>

            <form method="POST" action="{{ route('booking.store', $clinic->slug) }}" class="stack" style="gap:16px">
                @csrf
                <input type="hidden" name="professional_id" value="{{ $pro->id }}">
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                {{-- 3. Horário --}}
                <section class="card bk-step" id="horarios">
                    <div class="bk-step-title"><span class="bk-n">3</span> Horário <span class="muted small ml-auto">{{ ucfirst($date->translatedFormat('l, d/m')) }}</span></div>
                    @if($slots->isEmpty())
                        <div class="empty" style="padding:28px 12px">
                            <p>Não há horários livres neste dia. Tente outra data.</p>
                        </div>
                    @else
                        <div class="bk-slots">
                            @foreach($slots as $s)
                                <label class="bk-slot">
                                    <input type="radio" name="time" value="{{ $s }}" @checked(old('time') === $s) required>
                                    <span>{{ $s }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if($slots->isNotEmpty())
                    {{-- 4. Dados --}}
                    <section class="card bk-step">
                        <div class="bk-step-title"><span class="bk-n">4</span> Seus dados</div>
                        <div class="form-grid">
                            <x-field name="name" label="Nome completo *" class="col-6">
                                <input id="name" name="name" value="{{ old('name') }}" @class(['input', 'is-invalid' => $errors->has('name')]) autocomplete="name" required>
                            </x-field>
                            <x-field name="phone" label="WhatsApp *" class="col-3">
                                <input id="phone" name="phone" value="{{ old('phone') }}" @class(['input', 'is-invalid' => $errors->has('phone')]) data-mask="phone" inputmode="tel" autocomplete="tel" placeholder="(00) 00000-0000" required>
                            </x-field>
                            <x-field name="email" label="E-mail" class="col-3">
                                <input id="email" name="email" type="email" value="{{ old('email') }}" @class(['input', 'is-invalid' => $errors->has('email')]) autocomplete="email">
                            </x-field>
                            <x-field name="notes" label="Motivo da consulta (opcional)" class="col-6">
                                <textarea id="notes" name="notes" class="textarea" rows="2" maxlength="500">{{ old('notes') }}</textarea>
                            </x-field>
                        </div>
                        <button class="btn btn-primary btn-lg btn-block mt-3"><x-icon name="calendar-plus" size="sm"/> Confirmar agendamento</button>
                        <p class="muted small mt-2" style="text-align:center">Com {{ $pro->name }} · {{ $clinic->slot_minutes }} minutos</p>
                    </section>
                @endif
            </form>
        @endif
    @endif

    <footer class="bk-foot muted small">
        Agendamento online por <a href="{{ route('home') }}" class="strong">{{ config('app.name') }}</a>
    </footer>
</div>

@if($waClinic)
    <a href="{{ $waClinic }}" target="_blank" rel="noopener" class="wa-fab" aria-label="Falar com a clínica pelo WhatsApp"><x-whatsapp-icon size="28"/></a>
@endif
<script src="{{ asset('js/app.js') }}?v=1"></script>
<script>
    (function () {
        var strip = document.querySelector('.bk-days'), active = strip && strip.querySelector('.active');
        if (active) strip.scrollLeft = active.offsetLeft - strip.offsetLeft - (strip.clientWidth - active.offsetWidth) / 2;
    })();
</script>
</body>
</html>
