@php
    use App\Models\BookingRequest;
    use App\Models\Site;
    use App\Support\WhatsApp;

    /** @var Site $site */
    $s = $site;
    $font = $s->font();
    $brand = $s->get('brand.name');
    $waNumber = $s->get('contact.whatsapp') ?: $clinic->phone;
    $wa = WhatsApp::link($waNumber, 'Olá! Vim pelo site e gostaria de agendar uma avaliação.');
    $logo = $s->image('brand.logo', 400);
    $sent = session('site_sent');
    $instagram = ltrim((string) $s->get('contact.instagram'), '@');
    $words = preg_split('/\s+/', trim($s->get('hero.title')));
    $hl = preg_split('/\s+/', trim((string) $s->get('hero.highlight')));
    $nav = collect([
        'treatments' => ['#tratamentos', 'Tratamentos'],
        'about' => ['#sobre', 'Sobre'],
        'results' => ['#resultados', 'Resultados'],
        'journey' => ['#jornada', 'Jornada'],
        'faq' => ['#duvidas', 'Dúvidas'],
    ])->filter(fn ($v, $k) => $s->enabled($k));
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $s->get('seo.title') }}</title>
    <meta name="description" content="{{ $s->get('seo.description') }}">
    <meta property="og:title" content="{{ $s->get('seo.title') }}">
    <meta property="og:description" content="{{ $s->get('seo.description') }}">
    <meta property="og:image" content="{{ $s->image('hero.image', 1200) }}">
    <meta name="theme-color" content="{{ $s->get('theme.dark') }}">
    @if($logo)<link rel="icon" href="{{ $logo }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.googleapis.com/css2?{{ $font['url'] }}&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v=1">
    <style>:root{ {!! $s->themeCss() !!} }</style>
    <script>document.documentElement.classList.add('js')</script>
</head>
<body class="st">

@auth
    @if(auth()->user()->clinic_id === $clinic->id)
        <div class="st-adminbar">
            @unless($s->is_published)<span class="st-pill">Site não publicado — só a equipe vê</span>@endunless
            <a href="{{ route('site.edit') }}">Editar site</a>
            <a href="{{ route('requests.index') }}">Solicitações</a>
            <a href="{{ route('dashboard') }}">Painel</a>
        </div>
    @endif
@endauth

{{-- ============ NAV FLUTUANTE ============ --}}
<header class="st-nav" data-nav>
    <a href="#inicio" class="st-brand">
        @if($logo)<img src="{{ $logo }}" alt="{{ $brand }}">@endif
        <span><b>{{ $brand }}</b><small>{{ $s->get('brand.tagline') }}</small></span>
    </a>
    <nav class="st-links" aria-label="Seções">
        @foreach($nav as [$href, $label])<a href="{{ $href }}">{{ $label }}</a>@endforeach
    </nav>
    <a href="#agendar" class="st-btn st-btn-glow st-nav-cta"><span>{{ $s->get('hero.cta') }}</span></a>
    <button class="st-burger" type="button" aria-label="Abrir menu" data-burger><i></i><i></i></button>
</header>

<main>
    {{-- ============ HERO ============ --}}
    <section class="st-hero" id="inicio">
        <div class="st-spotlight" aria-hidden="true"></div>
        <div class="st-wrap st-hero-in">
            <div class="st-hero-copy">
                <span class="st-eyebrow" data-reveal>{{ $s->get('hero.eyebrow') }}</span>
                <h1 class="st-h1" data-words>
                    @foreach($words as $w)<span class="w">{{ $w }}</span> @endforeach
                    @if($s->get('hero.highlight'))<em>@foreach($hl as $w)<span class="w">{{ $w }}</span> @endforeach</em>@endif
                </h1>
                <p class="st-lead" data-reveal style="--d:.5s">{{ $s->get('hero.subtitle') }}</p>
                <div class="st-cta" data-reveal style="--d:.65s">
                    <a href="#agendar" class="st-btn st-btn-dark st-btn-lg st-moving"><span>{{ $s->get('hero.cta') }} <x-icon name="arrow-right" size="sm"/></span></a>
                    @if($s->enabled('treatments'))
                        <a href="#tratamentos" class="st-link">{{ $s->get('hero.cta_secondary') }}</a>
                    @endif
                </div>
                <ul class="st-badges" data-reveal style="--d:.8s">
                    @foreach($s->list('hero.badges') as $b)<li><span class="dot"></span>{{ $b }}</li>@endforeach
                </ul>
            </div>
            <div class="st-hero-art" data-reveal style="--d:.2s">
                <div class="st-arch" data-parallax>
                    @if($img = $s->image('hero.image', 1400))<img src="{{ $img }}" alt="" fetchpriority="high">@endif
                </div>
                <div class="st-glass st-float-a">
                    <span class="st-glass-ico"><x-icon name="sparkles" size="sm"/></span>
                    <div><small>Planejamento</small><b>Sorriso digital 3D</b></div>
                </div>
                @if($stat = ($s->list('about.stats')[0] ?? null))
                    <div class="st-glass st-float-b">
                        <b>{{ $stat['value'] ?? '' }}</b><small>{{ $stat['label'] ?? '' }}</small>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============ FAIXA INFINITA ============ --}}
    @if($marquee = $s->list('marquee'))
        <div class="st-marquee" aria-hidden="true">
            <div class="st-marquee-track">
                @for($i = 0; $i < 2; $i++)
                    @foreach($marquee as $m)<span>{{ $m }}</span><i>✦</i>@endforeach
                @endfor
            </div>
        </div>
    @endif

    {{-- ============ TRATAMENTOS (cards expansíveis) ============ --}}
    @if($s->enabled('treatments') && ($treatments = $s->list('treatments.items')))
        <section class="st-section" id="tratamentos">
            <div class="st-wrap">
                <div class="st-head">
                    <span class="st-kicker" data-reveal>{{ $s->get('treatments.title') }}</span>
                    <h2 class="st-h2" data-reveal>{{ $s->get('treatments.subtitle') }}</h2>
                </div>
                <div class="st-expand" data-reveal>
                    @foreach($treatments as $i => $t)
                        <article @class(['st-x', 'is-open' => $i === 0]) tabindex="0">
                            @if($img = Site::imageUrl($t['image'] ?? null, 1000))<img src="{{ $img }}" alt="" loading="lazy">@endif
                            <div class="st-x-body">
                                <span class="st-x-n">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $t['title'] ?? '' }}</h3>
                                <p>{{ $t['text'] ?? '' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ SOBRE ============ --}}
    @if($s->enabled('about'))
        <section class="st-section st-about" id="sobre">
            <div class="st-wrap st-about-in">
                <div class="st-tilt" data-tilt data-reveal>
                    <div class="st-tilt-card">
                        @if($img = $s->image('about.image', 1000))<img src="{{ $img }}" alt="{{ $s->get('about.name') }}" loading="lazy">@endif
                        <div class="st-tilt-tag"><b>{{ $s->get('about.name') }}</b><small>{{ $s->get('about.registry') }}</small></div>
                    </div>
                </div>
                <div>
                    <span class="st-kicker" data-reveal>Sobre</span>
                    <h2 class="st-h2" data-reveal>{{ $s->get('about.title') }}</h2>
                    <div class="st-prose" data-reveal>
                        @foreach(preg_split('/\n\s*\n/', trim((string) $s->get('about.text'))) as $p)<p>{{ $p }}</p>@endforeach
                    </div>
                    <div class="st-sign" data-reveal>
                        <span class="st-sign-name">{{ $s->get('about.name') }}</span>
                        <small>{{ $s->get('about.role') }}</small>
                    </div>
                    @if($stats = $s->list('about.stats'))
                        <dl class="st-stats" data-reveal>
                            @foreach($stats as $st)
                                <div><dt data-count>{{ $st['value'] ?? '' }}</dt><dd>{{ $st['label'] ?? '' }}</dd></div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ============ RESULTADOS (antes e depois) ============ --}}
    @if($s->enabled('results') && ($results = $s->list('results.items')))
        <section class="st-section st-results" id="resultados">
            <div class="st-wrap">
                <div class="st-head">
                    <span class="st-kicker" data-reveal>{{ $s->get('results.title') }}</span>
                    <h2 class="st-h2" data-reveal>Transformações que respeitam a sua essência.</h2>
                    <p class="st-sub" data-reveal>{{ $s->get('results.subtitle') }}</p>
                </div>
                <div class="st-compare-grid">
                    @foreach($results as $r)
                        <figure class="st-compare-card" data-reveal>
                            <div class="st-compare" data-compare style="--pos:50%">
                                <img src="{{ Site::imageUrl($r['after'] ?? null, 1100) }}" alt="Depois" loading="lazy">
                                <div class="st-compare-before"><img src="{{ Site::imageUrl($r['before'] ?? null, 1100) }}" alt="Antes" loading="lazy"></div>
                                <span class="st-compare-label l">Antes</span><span class="st-compare-label r">Depois</span>
                                <div class="st-compare-handle"><span><x-icon name="chevron-left" size="sm"/><x-icon name="chevron-right" size="sm"/></span></div>
                                <input type="range" min="0" max="100" value="50" aria-label="Comparar antes e depois">
                            </div>
                            @if(! empty($r['caption']))<figcaption>{{ $r['caption'] }}</figcaption>@endif
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ JORNADA (linha do tempo) ============ --}}
    @if($s->enabled('journey') && ($steps = $s->list('journey.steps')))
        <section class="st-section st-journey" id="jornada">
            <div class="st-wrap st-journey-in">
                <div class="st-journey-head">
                    <span class="st-kicker" data-reveal>Experiência</span>
                    <h2 class="st-h2" data-reveal>{{ $s->get('journey.title') }}</h2>
                    <a href="#agendar" class="st-btn st-btn-dark" data-reveal><span>{{ $s->get('hero.cta') }}</span></a>
                </div>
                <ol class="st-timeline" data-timeline>
                    <span class="st-beam" aria-hidden="true"><i></i></span>
                    @foreach($steps as $i => $step)
                        <li data-reveal>
                            <span class="st-tl-dot">{{ $i + 1 }}</span>
                            <h3>{{ $step['title'] ?? '' }}</h3>
                            <p>{{ $step['text'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ============ ESPAÇO (focus cards) ============ --}}
    @if($s->enabled('space') && ($space = $s->list('space.images')))
        <section class="st-section" id="espaco">
            <div class="st-wrap">
                <div class="st-head st-head-split">
                    <h2 class="st-h2" data-reveal>{{ $s->get('space.title') }}</h2>
                    <p class="st-sub" data-reveal>{{ $s->get('space.text') }}</p>
                </div>
                <div class="st-focus" data-reveal>
                    @foreach($space as $img)
                        <div class="st-focus-card"><img src="{{ Site::imageUrl($img, 1100) }}" alt="" loading="lazy"></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ DEPOIMENTOS (cards em movimento) ============ --}}
    @if($s->enabled('testimonials') && ($testimonials = $s->list('testimonials.items')))
        <section class="st-section st-dark st-testi">
            <div class="st-wrap">
                <div class="st-head">
                    <span class="st-kicker" data-reveal>Depoimentos</span>
                    <h2 class="st-h2" data-reveal>{{ $s->get('testimonials.title') }}</h2>
                </div>
            </div>
            <div class="st-cards" data-reveal>
                <div class="st-cards-track" style="--dur: {{ max(30, count($testimonials) * 12) }}s">
                    @for($i = 0; $i < 2; $i++)
                        @foreach($testimonials as $t)
                            <blockquote class="st-quote" @if($i) aria-hidden="true" @endif>
                                <span class="st-stars">★★★★★</span>
                                <p>“{{ $t['text'] ?? '' }}”</p>
                                <footer><b>{{ $t['name'] ?? '' }}</b>@if(! empty($t['detail']))<small>{{ $t['detail'] }}</small>@endif</footer>
                            </blockquote>
                        @endforeach
                    @endfor
                </div>
            </div>
        </section>
    @endif

    {{-- ============ FAQ ============ --}}
    @if($s->enabled('faq') && ($faq = $s->list('faq.items')))
        <section class="st-section" id="duvidas">
            <div class="st-wrap st-faq-in">
                <div>
                    <span class="st-kicker" data-reveal>Dúvidas</span>
                    <h2 class="st-h2" data-reveal>{{ $s->get('faq.title') }}</h2>
                    @if($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="st-link mt" data-reveal>Pergunte pelo WhatsApp</a>@endif
                </div>
                <div class="st-faq" data-reveal>
                    @foreach($faq as $f)
                        <details @if($loop->first) open @endif>
                            <summary>{{ $f['q'] ?? '' }}<span class="st-plus" aria-hidden="true"></span></summary>
                            <p>{{ $f['a'] ?? '' }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============ AGENDAR (lamp + formulário) ============ --}}
    <section class="st-section st-dark st-book" id="agendar">
        <div class="st-lamp" aria-hidden="true"><span></span></div>
        <div class="st-wrap st-book-in">
            <div class="st-book-copy">
                <span class="st-kicker" data-reveal>Agendamento</span>
                <h2 class="st-h2" data-reveal>{{ $s->get('cta.title') }}</h2>
                <p class="st-sub" data-reveal>{{ $s->get('cta.text') }}</p>
                <ul class="st-contact" data-reveal>
                    @if($s->get('contact.address'))<li><x-icon name="map-pin" size="sm"/><a href="{{ $s->get('contact.map_url') ?: 'https://maps.google.com/?q='.urlencode($s->get('contact.address')) }}" target="_blank" rel="noopener">{{ $s->get('contact.address') }}</a></li>@endif
                    @if($s->get('contact.hours'))<li><x-icon name="clock" size="sm"/><span>{{ $s->get('contact.hours') }}</span></li>@endif
                    @if($s->get('contact.phone'))<li><x-icon name="phone" size="sm"/><span>{{ $s->get('contact.phone') }}</span></li>@endif
                    @if($s->get('contact.email'))<li><x-icon name="mail" size="sm"/><a href="mailto:{{ $s->get('contact.email') }}">{{ $s->get('contact.email') }}</a></li>@endif
                </ul>
                @if($wa)
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="st-btn st-btn-wa" data-reveal><span><x-whatsapp-icon size="18"/> Conversar no WhatsApp</span></a>
                @endif
            </div>

            <div class="st-form-card" data-reveal>
                @if($sent)
                    <div class="st-sent">
                        <span class="st-sent-ico"><x-icon name="check" size="lg"/></span>
                        <h3>Recebemos seu pedido{{ is_string($sent) ? ', '.$sent : '' }}!</h3>
                        <p>Nossa equipe vai entrar em contato pelo WhatsApp para confirmar o melhor horário para a sua avaliação.</p>
                        @if($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="st-btn st-btn-wa mt"><span><x-whatsapp-icon size="18"/> Falar agora pelo WhatsApp</span></a>@endif
                    </div>
                @else
                    <form method="POST" action="{{ route('site.request', $clinic->slug) }}" class="st-form" novalidate>
                        @csrf
                        <h3>Agende sua avaliação</h3>
                        <p class="st-form-sub">Preencha e retornamos em até um dia útil.</p>
                        @if($errors->any())<div class="st-error">{{ $errors->first() }}</div>@endif
                        <input type="text" name="website" class="st-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                        <label class="st-field"><span>Nome completo</span><input name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                        <div class="st-row">
                            <label class="st-field"><span>WhatsApp</span><input name="phone" value="{{ old('phone') }}" data-mask="phone" inputmode="tel" autocomplete="tel" placeholder="(00) 00000-0000" required></label>
                            <label class="st-field"><span>E-mail <i>(opcional)</i></span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email"></label>
                        </div>
                        <label class="st-field"><span>Tratamento de interesse</span>
                            <select name="procedure">
                                @foreach($s->list('contact.procedures') as $p)<option @selected(old('procedure') === $p)>{{ $p }}</option>@endforeach
                            </select>
                        </label>
                        <div class="st-row">
                            <label class="st-field"><span>Data de preferência</span><input type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ today()->toDateString() }}"></label>
                            <label class="st-field"><span>Período</span>
                                <select name="preferred_period">
                                    <option value="">Qualquer horário</option>
                                    @foreach(BookingRequest::PERIODS as $k => $v)<option value="{{ $k }}" @selected(old('preferred_period') === $k)>{{ $v }}</option>@endforeach
                                </select>
                            </label>
                        </div>
                        <label class="st-field"><span>Mensagem <i>(opcional)</i></span><textarea name="message" rows="3" maxlength="1000">{{ old('message') }}</textarea></label>
                        <button class="st-btn st-btn-accent st-btn-lg st-btn-block"><span>Solicitar avaliação <x-icon name="arrow-right" size="sm"/></span></button>
                        <p class="st-privacy"><x-icon name="lock" size="sm"/> Seus dados são usados apenas para o agendamento.</p>
                    </form>
                @endif
            </div>
        </div>
    </section>
</main>

<footer class="st-footer">
    <div class="st-wrap">
        <div class="st-footer-top">
            <div>
                <b class="st-footer-brand">{{ $brand }}</b>
                <p>{{ $s->get('brand.tagline') }} · {{ $s->get('about.registry') }}</p>
            </div>
            <nav>
                @foreach($nav as [$href, $label])<a href="{{ $href }}">{{ $label }}</a>@endforeach
                @if($instagram)<a href="https://instagram.com/{{ $instagram }}" target="_blank" rel="noopener">Instagram</a>@endif
            </nav>
        </div>
        <div class="st-wordmark" aria-hidden="true">{{ $brand }}</div>
        <div class="st-footer-bottom">
            <small>© {{ date('Y') }} {{ $brand }}. Responsável técnica: {{ $s->get('about.name') }} — {{ $s->get('about.registry') }}.</small>
            <a href="{{ route('login.clinic', $clinic->slug) }}">Área do profissional</a>
        </div>
    </div>
</footer>

@if($wa)
    <a href="{{ $wa }}" target="_blank" rel="noopener" class="st-wa" aria-label="Conversar pelo WhatsApp"><x-whatsapp-icon size="26"/></a>
@endif

<script src="{{ asset('js/site.js') }}?v=1" defer></script>
</body>
</html>
