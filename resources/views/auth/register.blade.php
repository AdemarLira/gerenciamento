@extends('layouts.guest')
@section('title', 'Cadastrar clínica')

@section('content')
    <h2>Cadastre sua clínica</h2>
    <p class="sub">Leva menos de um minuto. Depois você personaliza logo e cores.</p>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <x-field name="clinic_name" label="Nome da clínica" class="">
            <input id="clinic_name" name="clinic_name" value="{{ old('clinic_name') }}" @class(['input', 'is-invalid' => $errors->has('clinic_name')]) placeholder="Ex.: Clínica Bem Viver" required autofocus>
        </x-field>
        <x-field name="name" label="Seu nome" class="">
            <input id="name" name="name" value="{{ old('name') }}" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="Nome completo" required>
        </x-field>
        <x-field name="email" label="E-mail de acesso" class="">
            <input id="email" name="email" type="email" value="{{ old('email') }}" @class(['input', 'is-invalid' => $errors->has('email')]) placeholder="voce@clinica.com" required>
        </x-field>
        <div class="grid grid-2" style="gap:12px; margin-top:16px">
            <x-field name="password" label="Senha" class="">
                <input id="password" name="password" type="password" @class(['input', 'is-invalid' => $errors->has('password')]) placeholder="mín. 8 caracteres" required>
            </x-field>
            <x-field name="password_confirmation" label="Confirmar senha" class="">
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" placeholder="repita a senha" required>
            </x-field>
        </div>

        <button class="btn btn-primary btn-block btn-lg mt-3">Criar conta <x-icon name="arrow-right" size="sm"/></button>
    </form>

    <p class="muted mt-3" style="text-align:center">Já tem conta? <a href="{{ route('login') }}" class="link">Entrar</a></p>
@endsection
