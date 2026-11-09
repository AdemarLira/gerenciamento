@extends('layouts.app')
@section('title', 'Personalizar')
@section('heading', 'Personalizar clínica')
@section('subheading', 'Identidade visual, contato e expediente')

@section('content')
<div class="grid grid-main" style="align-items:start">
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="stack">
    @csrf @method('PUT')

    <div class="card">
        <div class="card-header"><div><h2>Identidade visual</h2><div class="sub">Aparece no menu, na tela de login e nos lembretes.</div></div></div>
        <div class="card-body form-grid">
            <x-field name="name" label="Nome da clínica *" class="col-6">
                <input id="name" name="name" value="{{ old('name', $clinic->name) }}" @class(['input', 'is-invalid' => $errors->has('name')]) data-clinic-name required>
            </x-field>

            <x-field name="logo" label="Logo" help="PNG, JPG, WEBP ou SVG até 2 MB. Prefira imagem quadrada." class="col-6">
                <div class="logo-drop">
                    <div class="logo-preview" data-logo-preview>
                        @if($clinic->logoUrl())<img src="{{ $clinic->logoUrl() }}" alt="">@else{{ $clinic->initials() }}@endif
                    </div>
                    <div style="flex:1;min-width:0">
                        <label class="btn btn-sm" style="cursor:pointer">
                            <x-icon name="upload" size="sm"/> Enviar imagem
                            <input type="file" name="logo" accept="image/*" data-logo-input hidden>
                        </label>
                        @if($clinic->logo_path)
                            <label class="switch small mt-2" style="display:flex"><input type="checkbox" name="remove_logo" value="1"> Remover logo atual</label>
                        @endif
                    </div>
                </div>
            </x-field>

            <x-field name="primary_color" label="Cor principal" help="Botões, destaques e menu ativo usam esta cor." class="col-6">
                <div class="swatches mb-2">
                    @foreach($presets as $hex => $label)
                        <label class="swatch" title="{{ $label }}">
                            <input type="radio" name="primary_color_preset" value="{{ $hex }}" @checked(strtoupper(old('primary_color', $clinic->primary_color)) === $hex)>
                            <span style="--c: {{ $hex }}"></span>
                        </label>
                    @endforeach
                </div>
                <div class="row">
                    <input type="color" class="color-input" value="{{ old('primary_color', $clinic->primary_color) }}" data-accent-input>
                    <input name="primary_color" value="{{ strtoupper(old('primary_color', $clinic->primary_color)) }}" class="input" style="max-width:140px;font-family:ui-monospace,monospace" maxlength="7" data-accent-hex>
                    <span class="muted small">ou escolha uma cor personalizada</span>
                </div>
            </x-field>

            <x-field name="welcome_message" label="Frase da tela de login" class="col-6">
                <input id="welcome_message" name="welcome_message" value="{{ old('welcome_message', $clinic->welcome_message) }}" class="input" maxlength="160" placeholder="Ex.: Cuidado de verdade, do agendamento ao retorno.">
            </x-field>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div><h2>Link de acesso da equipe</h2><div class="sub">A tela de login com a marca da clínica.</div></div></div>
        <div class="card-body form-grid">
            <x-field name="slug" label="Endereço" class="col-6">
                <div class="row" style="gap:0">
                    <span class="link-box prefix" style="border-radius:10px 0 0 10px;border-right:0;height:42px;white-space:nowrap">{{ preg_replace('#^https?://#', '', url('/c')) }}/</span>
                    <input id="slug" name="slug" value="{{ old('slug', $clinic->slug) }}" @class(['input', 'is-invalid' => $errors->has('slug')]) style="border-radius:0 10px 10px 0" required>
                </div>
            </x-field>
            <div class="col-6 row">
                <a href="{{ route('login.clinic', $clinic->slug) }}" target="_blank" class="btn btn-sm"><x-icon name="eye" size="sm"/> Ver tela de login</a>
                <button type="button" class="btn btn-sm btn-ghost" data-copy="{{ route('login.clinic', $clinic->slug) }}"><x-icon name="copy" size="sm"/> Copiar link</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div><h2>Agendamento online</h2><div class="sub">Link para seus pacientes marcarem consulta sozinhos, 24 horas por dia.</div></div></div>
        <div class="card-body stack" style="gap:14px">
            <div class="share-link">
                <span class="link-box"><x-icon name="link" size="sm"/> {{ $clinic->bookingUrl() }}</span>
                <button type="button" class="btn btn-sm" data-copy="{{ $clinic->bookingUrl() }}"><x-icon name="copy" size="sm"/> Copiar</button>
                <a href="{{ $clinic->bookingUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-ghost"><x-icon name="eye" size="sm"/> Abrir</a>
            </div>
            <div class="row wrap" style="gap:8px">
                <a href="https://wa.me/?text={{ rawurlencode('Agende sua consulta na '.$clinic->name.' pelo link: '.$clinic->bookingUrl()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-wa"><x-whatsapp-icon size="15"/> Enviar link pelo WhatsApp</a>
                <span class="muted small">Dica: coloque o link na bio do Instagram e no perfil do Google.</span>
            </div>
            <p class="muted small">Os horários livres seguem o expediente e a duração padrão abaixo (segunda a sábado). O telefone da clínica é usado como WhatsApp de contato na página.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Contato e expediente</h2></div>
        <div class="card-body form-grid">
            <x-field name="document" label="CNPJ / CPF" class="col-2">
                <input id="document" name="document" value="{{ old('document', $clinic->document) }}" class="input">
            </x-field>
            <x-field name="phone" label="Telefone / WhatsApp" class="col-2">
                <input id="phone" name="phone" value="{{ old('phone', $clinic->phone) }}" class="input" data-mask="phone">
            </x-field>
            <x-field name="email" label="E-mail" class="col-2">
                <input id="email" name="email" type="email" value="{{ old('email', $clinic->email) }}" @class(['input', 'is-invalid' => $errors->has('email')])>
            </x-field>
            <x-field name="address" label="Endereço" class="col-6">
                <input id="address" name="address" value="{{ old('address', $clinic->address) }}" class="input">
            </x-field>
            <x-field name="opens_at" label="Abre às" class="col-2">
                <input id="opens_at" name="opens_at" type="time" value="{{ old('opens_at', substr($clinic->opens_at, 0, 5)) }}" class="input" required>
            </x-field>
            <x-field name="closes_at" label="Fecha às" class="col-2">
                <input id="closes_at" name="closes_at" type="time" value="{{ old('closes_at', substr($clinic->closes_at, 0, 5)) }}" @class(['input', 'is-invalid' => $errors->has('closes_at')]) required>
            </x-field>
            <x-field name="slot_minutes" label="Duração padrão da consulta" class="col-2">
                <select id="slot_minutes" name="slot_minutes" class="select">
                    @foreach([10, 15, 20, 30, 40, 45, 50, 60, 90] as $m)
                        <option value="{{ $m }}" @selected((int) old('slot_minutes', $clinic->slot_minutes) === $m)>{{ $m }} minutos</option>
                    @endforeach
                </select>
            </x-field>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary btn-lg"><x-icon name="check" size="sm"/> Salvar personalização</button>
        </div>
    </div>
</form>

<aside class="preview-pane">
    <div class="card">
        <div class="card-header"><h2>Pré-visualização</h2><span class="sub ml-auto">ao vivo</span></div>
        <div class="card-body">
            <div class="mock">
                <div class="mock-side">
                    <div class="row mb-2">
                        <div class="logo-preview" data-logo-preview style="width:36px;height:36px;border-radius:10px;font-size:13px">
                            @if($clinic->logoUrl())<img src="{{ $clinic->logoUrl() }}" alt="">@else{{ $clinic->initials() }}@endif
                        </div>
                        <strong class="truncate" data-clinic-name-preview>{{ $clinic->name }}</strong>
                    </div>
                    <div class="mock-link active"></div>
                    <div class="mock-link"></div>
                    <div class="mock-link"></div>
                </div>
                <div style="padding:16px" class="stack">
                    <div class="card stat highlight" style="padding:14px">
                        <div class="label">Consultas hoje</div>
                        <div class="value" style="font-size:24px">12</div>
                    </div>
                    <div class="row">
                        <span class="btn btn-primary btn-sm">Nova consulta</span>
                        <span class="badge status-confirmed">Confirmado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
</div>
@endsection
