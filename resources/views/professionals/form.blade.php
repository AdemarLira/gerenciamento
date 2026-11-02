@extends('layouts.app')
@php $editing = $professional->exists; @endphp
@section('title', $editing ? 'Editar profissional' : 'Novo profissional')
@section('heading', $editing ? 'Editar profissional' : 'Novo profissional')
@section('actions')
    <a href="{{ route('professionals.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" size="sm"/> Voltar</a>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('professionals.update', $professional) : route('professionals.store') }}" class="card" style="max-width:820px">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="card-body form-grid">
        <x-field name="name" label="Nome *" class="col-4">
            <input id="name" name="name" value="{{ old('name', $professional->name) }}" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="Dra. Maria Silva" required autofocus>
        </x-field>
        <x-field name="registry" label="Registro (CRM, CRO, CRP…)" class="col-2">
            <input id="registry" name="registry" value="{{ old('registry', $professional->registry) }}" class="input" placeholder="CRM 000000">
        </x-field>
        <x-field name="specialty" label="Especialidade" class="col-3">
            <input id="specialty" name="specialty" value="{{ old('specialty', $professional->specialty) }}" class="input" list="specialties">
            <datalist id="specialties">
                @foreach(['Clínico Geral', 'Cardiologia', 'Dermatologia', 'Ginecologia', 'Pediatria', 'Ortopedia', 'Psicologia', 'Psiquiatria', 'Nutrição', 'Fisioterapia', 'Odontologia', 'Fonoaudiologia'] as $s)<option value="{{ $s }}">@endforeach
            </datalist>
        </x-field>
        <x-field name="phone" label="Telefone" class="col-3">
            <input id="phone" name="phone" value="{{ old('phone', $professional->phone) }}" class="input" data-mask="phone">
        </x-field>
        <x-field name="email" label="E-mail" class="col-6">
            <input id="email" name="email" type="email" value="{{ old('email', $professional->email) }}" @class(['input', 'is-invalid' => $errors->has('email')])>
        </x-field>
        <x-field name="color" label="Cor na agenda" class="col-6">
            <div class="swatches">
                @foreach(['#7C5CFF', '#3B82F6', '#06B6D4', '#10B981', '#84CC16', '#F59E0B', '#F97316', '#EF4444', '#EC4899', '#A78BFA'] as $c)
                    <label class="swatch" title="{{ $c }}">
                        <input type="radio" name="color" value="{{ $c }}" @checked(strtoupper(old('color', $professional->color)) === $c)>
                        <span style="--c: {{ $c }}"></span>
                    </label>
                @endforeach
            </div>
        </x-field>
        <div class="col-6">
            <label class="switch"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $professional->is_active))> Ativo (aparece na agenda)</label>
        </div>
    </div>
    <div class="form-actions">
        @if($editing)
            <button form="delete-form" class="btn btn-danger" style="margin-right:auto"><x-icon name="trash" size="sm"/> Excluir</button>
        @endif
        <a href="{{ route('professionals.index') }}" class="btn btn-ghost">Cancelar</a>
        <button class="btn btn-primary"><x-icon name="check" size="sm"/> Salvar</button>
    </div>
</form>
@if($editing)
    <form id="delete-form" method="POST" action="{{ route('professionals.destroy', $professional) }}" data-confirm="Excluir {{ $professional->name }}? Se houver consultas, ele será apenas desativado.">
        @csrf @method('DELETE')
    </form>
@endif
@endsection
