@extends('layouts.app')
@section('title', 'Pacientes')
@section('heading', 'Pacientes')
@section('subheading', 'Cadastro e histórico de atendimentos')
@section('actions')
    <a href="{{ route('patients.create') }}" class="btn btn-primary"><x-icon name="user-plus" size="sm"/> <span class="btn-label">Novo paciente</span></a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="input-icon" style="flex:1;max-width:420px">
            <x-icon name="search"/>
            <input name="q" value="{{ $q }}" class="input" placeholder="Buscar por nome, CPF, telefone ou e-mail">
        </form>
        <div class="right muted small hide-sm">{{ $patients->total() }} {{ \Illuminate\Support\Str::plural('paciente', $patients->total()) }}</div>
    </div>

    @if($patients->isEmpty())
        <div class="empty">
            <div class="ico"><x-icon name="users"/></div>
            @if($q)
                <h3>Nenhum resultado para "{{ $q }}"</h3>
                <p>Confira a grafia ou busque pelo CPF.</p>
            @else
                <h3>Nenhum paciente ainda</h3>
                <p>Cadastre o primeiro paciente da clínica.</p>
                <a href="{{ route('patients.create') }}" class="btn btn-primary mt-2"><x-icon name="user-plus" size="sm"/> Novo paciente</a>
            @endif
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Paciente</th>
                    <th class="hide-sm">Contato</th>
                    <th class="hide-sm">Convênio</th>
                    <th class="hide-sm">Última consulta</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($patients as $p)
                    <tr>
                        <td>
                            <a href="{{ route('patients.show', $p) }}" class="row">
                                <x-avatar :initials="$p->initials()"/>
                                <div style="min-width:0">
                                    <div class="strong">{{ $p->name }}</div>
                                    <div class="muted small">
                                        {{ $p->age() !== null ? $p->age().' anos' : 'Idade não informada' }}
                                        @if($p->cpf) · {{ $p->formattedCpf() }} @endif
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td class="hide-sm">
                            <div>{{ $p->formattedPhone() ?? '—' }}</div>
                            <div class="muted small">{{ $p->email }}</div>
                        </td>
                        <td class="hide-sm">
                            @if($p->insurance)<span class="badge no-dot">{{ $p->insurance }}</span>@else<span class="muted">—</span>@endif
                        </td>
                        <td class="hide-sm">
                            @if($p->last_visit)
                                {{ \Illuminate\Support\Carbon::parse($p->last_visit)->format('d/m/Y') }}
                                <div class="muted small">{{ $p->appointments_count }} {{ \Illuminate\Support\Str::plural('consulta', $p->appointments_count) }}</div>
                            @else
                                <span class="muted">Sem consultas</span>
                            @endif
                        </td>
                        <td class="actions">
                            @if($wa = $p->whatsappUrl('Olá '.$p->firstName().', tudo bem? Aqui é da '.$currentClinic->name.'.'))
                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-ghost btn-icon btn-wa-soft" title="Conversar no WhatsApp" aria-label="Conversar com {{ $p->name }} no WhatsApp"><x-whatsapp-icon size="16"/></a>
                            @endif
                            <a href="{{ route('appointments.create', ['patient' => $p->id]) }}" class="btn btn-sm btn-ghost btn-icon" title="Agendar"><x-icon name="calendar-plus" size="sm"/></a>
                            <a href="{{ route('patients.edit', $p) }}" class="btn btn-sm btn-ghost btn-icon" title="Editar"><x-icon name="pencil" size="sm"/></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $patients->links() }}
    @endif
</div>
@endsection
