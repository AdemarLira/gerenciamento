@extends('layouts.app')
@section('title', $patient->name)
@section('heading', 'Ficha do paciente')
@section('actions')
    <a href="{{ route('patients.index') }}" class="btn btn-ghost hide-sm"><x-icon name="arrow-left" size="sm"/> Pacientes</a>
    <a href="{{ route('appointments.create', ['patient' => $patient->id]) }}" class="btn btn-primary"><x-icon name="calendar-plus" size="sm"/> <span class="btn-label">Agendar</span></a>
@endsection

@section('content')
<div class="grid grid-main">
    <div class="stack">
        <div class="card">
            <div class="profile-head">
                <x-avatar :initials="$patient->initials()" size="lg"/>
                <div style="min-width:0;flex:1">
                    <h2>{{ $patient->name }}</h2>
                    <div class="muted mt-1">
                        {{ $patient->age() !== null ? $patient->age().' anos' : 'Idade não informada' }}
                        @if($patient->insurance) · {{ $patient->insurance }} @endif
                        · paciente desde {{ $patient->created_at->format('m/Y') }}
                    </div>
                </div>
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm"><x-icon name="pencil" size="sm"/> Editar</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Próximas consultas</h2><span class="sub ml-auto">{{ $upcoming->count() }}</span></div>
            @forelse($upcoming as $a)
                <div class="tl-item">
                    <div class="tl-time">{{ $a->starts_at->format('d/m') }}<small>{{ $a->starts_at->format('H:i') }}</small></div>
                    <div class="tl-body">
                        <span class="pro-dot" style="background: {{ $a->professional->color }}"></span>
                        <div style="min-width:0">
                            <div class="strong truncate">{{ $a->type ?? 'Consulta' }} com {{ $a->professional->name }}</div>
                            <div class="muted small">{{ ucfirst($a->starts_at->translatedFormat('l')) }} · {{ $a->durationMinutes() }} min</div>
                        </div>
                    </div>
                    @include('appointments._status_menu', ['appointment' => $a])
                </div>
            @empty
                <div class="empty" style="padding:28px"><p>Nenhuma consulta futura agendada.</p></div>
            @endforelse
        </div>

        <div class="card">
            <div class="card-header"><h2>Histórico</h2><span class="sub ml-auto">{{ $history->count() }} {{ \Illuminate\Support\Str::plural('atendimento', $history->count()) }}</span></div>
            @if($history->isEmpty())
                <div class="empty" style="padding:28px"><p>Ainda não há atendimentos anteriores.</p></div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Data</th><th>Atendimento</th><th class="hide-sm">Valor</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($history as $a)
                            <tr>
                                <td><div class="strong">{{ $a->starts_at->format('d/m/Y') }}</div><div class="muted small">{{ $a->starts_at->format('H:i') }}</div></td>
                                <td>
                                    <div>{{ $a->type ?? 'Consulta' }}</div>
                                    <div class="muted small row" style="gap:6px"><span class="pro-dot" style="background: {{ $a->professional->color }}"></span>{{ $a->professional->name }}</div>
                                </td>
                                <td class="hide-sm">{{ $a->price ? 'R$ '.number_format($a->price, 2, ',', '.') : '—' }}</td>
                                <td><x-status :appointment="$a"/></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="stack">
        <div class="card">
            <div class="card-header"><h2>Dados</h2></div>
            <dl class="dl">
                <div><dt>CPF</dt><dd>{{ $patient->formattedCpf() ?? '—' }}</dd></div>
                <div><dt>Nascimento</dt><dd>{{ $patient->birth_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt>Gênero</dt><dd>{{ \App\Models\Patient::GENDERS[$patient->gender] ?? '—' }}</dd></div>
                <div><dt>Telefone</dt><dd>{{ $patient->formattedPhone() ?? '—' }}</dd></div>
                <div><dt>E-mail</dt><dd class="truncate" style="max-width:200px">{{ $patient->email ?? '—' }}</dd></div>
                <div><dt>Endereço</dt><dd>{{ $patient->address ?? '—' }}</dd></div>
            </dl>
            @if($patient->whatsappUrl())
                <div class="card-body" style="padding-top:0">
                    <a href="{{ $patient->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-block"><x-icon name="message" size="sm"/> Conversar no WhatsApp</a>
                </div>
            @endif
        </div>

        @if($patient->notes)
            <div class="card">
                <div class="card-header"><h2>Observações</h2></div>
                <div class="card-body text-2" style="white-space:pre-line">{{ $patient->notes }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('patients.destroy', $patient) }}" data-confirm="Remover {{ $patient->name }}? O histórico será mantido para relatórios.">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-block"><x-icon name="trash" size="sm"/> Remover paciente</button>
        </form>
    </div>
</div>
@endsection
