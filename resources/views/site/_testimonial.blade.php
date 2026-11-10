<div class="repeater-row" data-row>
    <div class="stack" style="gap:10px;flex:1;min-width:0">
        <div class="row" style="gap:10px">
            <input name="content[testimonials][items][{{ $i }}][name]" value="{{ $t['name'] ?? '' }}" class="input" placeholder="Nome (ex.: Mariana C.)">
            <input name="content[testimonials][items][{{ $i }}][detail]" value="{{ $t['detail'] ?? '' }}" class="input" placeholder="Tratamento">
        </div>
        <textarea name="content[testimonials][items][{{ $i }}][text]" class="textarea" rows="2" placeholder="Depoimento">{{ $t['text'] ?? '' }}</textarea>
    </div>
    @include('site._row_tools')
</div>
