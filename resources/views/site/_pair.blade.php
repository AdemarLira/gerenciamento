{{-- Linha com dois campos: título curto + texto (etapas, perguntas) --}}
@php $name = fn ($f) => 'content['.str_replace('.', '][', $base).']['.$i.']['.$f.']'; @endphp
<div class="repeater-row" data-row>
    <div class="stack" style="gap:10px;flex:1;min-width:0">
        <input name="{{ $name($a[0]) }}" value="{{ $a[2] }}" class="input" placeholder="{{ $a[1] }}">
        <textarea name="{{ $name($b[0]) }}" class="textarea" rows="2" placeholder="{{ $b[1] }}">{{ $b[2] }}</textarea>
    </div>
    @include('site._row_tools')
</div>
