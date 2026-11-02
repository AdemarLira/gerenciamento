@extends('layouts.app')
@php $editing = $user->exists; @endphp
@section('title', $editing ? 'Editar usuário' : 'Novo usuário')
@section('heading', $editing ? 'Editar usuário' : 'Novo usuário')
@section('actions')
    <a href="{{ route('users.index') }}" class="btn btn-ghost"><x-icon name="arrow-left" size="sm"/> Voltar</a>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}" class="card" style="max-width:720px">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="card-body form-grid">
        <x-field name="name" label="Nome *" class="col-3">
            <input id="name" name="name" value="{{ old('name', $user->name) }}" @class(['input', 'is-invalid' => $errors->has('name')]) required autofocus>
        </x-field>
        <x-field name="email" label="E-mail de acesso *" class="col-3">
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" @class(['input', 'is-invalid' => $errors->has('email')]) required>
        </x-field>
        <x-field name="role" label="Perfil *" class="col-6">
            <select id="role" name="role" class="select">
                @foreach(\App\Models\User::ROLES as $k => $v)
                    <option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </x-field>
        <x-field name="password" :label="$editing ? 'Nova senha' : 'Senha *'" :help="$editing ? 'Deixe em branco para manter a senha atual.' : 'Mínimo de 8 caracteres.'" class="col-3">
            <input id="password" name="password" type="password" @class(['input', 'is-invalid' => $errors->has('password')]) autocomplete="new-password" @required(! $editing)>
        </x-field>
        <x-field name="password_confirmation" label="Confirmar senha" class="col-3">
            <input id="password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password">
        </x-field>
    </div>
    <div class="form-actions">
        <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancelar</a>
        <button class="btn btn-primary"><x-icon name="check" size="sm"/> Salvar</button>
    </div>
</form>
@endsection
