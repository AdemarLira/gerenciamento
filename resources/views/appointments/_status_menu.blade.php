{{-- Badge de status com menu para alterar rapidamente --}}
<div class="dropdown">
    <button type="button" class="badge status-{{ $appointment->status }}" data-dropdown style="border:0;cursor:pointer;font-family:inherit">
        {{ $appointment->statusLabel() }} <x-icon name="chevron-down" size="sm" style="width:12px;height:12px"/>
    </button>
    <div class="dropdown-menu">
        @foreach(\App\Models\Appointment::STATUSES as $key => $label)
            @continue($key === $appointment->status)
            <form method="POST" action="{{ route('appointments.status', $appointment) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $key }}">
                <button><span class="badge status-{{ $key }}" style="height:auto;padding:0;background:none">{{ $label }}</span></button>
            </form>
        @endforeach
        <hr>
        <a href="{{ route('appointments.edit', $appointment) }}"><x-icon name="pencil" size="sm"/> Editar consulta</a>
    </div>
</div>
