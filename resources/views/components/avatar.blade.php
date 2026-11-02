@props(['initials' => '?', 'color' => null, 'size' => null])
<span {{ $attributes->merge(['class' => 'avatar'.($size ? ' avatar-'.$size : '')]) }}
    @if($color) style="background: color-mix(in srgb, {{ $color }} 18%, transparent); color: {{ $color }}; border-color: color-mix(in srgb, {{ $color }} 35%, transparent);" @endif
>{{ $initials }}</span>
