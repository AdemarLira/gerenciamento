@extends('layouts.app')
@php $editing = $appointment->exists; @endphp
@section('title', $editing ? 'Editar consulta' : 'Nova consulta')
@section('heading', $editing ? 'Editar consulta' : 'Nova consulta')
@section('subheading', $editing ? $appointment->patient->name.' · '.$appointment->starts_at->format('d/m/Y H:i') : 'Agende um atendimento')
@section('actions')
    <a href="{{ route('appointments.index', ['date' => $date]) }}" class="btn btn-ghost"><x-icon name="arrow-left" size="sm"/> Agenda</a>
@endsection

@section('content')
@if($professionals->isEmpty() || $patients->isEmpty())
    <div class="card" style="max-width:720px">
        <div class="empty">
            <div class="ico"><x-icon name="alert"/></div>
            <h3>Faltam dados para agendar</h3>
            <p>É preciso ter ao menos um profissional ativo e um paciente cadastrado.</p>
            <div class="row mt-2" style="justify-content:center">
                @if($patients->isEmpty())<a href="{{ route('patients.create') }}" class="btn btn-primary">Cadastrar paciente</a>@endif
                @if($professionals->isEmpty() && auth()->user()->isAdmin())<a href="{{ route('professionals.create') }}" class="btn">Cadastrar profissional</a>@endif
            </div>
        </div>
    </div>
@else
<div class="grid grid-main" style="align-items:start">
<form method="POST" action="{{ $editing ? route('appointments.update', $appointment) : route('appointments.store') }}" class="card">
    @csrf
    @if($editing) @method('PUT') @endif
    @if($bookingRequest ?? null)
        <input type="hidden" name="booking_request_id" value="{{ $bookingRequest->id }}">
        <div class="alert alert-success" style="margin:18px 22px 0">
            <x-icon name="message"/>
            <div>
                <strong>Solicitação do site — {{ $bookingRequest->name }}</strong><br>
                {{ $bookingRequest->procedure ?: 'Avaliação' }}
                @if($bookingRequest->preferred_date) · prefere {{ $bookingRequest->preferred_date->format('d/m') }}@endif
                @if($bookingRequest->periodLabel()) ({{ mb_strtolower($bookingRequest->periodLabel()) }})@endif
                · {{ $bookingRequest->formattedPhone() }}
            </div>
        </div>
    @endif

    <div class="card-header"><h2>Paciente e profissional</h2></div>
    <div class="card-body form-grid">
        <x-field name="patient_id" label="Paciente *" class="col-6">
            @unless($editing)
                <div class="input-icon mb-2">
                    <x-icon name="search"/>
                    <input class="input" placeholder="Filtrar pacientes pelo nome…" data-filter-select="patient_id">
                </div>
            @endunless
            <select id="patient_id" name="patient_id" @class(['select', 'is-invalid' => $errors->has('patient_id')]) required>
                <option value="">Selecione o paciente</option>
                @foreach($patients as $p)
                    <option value="{{ $p->id }}" @selected((int) old('patient_id', $appointment->patient_id) === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <div class="help"><a href="{{ route('patients.create') }}" class="link">+ Cadastrar novo paciente</a></div>
        </x-field>

        <x-field name="professional_id" label="Profissional *" class="col-3">
            <select id="professional_id" name="professional_id" @class(['select', 'is-invalid' => $errors->has('professional_id')]) required>
                <option value="">Selecione</option>
                @foreach($professionals as $p)
                    <option value="{{ $p->id }}" @selected((int) old('professional_id', $appointment->professional_id) === $p->id)>{{ $p->name }}{{ $p->specialty ? ' — '.$p->specialty : '' }}</option>
                @endforeach
            </select>
        </x-field>
        <x-field name="type" label="Tipo de atendimento" class="col-3">
            <input id="type" name="type" value="{{ old('type', $appointment->type) }}" class="input" list="types">
            <datalist id="types">@foreach(\App\Models\Appointment::TYPES as $t)<option value="{{ $t }}">@endforeach</datalist>
        </x-field>
    </div>

    <div class="card-header" style="border-top:1px solid var(--border)"><h2>Data e horário</h2></div>
    <div class="card-body form-grid">
        <x-field name="date" label="Data *" class="col-2">
            <input id="date" name="date" type="date" value="{{ old('date', $date) }}" @class(['input', 'is-invalid' => $errors->has('date')]) required>
        </x-field>
        <x-field name="time" label="Horário *" class="col-2">
            <input id="time" name="time" type="time" step="300" value="{{ old('time', $time) }}" @class(['input', 'is-invalid' => $errors->has('time')]) required>
        </x-field>
        <x-field name="duration" label="Duração" class="col-2">
            <select id="duration" name="duration" class="select">
                @foreach([10, 15, 20, 30, 40, 45, 50, 60, 90, 120] as $m)
                    <option value="{{ $m }}" @selected((int) old('duration', $duration) === $m)>{{ $m < 60 ? $m.' min' : intdiv($m, 60).'h'.($m % 60 ? ($m % 60) : '') }}</option>
                @endforeach
                @unless(in_array((int) old('duration', $duration), [10, 15, 20, 30, 40, 45, 50, 60, 90, 120]))
                    <option value="{{ old('duration', $duration) }}" selected>{{ old('duration', $duration) }} min</option>
                @endunless
            </select>
        </x-field>
        <x-field name="status" label="Status" class="col-3">
            <select id="status" name="status" class="select">
                @foreach(\App\Models\Appointment::STATUSES as $k => $v)
                    <option value="{{ $k }}" @selected(old('status', $appointment->status) === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </x-field>
        <x-field name="price" label="Valor (R$)" class="col-3">
            <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $appointment->price) }}" class="input" placeholder="0,00">
        </x-field>
    </div>

    <div class="card-header" style="border-top:1px solid var(--border)">
        <div><h2>Procedimento</h2><div class="sub">Informações clínicas para a equipe e orientações para o paciente.</div></div>
    </div>
    <div class="card-body form-grid">
        <x-field name="procedure" label="Procedimento a realizar" class="col-6">
            <input id="procedure" name="procedure" value="{{ old('procedure', $appointment->procedure) }}" @class(['input', 'is-invalid' => $errors->has('procedure')]) list="procedures" placeholder="Ex.: Lentes de contato dental — 10 elementos">
            <datalist id="procedures">@foreach(\App\Models\Appointment::PROCEDURES as $p)<option value="{{ $p }}">@endforeach</datalist>
        </x-field>
        <x-field name="procedure_details" label="Planejamento clínico" help="Dentes/região, materiais, etapas, anestesia, tempo de cadeira… Visível só para a equipe." class="col-6">
            <textarea id="procedure_details" name="procedure_details" class="textarea" rows="4" placeholder="Ex.: Elementos 13 a 23, porcelana feldspática, prova do mock-up aprovada, cor BL2.">{{ old('procedure_details', $appointment->procedure_details) }}</textarea>
        </x-field>
        <x-field name="patient_instructions" label="Orientações ao paciente" help="Pode ser enviado pelo WhatsApp depois de salvar." class="col-6">
            <textarea id="patient_instructions" name="patient_instructions" class="textarea" rows="3" placeholder="Ex.: Chegar 15 minutos antes, fazer refeição leve, trazer exames recentes.">{{ old('patient_instructions', $appointment->patient_instructions) }}</textarea>
        </x-field>
        <x-field name="notes" label="Observações internas" class="col-6">
            <textarea id="notes" name="notes" class="textarea" rows="2" placeholder="Recados para a recepção, forma de pagamento, etc.">{{ old('notes', $appointment->notes) }}</textarea>
        </x-field>
    </div>

    <div class="form-actions">
        <a href="{{ route('appointments.index', ['date' => $date]) }}" class="btn btn-ghost">Cancelar</a>
        <button class="btn btn-primary"><x-icon name="check" size="sm"/> {{ $editing ? 'Salvar alterações' : 'Agendar' }}</button>
    </div>
</form>

<div class="stack">
    <div class="card card-body">
        <div class="row mb-2 stat" style="padding:0"><span class="ico"><x-icon name="clock" size="sm"/></span>
            <div><div class="strong">Expediente</div><div class="muted small">{{ substr($currentClinic->opens_at, 0, 5) }} às {{ substr($currentClinic->closes_at, 0, 5) }} · consultas de {{ $currentClinic->slot_minutes }} min</div></div>
        </div>
        <p class="muted small">O sistema impede que o mesmo profissional tenha duas consultas no mesmo horário. Consultas canceladas ou com falta liberam o horário.</p>
    </div>

    @if($editing)
        <div class="card card-body">
            <div class="strong mb-2">Ações rápidas</div>
            <div class="stack" style="gap:8px">
                <a href="{{ route('patients.show', $appointment->patient_id) }}" class="btn btn-block"><x-icon name="user" size="sm"/> Ver ficha do paciente</a>
                @if($wa = $appointment->patient->whatsappUrl('Olá '.$appointment->patient->firstName().'! Lembrete da sua consulta em '.$appointment->starts_at->format('d/m \à\s H:i').' com '.$appointment->professional->name.' na '.$currentClinic->name.'. Podemos confirmar?'))
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-wa btn-block"><x-whatsapp-icon size="15"/> Enviar lembrete no WhatsApp</a>
                    @if($appointment->patient_instructions)
                        <a href="{{ $appointment->patient->whatsappUrl('Olá '.$appointment->patient->firstName().'! Seguem as orientações para o seu atendimento'.($appointment->procedure ? ' ('.$appointment->procedure.')' : '').' em '.$appointment->starts_at->format('d/m \à\s H:i').":\n\n".$appointment->patient_instructions) }}" target="_blank" rel="noopener" class="btn btn-wa btn-block"><x-whatsapp-icon size="15"/> Enviar orientações</a>
                    @endif
                    <a href="{{ $appointment->patient->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-block"><x-whatsapp-icon size="15"/> Abrir conversa</a>
                @endif
                <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" data-confirm="Excluir esta consulta definitivamente? Para manter o histórico, prefira mudar o status para Cancelado.">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-block"><x-icon name="trash" size="sm"/> Excluir consulta</button>
                </form>
            </div>
        </div>
    @endif
</div>
</div>
@endif
@endsection
