@extends('layouts.app')
@section('title', 'Painel')
@section('heading')
    @php $h = now()->hour; @endphp
    {{ $h < 12 ? 'Bom dia' : ($h < 18 ? 'Boa tarde' : 'Boa noite') }}, {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first() }}
@endsection
@section('subheading', ucfirst(now()->translatedFormat('l, d \d\e F')))
@section('actions')
    <a href="{{ route('patients.create') }}" class="btn hide-sm"><x-icon name="user-plus" size="sm"/> <span class="btn-label">Paciente</span></a>
    <a href="{{ route('appointments.create') }}" class="btn btn-primary"><x-icon name="plus" size="sm"/> <span class="btn-label">Nova consulta</span></a>
@endsection

@section('content')
<div class="grid grid-4 mb-3">
    <div class="card stat highlight">
        <div class="label"><span class="ico"><x-icon name="calendar" size="sm"/></span> Consultas hoje</div>
        <div class="value">{{ $stats['today'] }}</div>
        <div class="hint">{{ $stats['today_confirmed'] }} confirmadas</div>
    </div>
    <div class="card stat">
        <div class="label"><span class="ico"><x-icon name="users" size="sm"/></span> Pacientes</div>
        <div class="value">{{ number_format($stats['patients'], 0, ',', '.') }}</div>
        <div class="hint">+{{ $stats['patients_month'] }} este mês</div>
    </div>
    <div class="card stat">
        <div class="label"><span class="ico"><x-icon name="trending-up" size="sm"/></span> Comparecimento</div>
        <div class="value">{{ $stats['attendance_rate'] !== null ? $stats['attendance_rate'].'%' : '—' }}</div>
        <div class="hint">consultas realizadas no mês</div>
    </div>
    <div class="card stat">
        <div class="label"><span class="ico"><x-icon name="wallet" size="sm"/></span> Faturado no mês</div>
        <div class="value">R$ {{ number_format($stats['revenue_month'], 0, ',', '.') }}</div>
        <div class="hint">consultas concluídas</div>
    </div>
</div>

<div class="grid grid-main">
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Agenda de hoje</h2>
                <div class="sub">{{ $todayAppointments->count() }} {{ \Illuminate\Support\Str::plural('agendamento', $todayAppointments->count()) }}</div>
            </div>
            <div class="right"><a href="{{ route('appointments.index') }}" class="btn btn-sm btn-ghost">Ver agenda <x-icon name="arrow-right" size="sm"/></a></div>
        </div>

        @if($todayAppointments->isNotEmpty())
        <div class="timeline">
        @foreach($todayAppointments as $a)
                <div @class(['tl-item', 'is-past' => $a->ends_at->isPast()])>
                    <div class="tl-time">{{ $a->starts_at->format('H:i') }}<small>{{ $a->durationMinutes() }} min</small></div>
                    <a href="{{ route('patients.show', $a->patient_id) }}" class="tl-body">
                        <x-avatar :initials="$a->patient->initials()"/>
                        <div style="min-width:0">
                            <div class="strong truncate">{{ $a->patient->name }}</div>
                            <div class="muted small row" style="gap:6px">
                                <span class="pro-dot" style="background: {{ $a->professional->color }}"></span>
                                <span class="truncate">{{ $a->professional->name }} · {{ $a->type ?? 'Consulta' }}</span>
                            </div>
                        </div>
                    </a>
                    @include('appointments._status_menu', ['appointment' => $a])
                </div>
        @endforeach
        </div>
        @else
            <div class="empty">
                <div class="ico"><x-icon name="calendar"/></div>
                <h3>Nenhuma consulta hoje</h3>
                <p>Aproveite para organizar a semana.</p>
                <a href="{{ route('appointments.create') }}" class="btn btn-primary mt-2"><x-icon name="plus" size="sm"/> Agendar consulta</a>
            </div>
        @endif
    </div>

    <div class="stack">
        @if($nextAppointment)
            <div class="card next-card">
                <div class="muted small strong" style="text-transform:uppercase;letter-spacing:.08em">Próximo atendimento</div>
                <div class="when mt-2">{{ $nextAppointment->starts_at->format('H:i') }}</div>
                <div class="muted small mt-1">{{ $nextAppointment->starts_at->diffForHumans() }}</div>
                <div class="row mt-3">
                    <x-avatar :initials="$nextAppointment->patient->initials()"/>
                    <div style="min-width:0">
                        <div class="strong truncate">{{ $nextAppointment->patient->name }}</div>
                        <div class="muted small truncate">{{ $nextAppointment->professional->name }}</div>
                    </div>
                </div>
                @if($wa = $nextAppointment->patient->whatsappUrl())
                    <a href="{{ $wa }}?text={{ urlencode('Olá '.$nextAppointment->patient->name.', confirmando sua consulta hoje às '.$nextAppointment->starts_at->format('H:i').' na '.$currentClinic->name.'.') }}" target="_blank" rel="noopener" class="btn btn-sm btn-block mt-3">
                        <x-icon name="message" size="sm"/> Lembrar pelo WhatsApp
                    </a>
                @endif
            </div>
        @endif

        <div class="card">
            <div class="card-header"><h2>Últimos 7 dias</h2><span class="sub ml-auto">atendimentos</span></div>
            <div class="card-body" style="padding-top:34px">
                @php $max = max(1, $week->max('total')); @endphp
                <div class="bars">
                    @foreach($week as $i => $d)
                        <div @class(['bar', 'today' => $loop->last])>
                            <div class="fill" style="height: {{ max(4, round($d['total'] / $max * 100)) }}%"><span>{{ $d['total'] }}</span></div>
                            <div class="lbl">{{ $d['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Profissionais hoje</h2></div>
            @forelse($professionals as $p)
                <a href="{{ route('appointments.index', ['professional' => $p->id]) }}" class="tl-item" style="grid-template-columns: auto 1fr auto">
                    <x-avatar :initials="$p->initials()" :color="$p->color" size="sm"/>
                    <div style="min-width:0">
                        <div class="strong truncate">{{ $p->name }}</div>
                        <div class="muted small truncate">{{ $p->specialty }}</div>
                    </div>
                    <span class="badge no-dot">{{ $p->today_count }}</span>
                </a>
            @empty
                <div class="empty" style="padding:28px">
                    <p>Nenhum profissional cadastrado.</p>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('professionals.create') }}" class="btn btn-sm mt-2">Cadastrar profissional</a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
