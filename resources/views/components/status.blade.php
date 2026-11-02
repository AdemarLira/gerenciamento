@props(['appointment'])
<span {{ $attributes->merge(['class' => 'badge status-'.$appointment->status]) }}>{{ $appointment->statusLabel() }}</span>
