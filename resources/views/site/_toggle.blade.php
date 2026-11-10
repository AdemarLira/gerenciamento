{{-- Liga/desliga uma seção do site --}}
<label class="switch ml-auto">
    <input type="hidden" name="content[sections][{{ $key }}]" value="0">
    <input type="checkbox" name="content[sections][{{ $key }}]" value="1" @checked($site->enabled($key))> Mostrar no site
</label>
