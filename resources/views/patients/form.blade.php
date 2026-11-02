@extends('layouts.app')
@php $editing = $patient->exists; @endphp
@section('title', $editing ? 'Editar paciente' : 'Novo paciente')
@section('heading', $editing ? 'Editar paciente' : 'Novo paciente')
@section('subheading', $editing ? $patient->name : 'Preencha os dados de cadastro')
@section('actions')
    <a href="{{ $editing ? route('patients.show', $patient) : route('patients.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" size="sm"/> Voltar</a>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('patients.update', $patient) : route('patients.store') }}" class="card" style="max-width:920px">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="card-header"><h2>Dados pessoais</h2></div>
    <div class="card-body form-grid">
        <x-field name="name" label="Nome completo *" class="col-4">
            <input id="name" name="name" value="{{ old('name', $patient->name) }}" @class(['input', 'is-invalid' => $errors->has('name')]) required autofocus>
        </x-field>
        <x-field name="birth_date" label="Data de nascimento" class="col-2">
            <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $patient->birth_date?->format('Y-m-d')) }}" @class(['input', 'is-invalid' => $errors->has('birth_date')]) max="{{ now()->toDateString() }}">
        </x-field>
        <x-field name="cpf" label="CPF" class="col-2">
            <input id="cpf" name="cpf" value="{{ old('cpf', $patient->cpf) }}" @class(['input', 'is-invalid' => $errors->has('cpf')]) data-mask="cpf" inputmode="numeric" placeholder="000.000.000-00">
        </x-field>
        <x-field name="gender" label="Gênero" class="col-2">
            <select id="gender" name="gender" class="select">
                <option value="">Selecione</option>
                @foreach(\App\Models\Patient::GENDERS as $k => $v)
                    <option value="{{ $k }}" @selected(old('gender', $patient->gender) === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </x-field>
        <x-field name="insurance" label="Convênio" class="col-2">
            <input id="insurance" name="insurance" value="{{ old('insurance', $patient->insurance) }}" class="input" list="insurances" placeholder="Particular">
            <datalist id="insurances">
                @foreach(['Particular', 'Unimed', 'Bradesco Saúde', 'SulAmérica', 'Amil', 'Hapvida', 'NotreDame Intermédica'] as $i)<option value="{{ $i }}">@endforeach
            </datalist>
        </x-field>
    </div>

    <div class="card-header" style="border-top:1px solid var(--border)"><h2>Contato</h2></div>
    <div class="card-body form-grid">
        <x-field name="phone" label="Telefone / WhatsApp" class="col-2">
            <input id="phone" name="phone" value="{{ old('phone', $patient->phone) }}" @class(['input', 'is-invalid' => $errors->has('phone')]) data-mask="phone" inputmode="tel" placeholder="(00) 00000-0000">
        </x-field>
        <x-field name="email" label="E-mail" class="col-4">
            <input id="email" name="email" type="email" value="{{ old('email', $patient->email) }}" @class(['input', 'is-invalid' => $errors->has('email')])>
        </x-field>
        <x-field name="address" label="Endereço" class="col-6">
            <input id="address" name="address" value="{{ old('address', $patient->address) }}" class="input" placeholder="Rua, número, bairro, cidade">
        </x-field>
        <x-field name="notes" label="Observações" help="Alergias, preferências, informações importantes para o atendimento." class="col-6">
            <textarea id="notes" name="notes" class="textarea" rows="4">{{ old('notes', $patient->notes) }}</textarea>
        </x-field>
    </div>

    <div class="form-actions">
        <a href="{{ $editing ? route('patients.show', $patient) : route('patients.index') }}" class="btn btn-ghost">Cancelar</a>
        <button class="btn btn-primary"><x-icon name="check" size="sm"/> {{ $editing ? 'Salvar alterações' : 'Cadastrar paciente' }}</button>
    </div>
</form>
@endsection
