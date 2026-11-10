@extends('layouts.app')
@section('title', 'Editar site')
@section('heading', 'Editar site')
@section('subheading', 'Textos, fotos, cores e seções da sua página')
@section('actions')
    <a href="{{ $clinic->siteUrl() }}" target="_blank" rel="noopener" class="btn"><x-icon name="eye" size="sm"/> <span class="btn-label">Ver site</span></a>
    <button form="site-form" class="btn btn-primary"><x-icon name="check" size="sm"/> <span class="btn-label">Salvar</span></button>
@endsection

@php
    use App\Models\Site;

    /** @var Site $site */
    $n = fn (string $path) => 'content['.str_replace('.', '][', $path).']';
    $v = fn (string $path) => old('content.'.$path, $site->get($path));
    $lines = fn (string $path) => old('content.'.$path) ?? implode("\n", (array) $site->get($path));
    $tabs = [
        'identidade' => 'Identidade', 'topo' => 'Topo', 'tratamentos' => 'Tratamentos', 'sobre' => 'Sobre',
        'resultados' => 'Resultados', 'jornada' => 'Jornada', 'espaco' => 'Espaço', 'depoimentos' => 'Depoimentos',
        'duvidas' => 'Dúvidas', 'contato' => 'Contato e agendamento', 'seo' => 'Google',
    ];
@endphp

@section('content')
<form id="site-form" method="POST" action="{{ route('site.update') }}" enctype="multipart/form-data" class="site-editor">
    @csrf @method('PUT')
    <input type="hidden" name="_section" value="{{ request('secao') }}" data-section-input>

    <nav class="editor-tabs" aria-label="Seções do site">
        @foreach($tabs as $id => $label)<a href="#{{ $id }}" data-tab="{{ $id }}">{{ $label }}</a>@endforeach
    </nav>

    {{-- ===== Identidade ===== --}}
    <section class="card editor-sec" id="identidade">
        <div class="card-header">
            <div><h2>Identidade visual</h2><div class="sub">Nome, logo, cores e fontes do site.</div></div>
            <label class="switch ml-auto"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $site->is_published))> Site publicado</label>
        </div>
        <div class="card-body form-grid">
            <x-field name="content.brand.name" label="Nome no site *" class="col-3">
                <input name="{{ $n('brand.name') }}" value="{{ $v('brand.name') }}" class="input" required maxlength="80">
            </x-field>
            <x-field name="content.brand.tagline" label="Subtítulo da marca" class="col-3">
                <input name="{{ $n('brand.tagline') }}" value="{{ $v('brand.tagline') }}" class="input" maxlength="80" placeholder="Odontologia Estética">
            </x-field>
            <x-site-image path="brand.logo" :value="$v('brand.logo')" label="Logo (opcional)" ratio="1/1" class="col-2" style="max-width:200px" help="PNG com fundo transparente fica melhor."/>

            <div class="col-4">
                <label class="label">Paleta de cores</label>
                <div class="palettes">
                    @foreach(Site::PALETTES as $key => $p)
                        <label class="palette">
                            <input type="radio" name="{{ $n('theme.palette') }}" value="{{ $key }}" @checked($v('theme.palette') === $key) data-palette='@json($p)'>
                            <span class="palette-swatch"><i style="background:{{ $p['dark'] }}"></i><i style="background:{{ $p['accent'] }}"></i><i style="background:{{ $p['light'] }}"></i></span>
                            <span class="small strong">{{ $p['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="color-row mt-2">
                    @foreach(['accent' => 'Destaque', 'dark' => 'Fundo escuro', 'light' => 'Fundo claro'] as $key => $label)
                        <label class="color-pick">
                            <input type="color" value="{{ $v("theme.$key") }}" data-color-for="{{ $key }}">
                            <input type="text" name="{{ $n("theme.$key") }}" value="{{ $v("theme.$key") }}" class="input" maxlength="7" data-color-hex="{{ $key }}">
                            <span class="small muted">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <x-field name="content.theme.font" label="Tipografia" class="mt-2">
                    <select name="{{ $n('theme.font') }}" class="select">
                        @foreach(Site::FONTS as $key => $f)<option value="{{ $key }}" @selected($v('theme.font') === $key)>{{ $f['label'] }}</option>@endforeach
                    </select>
                </x-field>
            </div>
        </div>
    </section>

    {{-- ===== Topo ===== --}}
    <section class="card editor-sec" id="topo">
        <div class="card-header"><div><h2>Topo da página</h2><div class="sub">A primeira coisa que o paciente vê.</div></div></div>
        <div class="card-body form-grid">
            <div class="col-4 form-grid" style="grid-template-columns:repeat(4,minmax(0,1fr))">
                <x-field name="content.hero.eyebrow" label="Linha de apoio" class="col-4">
                    <input name="{{ $n('hero.eyebrow') }}" value="{{ $v('hero.eyebrow') }}" class="input" maxlength="90">
                </x-field>
                <x-field name="content.hero.title" label="Título" class="col-2">
                    <input name="{{ $n('hero.title') }}" value="{{ $v('hero.title') }}" class="input" maxlength="90">
                </x-field>
                <x-field name="content.hero.highlight" label="Destaque em itálico" class="col-2">
                    <input name="{{ $n('hero.highlight') }}" value="{{ $v('hero.highlight') }}" class="input" maxlength="60">
                </x-field>
                <x-field name="content.hero.subtitle" label="Texto" class="col-4">
                    <textarea name="{{ $n('hero.subtitle') }}" class="textarea" rows="3" maxlength="400">{{ $v('hero.subtitle') }}</textarea>
                </x-field>
                <x-field name="content.hero.cta" label="Botão principal" class="col-2">
                    <input name="{{ $n('hero.cta') }}" value="{{ $v('hero.cta') }}" class="input" maxlength="40">
                </x-field>
                <x-field name="content.hero.cta_secondary" label="Link secundário" class="col-2">
                    <input name="{{ $n('hero.cta_secondary') }}" value="{{ $v('hero.cta_secondary') }}" class="input" maxlength="40">
                </x-field>
                <x-field name="content.hero.badges" label="Diferenciais (um por linha)" class="col-2">
                    <textarea name="{{ $n('hero.badges') }}" class="textarea" rows="3">{{ $lines('hero.badges') }}</textarea>
                </x-field>
                <x-field name="content.marquee" label="Faixa em movimento (um por linha)" class="col-2">
                    <textarea name="{{ $n('marquee') }}" class="textarea" rows="3">{{ $lines('marquee') }}</textarea>
                </x-field>
            </div>
            <x-site-image path="hero.image" :value="$v('hero.image')" label="Foto principal" ratio="4/5" class="col-2" help="Vertical, de preferência 1200×1600 px."/>
        </div>
    </section>

    {{-- ===== Tratamentos ===== --}}
    <section class="card editor-sec" id="tratamentos">
        <div class="card-header">
            <div><h2>Tratamentos</h2><div class="sub">Cards que se expandem ao passar o mouse.</div></div>
            @include('site._toggle', ['key' => 'treatments'])
        </div>
        <div class="card-body form-grid">
            <x-field name="content.treatments.title" label="Etiqueta" class="col-2">
                <input name="{{ $n('treatments.title') }}" value="{{ $v('treatments.title') }}" class="input">
            </x-field>
            <x-field name="content.treatments.subtitle" label="Título da seção" class="col-4">
                <input name="{{ $n('treatments.subtitle') }}" value="{{ $v('treatments.subtitle') }}" class="input">
            </x-field>
            <div class="col-6" data-repeater>
                <div class="repeater-list">
                    @foreach($site->list('treatments.items') as $i => $t)
                        @include('site._treatment', ['i' => $i, 't' => $t])
                    @endforeach
                </div>
                <template>@include('site._treatment', ['i' => 'IDX', 't' => []])</template>
                <button type="button" class="btn btn-sm mt-2" data-add><x-icon name="plus" size="sm"/> Adicionar tratamento</button>
            </div>
        </div>
    </section>

    {{-- ===== Sobre ===== --}}
    <section class="card editor-sec" id="sobre">
        <div class="card-header">
            <div><h2>Sobre a profissional</h2><div class="sub">Apresentação, foto e números.</div></div>
            @include('site._toggle', ['key' => 'about'])
        </div>
        <div class="card-body form-grid">
            <div class="col-4 form-grid" style="grid-template-columns:repeat(4,minmax(0,1fr))">
                <x-field name="content.about.title" label="Título" class="col-4">
                    <input name="{{ $n('about.title') }}" value="{{ $v('about.title') }}" class="input">
                </x-field>
                <x-field name="content.about.name" label="Nome" class="col-2">
                    <input name="{{ $n('about.name') }}" value="{{ $v('about.name') }}" class="input">
                </x-field>
                <x-field name="content.about.registry" label="Registro (CRO)" class="col-2">
                    <input name="{{ $n('about.registry') }}" value="{{ $v('about.registry') }}" class="input">
                </x-field>
                <x-field name="content.about.role" label="Especialidade" class="col-4">
                    <input name="{{ $n('about.role') }}" value="{{ $v('about.role') }}" class="input">
                </x-field>
                <x-field name="content.about.text" label="Texto (deixe uma linha em branco entre parágrafos)" class="col-4">
                    <textarea name="{{ $n('about.text') }}" class="textarea" rows="6">{{ $v('about.text') }}</textarea>
                </x-field>
                <div class="col-4">
                    <label class="label">Números</label>
                    <div class="stats-edit">
                        @for($i = 0; $i < 3; $i++)
                            <div class="row" style="gap:8px">
                                <input name="{{ $n("about.stats.$i.value") }}" value="{{ $v("about.stats.$i.value") }}" class="input" style="max-width:110px" placeholder="15+">
                                <input name="{{ $n("about.stats.$i.label") }}" value="{{ $v("about.stats.$i.label") }}" class="input" placeholder="anos de experiência">
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            <x-site-image path="about.image" :value="$v('about.image')" label="Foto da profissional" ratio="4/5" class="col-2"/>
        </div>
    </section>

    {{-- ===== Resultados ===== --}}
    <section class="card editor-sec" id="resultados">
        <div class="card-header">
            <div><h2>Antes e depois</h2><div class="sub">O paciente arrasta para comparar.</div></div>
            @include('site._toggle', ['key' => 'results'])
        </div>
        <div class="card-body form-grid">
            <div class="alert alert-error col-6" style="margin:0">
                <x-icon name="alert"/>
                <div>Publique apenas casos reais com autorização por escrito do paciente e siga as normas de publicidade do CFO. As imagens de exemplo são ilustrativas.</div>
            </div>
            <x-field name="content.results.subtitle" label="Texto da seção" class="col-6">
                <input name="{{ $n('results.subtitle') }}" value="{{ $v('results.subtitle') }}" class="input">
            </x-field>
            <div class="col-6" data-repeater>
                <div class="repeater-list">
                    @foreach($site->list('results.items') as $i => $r)
                        @include('site._result', ['i' => $i, 'r' => $r])
                    @endforeach
                </div>
                <template>@include('site._result', ['i' => 'IDX', 'r' => []])</template>
                <button type="button" class="btn btn-sm mt-2" data-add><x-icon name="plus" size="sm"/> Adicionar caso</button>
            </div>
        </div>
    </section>

    {{-- ===== Jornada ===== --}}
    <section class="card editor-sec" id="jornada">
        <div class="card-header">
            <div><h2>Jornada do paciente</h2><div class="sub">Linha do tempo com as etapas do atendimento.</div></div>
            @include('site._toggle', ['key' => 'journey'])
        </div>
        <div class="card-body form-grid">
            <x-field name="content.journey.title" label="Título" class="col-6">
                <input name="{{ $n('journey.title') }}" value="{{ $v('journey.title') }}" class="input">
            </x-field>
            <div class="col-6" data-repeater>
                <div class="repeater-list">
                    @foreach($site->list('journey.steps') as $i => $st)
                        @include('site._pair', ['i' => $i, 'base' => 'journey.steps', 'a' => ['title', 'Etapa', $st['title'] ?? ''], 'b' => ['text', 'Descrição', $st['text'] ?? '']])
                    @endforeach
                </div>
                <template>@include('site._pair', ['i' => 'IDX', 'base' => 'journey.steps', 'a' => ['title', 'Etapa', ''], 'b' => ['text', 'Descrição', '']])</template>
                <button type="button" class="btn btn-sm mt-2" data-add><x-icon name="plus" size="sm"/> Adicionar etapa</button>
            </div>
        </div>
    </section>

    {{-- ===== Espaço ===== --}}
    <section class="card editor-sec" id="espaco">
        <div class="card-header">
            <div><h2>O consultório</h2><div class="sub">Galeria com 4 fotos (a primeira fica maior).</div></div>
            @include('site._toggle', ['key' => 'space'])
        </div>
        <div class="card-body form-grid">
            <x-field name="content.space.title" label="Título" class="col-3">
                <input name="{{ $n('space.title') }}" value="{{ $v('space.title') }}" class="input">
            </x-field>
            <x-field name="content.space.text" label="Texto" class="col-3">
                <input name="{{ $n('space.text') }}" value="{{ $v('space.text') }}" class="input">
            </x-field>
            <div class="col-6 gallery-edit">
                @for($i = 0; $i < 4; $i++)
                    <x-site-image :path="'space.images.'.$i" :value="$site->get('space.images')[$i] ?? null" :label="'Foto '.($i + 1)" ratio="4/3"/>
                @endfor
            </div>
        </div>
    </section>

    {{-- ===== Depoimentos ===== --}}
    <section class="card editor-sec" id="depoimentos">
        <div class="card-header">
            <div><h2>Depoimentos</h2><div class="sub">Cards que deslizam continuamente. Os de exemplo devem ser trocados por depoimentos reais.</div></div>
            @include('site._toggle', ['key' => 'testimonials'])
        </div>
        <div class="card-body form-grid">
            <x-field name="content.testimonials.title" label="Título" class="col-6">
                <input name="{{ $n('testimonials.title') }}" value="{{ $v('testimonials.title') }}" class="input">
            </x-field>
            <div class="col-6" data-repeater>
                <div class="repeater-list">
                    @foreach($site->list('testimonials.items') as $i => $t)
                        @include('site._testimonial', ['i' => $i, 't' => $t])
                    @endforeach
                </div>
                <template>@include('site._testimonial', ['i' => 'IDX', 't' => []])</template>
                <button type="button" class="btn btn-sm mt-2" data-add><x-icon name="plus" size="sm"/> Adicionar depoimento</button>
            </div>
        </div>
    </section>

    {{-- ===== Dúvidas ===== --}}
    <section class="card editor-sec" id="duvidas">
        <div class="card-header">
            <div><h2>Perguntas frequentes</h2></div>
            @include('site._toggle', ['key' => 'faq'])
        </div>
        <div class="card-body form-grid">
            <x-field name="content.faq.title" label="Título" class="col-6">
                <input name="{{ $n('faq.title') }}" value="{{ $v('faq.title') }}" class="input">
            </x-field>
            <div class="col-6" data-repeater>
                <div class="repeater-list">
                    @foreach($site->list('faq.items') as $i => $f)
                        @include('site._pair', ['i' => $i, 'base' => 'faq.items', 'a' => ['q', 'Pergunta', $f['q'] ?? ''], 'b' => ['a', 'Resposta', $f['a'] ?? '']])
                    @endforeach
                </div>
                <template>@include('site._pair', ['i' => 'IDX', 'base' => 'faq.items', 'a' => ['q', 'Pergunta', ''], 'b' => ['a', 'Resposta', '']])</template>
                <button type="button" class="btn btn-sm mt-2" data-add><x-icon name="plus" size="sm"/> Adicionar pergunta</button>
            </div>
        </div>
    </section>

    {{-- ===== Contato ===== --}}
    <section class="card editor-sec" id="contato">
        <div class="card-header"><div><h2>Contato e agendamento</h2><div class="sub">Formulário "Agendar avaliação" — os pedidos chegam em Solicitações.</div></div></div>
        <div class="card-body form-grid">
            <x-field name="content.cta.title" label="Título do agendamento" class="col-3">
                <input name="{{ $n('cta.title') }}" value="{{ $v('cta.title') }}" class="input">
            </x-field>
            <x-field name="content.cta.text" label="Texto" class="col-3">
                <input name="{{ $n('cta.text') }}" value="{{ $v('cta.text') }}" class="input">
            </x-field>
            <x-field name="content.contact.whatsapp" label="WhatsApp (com DDD)" class="col-2">
                <input name="{{ $n('contact.whatsapp') }}" value="{{ $v('contact.whatsapp') }}" class="input" data-mask="phone">
            </x-field>
            <x-field name="content.contact.phone" label="Telefone fixo" class="col-2">
                <input name="{{ $n('contact.phone') }}" value="{{ $v('contact.phone') }}" class="input">
            </x-field>
            <x-field name="content.contact.email" label="E-mail" class="col-2">
                <input name="{{ $n('contact.email') }}" value="{{ $v('contact.email') }}" class="input">
            </x-field>
            <x-field name="content.contact.address" label="Endereço" class="col-4">
                <input name="{{ $n('contact.address') }}" value="{{ $v('contact.address') }}" class="input">
            </x-field>
            <x-field name="content.contact.instagram" label="Instagram (@)" class="col-2">
                <input name="{{ $n('contact.instagram') }}" value="{{ $v('contact.instagram') }}" class="input" placeholder="seuperfil">
            </x-field>
            <x-field name="content.contact.hours" label="Horário de atendimento" class="col-3">
                <input name="{{ $n('contact.hours') }}" value="{{ $v('contact.hours') }}" class="input">
            </x-field>
            <x-field name="content.contact.map_url" label="Link do Google Maps" class="col-3">
                <input name="{{ $n('contact.map_url') }}" value="{{ $v('contact.map_url') }}" class="input" placeholder="https://maps.google.com/...">
            </x-field>
            <x-field name="content.contact.procedures" label="Opções de tratamento no formulário (uma por linha)" class="col-6">
                <textarea name="{{ $n('contact.procedures') }}" class="textarea" rows="4">{{ $lines('contact.procedures') }}</textarea>
            </x-field>
        </div>
    </section>

    {{-- ===== SEO ===== --}}
    <section class="card editor-sec" id="seo">
        <div class="card-header"><div><h2>Como aparece no Google</h2></div></div>
        <div class="card-body form-grid">
            <x-field name="content.seo.title" label="Título da página" class="col-6">
                <input name="{{ $n('seo.title') }}" value="{{ $v('seo.title') }}" class="input" maxlength="70">
            </x-field>
            <x-field name="content.seo.description" label="Descrição" class="col-6">
                <textarea name="{{ $n('seo.description') }}" class="textarea" rows="2" maxlength="170">{{ $v('seo.description') }}</textarea>
            </x-field>
        </div>
        <div class="form-actions">
            <button type="submit" form="site-reset" class="btn btn-ghost btn-danger mr-auto">Restaurar modelo padrão</button>
            <a href="{{ $clinic->siteUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost">Ver site</a>
            <button class="btn btn-primary btn-lg"><x-icon name="check" size="sm"/> Salvar site</button>
        </div>
    </section>
</form>

<form id="site-reset" method="POST" action="{{ route('site.reset') }}" data-confirm="Restaurar o modelo padrão? Todos os textos e fotos do site voltam ao exemplo inicial.">@csrf</form>

@push('scripts')<script src="{{ asset('js/site-editor.js') }}?v=1"></script>@endpush

<div class="save-bar" data-save-bar hidden>
    <span>Alterações não salvas</span>
    <button form="site-form" class="btn btn-primary btn-sm"><x-icon name="check" size="sm"/> Salvar</button>
</div>
@endsection
