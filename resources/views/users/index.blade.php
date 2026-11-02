@extends('layouts.app')
@section('title', 'Equipe')
@section('heading', 'Equipe')
@section('subheading', 'Quem tem acesso ao sistema')
@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary"><x-icon name="user-plus" size="sm"/> <span class="btn-label">Novo usuário</span></a>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Usuário</th><th>Perfil</th><th class="hide-sm">Desde</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>
                        <div class="row">
                            <x-avatar :initials="$u->initials()"/>
                            <div style="min-width:0">
                                <div class="strong">{{ $u->name }} @if($u->is(auth()->user()))<span class="muted small">(você)</span>@endif</div>
                                <div class="muted small">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge no-dot" @if($u->isAdmin()) style="background:rgba(var(--accent-rgb),.15);color:var(--accent)" @endif>{{ $u->roleLabel() }}</span></td>
                    <td class="hide-sm muted">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="actions">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-ghost btn-icon" title="Editar"><x-icon name="pencil" size="sm"/></a>
                        @unless($u->is(auth()->user()))
                            <form method="POST" action="{{ route('users.destroy', $u) }}" style="display:inline" data-confirm="Remover o acesso de {{ $u->name }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-ghost btn-icon btn-danger" title="Remover"><x-icon name="trash" size="sm"/></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-3 mt-3">
    <div class="card card-body"><div class="strong">Administrador</div><p class="muted small mt-1">Acesso total: agenda, pacientes, profissionais, equipe e personalização.</p></div>
    <div class="card card-body"><div class="strong">Profissional</div><p class="muted small mt-1">Agenda e pacientes. Ideal para médicos e terapeutas.</p></div>
    <div class="card card-body"><div class="strong">Recepção</div><p class="muted small mt-1">Agenda e pacientes, para marcar e confirmar consultas.</p></div>
</div>
@endsection
