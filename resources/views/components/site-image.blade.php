@props(['path', 'value' => null, 'label' => null, 'ratio' => '4/3', 'help' => null])
@php
    $name = 'content['.str_replace('.', '][', $path).']';
    $upload = 'uploads['.str_replace('.', '__', $path).']';
    $url = \App\Models\Site::imageUrl($value, 600);
@endphp
<div {{ $attributes->merge(['class' => 'img-field']) }} data-img-field>
    @if($label)<label class="label">{{ $label }}</label>@endif
    <div class="img-drop" style="--ratio: {{ $ratio }}">
        <img src="{{ $url }}" alt="" data-img-preview @if(! $url) hidden @endif>
        <span class="img-empty" @if($url) hidden @endif><x-icon name="upload"/> Sem imagem</span>
        <div class="img-actions">
            <label class="btn btn-sm">
                <x-icon name="upload" size="sm"/> {{ $url ? 'Trocar' : 'Enviar' }}
                <input type="file" name="{{ $upload }}" accept="image/*" hidden data-img-input>
            </label>
            <button type="button" class="btn btn-sm btn-ghost btn-icon" title="Remover imagem" aria-label="Remover imagem" data-img-remove @if(! $url) hidden @endif><x-icon name="trash" size="sm"/></button>
        </div>
    </div>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-img-value>
    @if($help)<div class="help">{{ $help }}</div>@endif
</div>
