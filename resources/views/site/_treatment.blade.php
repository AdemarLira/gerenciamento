<div class="repeater-row" data-row>
    <x-site-image :path="'treatments.items.'.$i.'.image'" :value="$t['image'] ?? null" ratio="4/3"/>
    <div class="stack" style="gap:10px;flex:1;min-width:0">
        <input name="content[treatments][items][{{ $i }}][title]" value="{{ $t['title'] ?? '' }}" class="input" placeholder="Nome do tratamento">
        <textarea name="content[treatments][items][{{ $i }}][text]" class="textarea" rows="3" placeholder="Descrição curta">{{ $t['text'] ?? '' }}</textarea>
    </div>
    @include('site._row_tools')
</div>
