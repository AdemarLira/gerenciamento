@extends('layouts.app')
@section('title', 'Profissionais')
@section('heading', 'Profissionais')
@section('subheading', 'Quem atende na clínica')
@section('actions')
    <a href="{{ route('professionals.create') }}" class="btn btn-primary"><x-icon name="plus" size="sm"/> <span class="btn-label">Novo profissional</span></a>
@endsection

@section('content')
@if($professionals->isEmpty())
    <div class="card">
        <div class="empty">
            <div class="ico"><x-icon name="stethoscope"/></div>
            <h3>Nenhum profissional cadastrado</h3>
            <p>Cadastre médicos, dentistas, psicólogos e demais profissionais que atendem na clínica.</p>
            <a href="{{ route('professionals.create') }}" class="btn btn-primary mt-2"><x-icon name="plus" size="sm"/> Novo profissional</a>
        </div>
    </div>
@else
<div class="grid grid-3">
    @foreach($professionals as $p)
        <div class="card" @if(! $p->is_active) style="opacity:.55" @endif>
            <div class="card-body">
                <div class="row">
                    <x-avatar :initials="$p->initials()" :color="$p->color" size="lg" style="width:52px;height:52px;font-size:17px"/>
                    <div style="min-width:0;flex:1">
                        <div class="strong truncate" style="font-size:15px">{{ $p->name }}</div>
                        <div class="muted small truncate">{{ $p->specialty ?? 'Sem especialidade' }}</div>
                        @if($p->registry)<div class="muted small">{{ $p->registry }}</div>@endif
                    </div>
                    @unless($p->is_active)<span class="badge no-dot">Inativo</span>@endunless
                </div>
                <div class="row between mt-3">
                    <span class="muted small"><strong class="text-2">{{ $p->upcoming_count }}</strong> {{ \Illuminate\Support\Str::plural('consulta', $p->upcoming_count) }} futuras</span>
                    <div class="row" style="gap:4px">
                        <a href="{{ route('appointments.index', ['professional' => $p->id, 'view' => 'week']) }}" class="btn btn-sm btn-ghost btn-icon" title="Ver agenda"><x-icon name="calendar" size="sm"/></a>
                        <a href="{{ route('professionals.edit', $p) }}" class="btn btn-sm btn-ghost btn-icon" title="Editar"><x-icon name="pencil" size="sm"/></a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection
