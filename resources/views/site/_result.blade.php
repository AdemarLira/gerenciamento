<div class="repeater-row" data-row>
    <x-site-image :path="'results.items.'.$i.'.before'" :value="$r['before'] ?? null" label="Antes" ratio="4/3"/>
    <x-site-image :path="'results.items.'.$i.'.after'" :value="$r['after'] ?? null" label="Depois" ratio="4/3"/>
    <div class="stack" style="gap:8px;flex:1;min-width:0">
        <label class="label">Legenda</label>
        <input name="content[results][items][{{ $i }}][caption]" value="{{ $r['caption'] ?? '' }}" class="input" placeholder="Ex.: Lentes de contato em porcelana">
    </div>
    @include('site._row_tools')
</div>
