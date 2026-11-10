@extends('layouts.app')
@section('title', 'Solicitações')
@section('heading', 'Solicitações do site')
@section('subheading', 'Pedidos de avaliação feitos pelos pacientes no seu site')
@section('actions')
    <a href="{{ $currentClinic->siteUrl() }}" target="_blank" rel="noopener" class="btn"><x-icon name="eye" size="sm"/> <span class="btn-label">Ver site</span></a>
@endsection

@section('content')
@php
    $open = ($counts['new'] ?? 0) + ($counts['contacted'] ?? 0);
    $tabs = ['abertas' => ['Em aberto', $open], 'agendadas' => ['Agendadas', $counts['scheduled'] ?? 0], 'arquivadas' => ['Arquivadas', $counts['archived'] ?? 0]];
@endphp

<div class="toolbar">
    <div class="segmented">
        @foreach($tabs as $key => [$label, $n])
            <a href="{{ route('requests.index', ['filtro' => $key]) }}" @class(['active' => $filter === $key])>{{ $label }} <span class="muted">{{ $n }}</span></a>
        @endforeach
    </div>
</div>

@if($requests->isEmpty())
    <div class="card">
        <div class="empty">
            <div class="ico"><x-icon name="message"/></div>
            <h3>{{ $filter === 'abertas' ? 'Nenhum pedido em aberto' : 'Nada por aqui' }}</h3>
            <p>Quando um paciente pedir uma avaliação pelo site, o pedido aparece aqui para você marcar na agenda.</p>
            <a href="{{ $currentClinic->siteUrl() }}#agendar" target="_blank" rel="noopener" class="btn mt-2"><x-icon name="eye" size="sm"/> Ver formulário no site</a>
        </div>
    </div>
@else
    <div class="stack" style="gap:14px">
        @foreach($requests as $r)
            <div @class(['card', 'req', 'is-new' => $r->status === 'new'])>
                <div class="req-main">
                    <x-avatar :initials="collect(explode(' ', $r->name))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')"/>
                    <div style="min-width:0;flex:1">
                        <div class="row wrap" style="gap:8px">
                            <span class="strong">{{ $r->name }}</span>
                            <span class="badge req-{{ $r->status }}">{{ $r->statusLabel() }}</span>
                            <span class="muted small">{{ $r->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="req-meta">
                            <span><x-icon name="sparkles" size="sm"/> {{ $r->procedure ?: 'Avaliação' }}</span>
                            <span><x-icon name="phone" size="sm"/> {{ $r->formattedPhone() }}</span>
                            @if($r->email)<span><x-icon name="mail" size="sm"/> {{ $r->email }}</span>@endif
                            @if($r->preferred_date || $r->preferred_period)
                                <span><x-icon name="calendar" size="sm"/> Prefere {{ $r->preferred_date ? $r->preferred_date->translatedFormat('D, d/m') : '' }} {{ $r->periodLabel() ? '· '.mb_strtolower($r->periodLabel()) : '' }}</span>
                            @endif
                        </div>
                        @if($r->message)<p class="req-msg">“{{ $r->message }}”</p>@endif
                        @if($r->appointment)
                            <a href="{{ route('appointments.edit', $r->appointment) }}" class="req-link">
                                <x-icon name="check-circle" size="sm"/> Agendado para {{ $r->appointment->starts_at->translatedFormat('d/m \à\s H:i') }} com {{ $r->appointment->professional->name }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="req-actions">
                    @if($wa = $r->whatsappUrl('Olá '.$r->firstName().'! Aqui é da '.$currentClinic->name.'. Recebemos seu pedido de avaliação'.($r->procedure ? ' para '.mb_strtolower($r->procedure) : '').' pelo site. Qual o melhor dia e horário para você?'))
                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-wa"><x-whatsapp-icon size="15"/> WhatsApp</a>
                    @endif
                    @if($r->status !== 'scheduled')
                        <form method="POST" action="{{ route('requests.schedule', $r) }}">
                            @csrf
                            <button class="btn btn-sm btn-primary"><x-icon name="calendar-plus" size="sm"/> Marcar na agenda</button>
                        </form>
                    @endif
                    @if($r->status === 'new')
                        <form method="POST" action="{{ route('requests.update', $r) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="contacted">
                            <button class="btn btn-sm btn-ghost">Já entrei em contato</button>
                        </form>
                    @endif
                    @if(in_array($r->status, ['new', 'contacted']))
                        <form method="POST" action="{{ route('requests.update', $r) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="archived">
                            <button class="btn btn-sm btn-ghost btn-icon" title="Arquivar" aria-label="Arquivar"><x-icon name="x" size="sm"/></button>
                        </form>
                    @elseif($r->status === 'archived')
                        <form method="POST" action="{{ route('requests.update', $r) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="new">
                            <button class="btn btn-sm btn-ghost">Reabrir</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
@endif
@endsection
