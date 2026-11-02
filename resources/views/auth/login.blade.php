@extends('layouts.guest')
@section('title', 'Entrar')

@section('content')
    <h2>Entrar</h2>
    <p class="sub">Use o e-mail e a senha cadastrados pela sua clínica.</p>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <x-field name="email" label="E-mail" class="">
            <div class="input-icon">
                <x-icon name="mail"/>
                <input id="email" name="email" type="email" value="{{ old('email') }}" @class(['input', 'is-invalid' => $errors->has('email')]) placeholder="voce@clinica.com" autocomplete="email" required autofocus>
            </div>
        </x-field>

        <x-field name="password" label="Senha" class="">
            <div class="input-icon">
                <x-icon name="lock"/>
                <input id="password" name="password" type="password" class="input" placeholder="••••••••" autocomplete="current-password" required>
            </div>
        </x-field>

        <div class="row between mt-2 mb-3">
            <label class="switch small"><input type="checkbox" name="remember" value="1"> Manter conectado</label>
        </div>

        <button class="btn btn-primary btn-block btn-lg">Entrar <x-icon name="arrow-right" size="sm"/></button>
    </form>

    @unless($brand)
        <div class="divider">ainda não tem conta?</div>
        <a href="{{ route('register') }}" class="btn btn-block btn-lg"><x-icon name="building" size="sm"/> Cadastrar minha clínica</a>
    @endunless
@endsection
