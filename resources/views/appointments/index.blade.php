@extends('layouts.app')
@section('title', 'Agenda')
@section('heading', 'Agenda')
@section('subheading', $view === 'week'
    ? 'Semana de '.$from->format('d/m').' a '.$to->format('d/m')
    : ucfirst($date->translatedFormat('l, d \d\e F \d\e Y')))
@section('actions')
    <a href="{{ route('appointments.create', ['date' => $date->toDateString(), 'professional' => $selectedPro]) }}" class="btn btn-primary"><x-icon name="plus" size="sm"/> <span class="btn-label">Nova consulta</span></a>
@endsection

@php
    $step = $view === 'week' ? 'week' : 'day';
    $prev = $view === 'week' ? $date->copy()->subWeek() : $date->copy()->subDay();
    $next = $view === 'week' ? $date->copy()->addWeek() : $date->copy()->addDay();
    $q = fn ($extra) => array_filter(array_merge(['view' => $view, 'date' => $date->toDateString(), 'professional' => $selectedPro], $extra));

    // Colunas: profissionais (dia) ou dias (semana)
    if ($view === 'week') {
        $cols = collect(range(0, 6))->map(fn ($i) => $from->copy()->addDays($i))
            ->reject(fn ($d) => $d->isSunday() && $appointments->every(fn ($a) => ! $a->starts_at->isSameDay($d)))
            ->values();
    } else {
        $cols = $columns;
    }
    $hourH = 96;

    // Distribui consultas sobrepostas lado a lado (faixas) dentro da coluna.
    $lanes = function ($items) {
        $out = []; $cluster = []; $clusterEnd = null; $laneEnds = [];
        $flush = function () use (&$cluster, &$laneEnds, &$out) {
            $n = max(1, count($laneEnds));
            foreach ($cluster as $id => $lane) { $out[$id] = [$lane, $n]; }
            $cluster = []; $laneEnds = [];
        };
        foreach ($items->sortBy('starts_at') as $a) {
            if ($clusterEnd && $a->starts_at->gte($clusterEnd)) { $flush(); $clusterEnd = null; }
            $lane = null;
            foreach ($laneEnds as $i => $end) { if ($a->starts_at->gte($end)) { $lane = $i; break; } }
            if ($lane === null) { $lane = count($laneEnds); }
            $laneEnds[$lane] = $a->ends_at;
            $cluster[$a->id] = $lane;
            $clusterEnd = $clusterEnd && $clusterEnd->gt($a->ends_at) ? $clusterEnd : $a->ends_at;
        }
        $flush();
        return $out;
    };
    $pos = function ($a, $lane = 0, $n = 1) use ($openHour) {
        $top = (($a->starts_at->hour - $openHour) * 60 + $a->starts_at->minute) / 60;
        $h = max(0.34, $a->durationMinutes() / 60);
        $w = 100 / $n; $l = $w * $lane;
        return "top: calc(var(--hour-h) * {$top} + 2px); height: calc(var(--hour-h) * {$h} - 4px); left: calc({$l}% + 4px); width: calc({$w}% - 8px); right: auto;";
    };
@endphp

@section('content')
<div class="toolbar">
    <div class="segmented">
        <a href="{{ route('appointments.index', $q(['view' => 'day'])) }}" @class(['active' => $view === 'day'])>Dia</a>
        <a href="{{ route('appointments.index', $q(['view' => 'week'])) }}" @class(['active' => $view === 'week'])>Semana</a>
    </div>
    <div class="row" style="gap:4px">
        <a href="{{ route('appointments.index', $q(['date' => $prev->toDateString()])) }}" class="btn btn-icon btn-ghost" aria-label="Anterior"><x-icon name="chevron-left"/></a>
        <a href="{{ route('appointments.index', $q(['date' => today()->toDateString()])) }}" class="btn btn-sm">Hoje</a>
        <a href="{{ route('appointments.index', $q(['date' => $next->toDateString()])) }}" class="btn btn-icon btn-ghost" aria-label="Próximo"><x-icon name="chevron-right"/></a>
    </div>
    <form method="GET" class="row" style="gap:8px">
        <input type="hidden" name="view" value="{{ $view }}">
        <input type="hidden" name="professional" value="{{ $selectedPro }}">
        <input type="date" name="date" value="{{ $date->toDateString() }}" class="input" style="height:38px;width:auto" onchange="this.form.submit()">
    </form>
    <form method="GET" class="ml-auto">
        <input type="hidden" name="view" value="{{ $view }}">
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        <select name="professional" class="select" style="height:38px;min-width:220px" onchange="this.form.submit()">
            <option value="">Todos os profissionais</option>
            @foreach($professionals as $p)
                <option value="{{ $p->id }}" @selected($selectedPro === $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($professionals->isEmpty())
    <div class="card">
        <div class="empty">
            <div class="ico"><x-icon name="stethoscope"/></div>
            <h3>Cadastre um profissional para começar</h3>
            <p>A agenda é organizada por profissional.</p>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('professionals.create') }}" class="btn btn-primary mt-2"><x-icon name="plus" size="sm"/> Cadastrar profissional</a>
            @endif
        </div>
    </div>
@else
<div class="card" style="overflow:hidden">
    <div class="calendar" style="--cols: {{ max(1, $cols->count()) }}; --hour-h: {{ $hourH }}px">
        <div class="cal-head">
            <div></div>
            @foreach($cols as $col)
                @if($view === 'week')
                    <div @class(['is-today' => $col->isToday()])>
                        <span class="day-num">{{ $col->format('d') }}</span>
                        <span>
                            <div class="strong">{{ ucfirst($col->translatedFormat('D')) }}</div>
                            <div class="muted small">{{ $appointments->filter(fn ($a) => $a->starts_at->isSameDay($col))->whereNotIn('status', ['canceled'])->count() }} consultas</div>
                        </span>
                    </div>
                @else
                    <div>
                        <x-avatar :initials="$col->initials()" :color="$col->color" size="sm"/>
                        <span style="min-width:0">
                            <div class="strong truncate">{{ $col->name }}</div>
                            <div class="muted small truncate">{{ $col->specialty }}</div>
                        </span>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="cal-body">
            <div class="cal-hours">
                @foreach($hours as $h)<div>{{ sprintf('%02d:00', $h) }}</div>@endforeach
            </div>

            @foreach($cols as $col)
                @php
                    $colDate = $view === 'week' ? $col : $date;
                    $items = $view === 'week'
                        ? $appointments->filter(fn ($a) => $a->starts_at->isSameDay($col))
                        : $appointments->where('professional_id', $col->id);
                @endphp
                <div @class(['cal-col', 'is-today' => $colDate->isToday()])>
                    @foreach($hours as $h)
                        @foreach([0, 30] as $m)
                            <a class="cal-slot" href="{{ route('appointments.create', array_filter([
                                'date' => $colDate->toDateString(),
                                'time' => sprintf('%02d:%02d', $h, $m),
                                'professional' => $view === 'week' ? $selectedPro : $col->id,
                            ])) }}" aria-label="Agendar às {{ sprintf('%02d:%02d', $h, $m) }}"></a>
                        @endforeach
                    @endforeach

                    @if($colDate->isToday() && now()->hour >= $openHour && now()->hour <= end($hours))
                        <div class="now-line" style="top: calc(var(--hour-h) * {{ ((now()->hour - $openHour) * 60 + now()->minute) / 60 }})"></div>
                    @endif

                    @php $layout = $lanes($items); @endphp
                    @foreach($items as $a)
                        <a href="{{ route('appointments.edit', $a) }}"
                           class="event is-{{ $a->status }}"
                           style="--c: {{ $a->professional->color }}; {{ $pos($a, ...($layout[$a->id] ?? [0, 1])) }}"
                           title="{{ $a->patient->name }} — {{ $a->statusLabel() }}">
                            <div class="ev-name truncate">@if($a->status === 'completed')<x-icon name="check" size="sm" style="width:12px;height:12px;vertical-align:-2px;color:var(--success)"/> @endif{{ $a->patient->name }}</div>
                            <div class="ev-time truncate">{{ $a->starts_at->format('H:i') }}–{{ $a->ends_at->format('H:i') }} · {{ $view === 'week' ? $a->professional->name : $a->statusLabel() }}</div>
                            @if($a->durationMinutes() >= 45)
                                <div class="muted truncate">{{ $a->type ?? 'Consulta' }}</div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row wrap mt-2 small muted" style="gap:16px">
    @foreach($professionals as $p)
        <span class="row" style="gap:6px"><span class="pro-dot" style="background: {{ $p->color }}"></span>{{ $p->name }}</span>
    @endforeach
    <span class="ml-auto hide-sm">Clique em um horário vazio para agendar.</span>
</div>
@endif
@endsection
