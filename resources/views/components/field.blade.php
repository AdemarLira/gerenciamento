@props(['name', 'label' => null, 'help' => null, 'class' => 'col-6'])
<div class="field {{ $class }}">
    @if($label)<label for="{{ $name }}">{{ $label }}</label>@endif
    {{ $slot }}
    @error($name)<div class="error">{{ $message }}</div>@enderror
    @if($help && ! $errors->has($name))<div class="help">{{ $help }}</div>@endif
</div>
