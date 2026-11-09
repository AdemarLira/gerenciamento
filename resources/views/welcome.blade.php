@php
    use App\Support\WhatsApp;

    $app = config('app.name');
    $wa = WhatsApp::sales();
    $waDemo = WhatsApp::sales('Olá! Quero agendar uma demonstração do '.$app.' para o meu consultório.');

    // Planos exibidos na seção de preços — ajuste valores e itens aqui.
    $plans = [
        [
            'name' => 'Consultório',
            'for' => 'Para quem atende sozinho',
            'price' => '79',
            'items' => ['1 profissional', 'Agenda e pacientes ilimitados', 'Página de agendamento online', 'Lembretes pelo WhatsApp', 'Logo e cores da sua marca'],
            'featured' => false,
        ],
        [
            'name' => 'Clínica',
            'for' => 'Para equipes em crescimento',
            'price' => '149',
            'items' => ['Até 5 profissionais', 'Tudo do plano Consultório', 'Usuários para recepção', 'Agenda por profissional', 'Painel de faturamento'],
            'featured' => true,
        ],
        [
            'name' => 'Rede',
            'for' => 'Para clínicas maiores',
            'price' => null,
            'items' => ['Profissionais ilimitados', 'Tudo do plano Clínica', 'Implantação acompanhada', 'Importação de pacientes', 'Suporte prioritário'],
            'featured' => false,
        ],
    ];

    $faq = [
        ['Preciso instalar alguma coisa?', 'Não. O sistema funciona no navegador do computador, tablet ou celular. Basta criar a conta e começar a usar.'],
        ['Como o paciente agenda sozinho?', 'Cada consultório ganha um link próprio de agendamento. Você coloca no Instagram, no Google ou envia pelo WhatsApp; o paciente escolhe o profissional, o dia e o horário livre, e a consulta aparece na sua agenda na hora.'],
        ['O WhatsApp envia mensagens automaticamente?', 'Os botões abrem a conversa no seu WhatsApp já com a mensagem pronta (lembrete, confirmação ou retorno). Você só toca em enviar — sem custo extra e sem risco de bloqueio do número.'],
        ['Meus dados e os dos pacientes ficam seguros?', 'Cada clínica tem seus dados isolados, com acesso por usuário e perfil (administrador, profissional ou recepção). Só a sua equipe enxerga seus pacientes.'],
        ['Posso cancelar quando quiser?', 'Sim. Não há fidelidade: você pode cancelar a qualquer momento.'],
        ['Serve para a minha especialidade?', 'Sim. O sistema é usado por médicos, dentistas, psicólogos, fisioterapeutas, nutricionistas e outros profissionais que trabalham com horário marcado.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $app }} — Agenda online e WhatsApp para consultórios e clínicas</title>
    <meta name="description" content="Agenda inteligente, agendamento online 24h e lembretes pelo WhatsApp para médicos, dentistas, psicólogos e clínicas. Menos faltas, mais tempo para atender.">
    <meta name="theme-color" content="#0B1B2B">
    <meta property="og:title" content="{{ $app }} — Seu consultório organizado, sua agenda cheia">
    <meta property="og:description" content="Agenda online, ficha do paciente e WhatsApp em um clique para profissionais da saúde.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v=1">
</head>
<body class="lp">

<header class="lp-nav" data-nav>
    <div class="lp-wrap lp-nav-in">
        <a href="{{ route('home') }}" class="lp-logo">
            <span class="lp-logo-mark"><x-icon name="activity" size="sm"/></span>
            {{ $app }}
        </a>
        <nav class="lp-links" aria-label="Seções">
            <a href="#recursos">Recursos</a>
            <a href="#agendamento">Agendamento online</a>
            <a href="#planos">Planos</a>
            <a href="#duvidas">Dúvidas</a>
        </nav>
        <div class="lp-nav-cta">
            <a href="{{ route('login') }}" class="lp-btn lp-btn-ghost">Entrar</a>
            <a href="{{ route('register') }}" class="lp-btn lp-btn-dark">Teste grátis</a>
        </div>
        <button class="lp-burger" type="button" aria-label="Abrir menu" data-burger><span></span><span></span></button>
    </div>
</header>

<main>
    {{-- ============ HERO ============ --}}
    <section class="lp-hero">
        <div class="lp-wrap lp-hero-in">
            <div class="lp-hero-copy">
                <span class="lp-eyebrow"><span class="dot"></span> Para médicos, dentistas, psicólogos e clínicas</span>
                <h1>Agenda cheia, <em>zero confusão</em> e paciente confirmado no WhatsApp.</h1>
                <p class="lp-lead">O {{ $app }} organiza seus horários, deixa o paciente agendar sozinho 24 horas por dia e envia lembretes pelo WhatsApp em um clique. Você cuida das pessoas; a agenda cuida de si mesma.</p>
                <div class="lp-cta">
                    <a href="{{ route('register') }}" class="lp-btn lp-btn-primary lp-btn-lg">Começar teste grátis <x-icon name="arrow-right" size="sm"/></a>
                    @if($waDemo)
                        <a href="{{ $waDemo }}" target="_blank" rel="noopener" class="lp-btn lp-btn-wa lp-btn-lg"><x-whatsapp-icon/> Falar no WhatsApp</a>
                    @endif
                </div>
                <ul class="lp-trust">
                    <li><x-icon name="check" size="sm"/> Sem cartão de crédito</li>
                    <li><x-icon name="check" size="sm"/> Pronto em 5 minutos</li>
                    <li><x-icon name="check" size="sm"/> Cancele quando quiser</li>
                </ul>
            </div>

            {{-- Mockup do sistema --}}
            <div class="lp-hero-art" aria-hidden="true">
                <div class="mk-window">
                    <div class="mk-bar"><i></i><i></i><i></i><span>Agenda · Hoje</span></div>
                    <div class="mk-body">
                        <div class="mk-side">
                            <div class="mk-brand"><span></span><b></b></div>
                            <div class="mk-nav on"></div><div class="mk-nav"></div><div class="mk-nav"></div><div class="mk-nav"></div>
                        </div>
                        <div class="mk-cal">
                            <div class="mk-cols">
                                <div class="mk-colh"><span style="--c:#0E8A7B">AL</span>Dra. Ana Lima<small>Cardiologia</small></div>
                                <div class="mk-colh"><span style="--c:#3B82F6">RM</span>Dr. Rafael Melo<small>Ortopedia</small></div>
                            </div>
                            <div class="mk-grid">
                                <div class="mk-hours"><i>08h</i><i>09h</i><i>10h</i><i>11h</i><i>12h</i></div>
                                <div class="mk-col">
                                    <div class="mk-ev" style="--c:#0E8A7B;top:2%;height:17%"><b>Marina Souza</b><small>08:00 · Confirmado</small></div>
                                    <div class="mk-ev" style="--c:#0E8A7B;top:22%;height:17%"><b>Carlos Pereira</b><small>09:00 · Retorno</small></div>
                                    <div class="mk-ev ghost" style="top:42%;height:17%"><small>+ Horário livre</small></div>
                                    <div class="mk-ev" style="--c:#0E8A7B;top:62%;height:27%"><b>Júlia Andrade</b><small>11:00 · Avaliação</small></div>
                                </div>
                                <div class="mk-col">
                                    <div class="mk-ev" style="--c:#3B82F6;top:12%;height:17%"><b>Pedro Alves</b><small>08:30 · Consulta</small></div>
                                    <div class="mk-ev online" style="--c:#3B82F6;top:42%;height:17%"><b>Lúcia Ramos</b><small>10:00 · Agendou online</small></div>
                                    <div class="mk-ev" style="--c:#3B82F6;top:72%;height:17%"><b>Bruno Costa</b><small>11:30 · Exame</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mk-float mk-wa">
                    <div class="mk-wa-head"><x-whatsapp-icon size="14"/> WhatsApp</div>
                    <div class="mk-bubble out">Olá Marina! Lembrete da sua consulta amanhã às 08:00 com a Dra. Ana. Podemos confirmar?</div>
                    <div class="mk-bubble in">Confirmado, obrigada! 😊</div>
                </div>

                <div class="mk-float mk-stat">
                    <span class="ico"><x-icon name="calendar-plus" size="sm"/></span>
                    <div><small>Novo agendamento online</small><b>Lúcia · 10:00</b></div>
                </div>
            </div>
        </div>

        <div class="lp-wrap">
            <div class="lp-profs">
                <span>Feito para</span>
                <ul>
                    <li>Médicos</li><li>Dentistas</li><li>Psicólogos</li><li>Fisioterapeutas</li><li>Nutricionistas</li><li>Fonoaudiólogos</li><li>Clínicas de estética</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ============ DORES ============ --}}
    <section class="lp-section lp-pain">
        <div class="lp-wrap">
            <div class="lp-head">
                <span class="lp-kicker">O problema</span>
                <h2>Seu tempo vale demais para ficar <em>preso ao telefone</em>.</h2>
            </div>
            <div class="lp-pain-grid">
                <article>
                    <span class="n">01</span>
                    <h3>Pacientes que faltam</h3>
                    <p>Horário reservado, consultório parado e faturamento perdido. Um lembrete na véspera muda tudo.</p>
                </article>
                <article>
                    <span class="n">02</span>
                    <h3>Agenda no papel ou na planilha</h3>
                    <p>Encaixes que se sobrepõem, rasuras e ninguém sabe ao certo qual horário está livre.</p>
                </article>
                <article>
                    <span class="n">03</span>
                    <h3>Recepção sobrecarregada</h3>
                    <p>Ligações e mensagens o dia inteiro só para marcar, remarcar e confirmar consultas.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- ============ RECURSOS ============ --}}
    <section class="lp-section" id="recursos">
        <div class="lp-wrap">
            <div class="lp-head">
                <span class="lp-kicker">Recursos</span>
                <h2>Tudo o que o seu consultório precisa, <em>em um só lugar</em>.</h2>
                <p>Simples para a recepção, completo para o profissional e bonito para o paciente.</p>
            </div>

            <div class="lp-features">
                <article class="lp-feat lp-feat-lg">
                    <div class="ico"><x-icon name="calendar"/></div>
                    <h3>Agenda inteligente</h3>
                    <p>Visão por dia e por semana, colunas por profissional e bloqueio automático de horários em conflito. Clique em um horário vazio e a consulta está marcada.</p>
                    <div class="lp-feat-chips"><span>Dia</span><span>Semana</span><span>Por profissional</span><span>Status da consulta</span></div>
                </article>
                <article class="lp-feat lp-feat-wa">
                    <div class="ico"><x-whatsapp-icon size="22"/></div>
                    <h3>WhatsApp em um clique</h3>
                    <p>Lembretes, confirmações e retornos com a mensagem já escrita. É só tocar em enviar.</p>
                </article>
                <article class="lp-feat">
                    <div class="ico"><x-icon name="link"/></div>
                    <h3>Agendamento online 24h</h3>
                    <p>Um link só seu para o paciente escolher o horário — inclusive de madrugada e no fim de semana.</p>
                </article>
                <article class="lp-feat">
                    <div class="ico"><x-icon name="file"/></div>
                    <h3>Ficha do paciente</h3>
                    <p>CPF, convênio, observações e histórico completo de consultas sempre à mão.</p>
                </article>
                <article class="lp-feat">
                    <div class="ico"><x-icon name="wallet"/></div>
                    <h3>Painel do dia e faturamento</h3>
                    <p>Consultas de hoje, próximo atendimento, taxa de comparecimento e faturado no mês.</p>
                </article>
                <article class="lp-feat">
                    <div class="ico"><x-icon name="palette"/></div>
                    <h3>Com a sua marca</h3>
                    <p>Seu logo, sua cor e seu link de acesso. O paciente vê o seu consultório, não o nosso.</p>
                </article>
                <article class="lp-feat">
                    <div class="ico"><x-icon name="shield"/></div>
                    <h3>Equipe com permissões</h3>
                    <p>Perfis de administrador, profissional e recepção, cada um vendo só o que precisa.</p>
                </article>
                <article class="lp-feat">
                    <div class="ico"><x-icon name="phone"/></div>
                    <h3>Acesse de qualquer lugar</h3>
                    <p>Funciona no computador, no tablet e no celular, sem instalar nada.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- ============ AGENDAMENTO ONLINE ============ --}}
    <section class="lp-section lp-spot" id="agendamento">
        <div class="lp-wrap lp-spot-in">
            <div class="lp-phone" aria-hidden="true">
                <div class="ph-notch"></div>
                <div class="ph-screen">
                    <div class="ph-brand"><span>CS</span><div><b>Clínica Sorriso</b><small>Agende sua consulta</small></div></div>
                    <div class="ph-label">Profissional</div>
                    <div class="ph-pro on"><i style="--c:#0E8A7B">AL</i><div><b>Dra. Ana Lima</b><small>Cardiologia</small></div><x-icon name="check" size="sm"/></div>
                    <div class="ph-pro"><i style="--c:#3B82F6">RM</i><div><b>Dr. Rafael Melo</b><small>Ortopedia</small></div></div>
                    <div class="ph-label">Data</div>
                    <div class="ph-days"><span><small>seg</small>10</span><span class="on"><small>ter</small>11</span><span><small>qua</small>12</span><span><small>qui</small>13</span></div>
                    <div class="ph-label">Horário</div>
                    <div class="ph-slots"><span>08:00</span><span class="off">08:30</span><span class="on">09:00</span><span>09:30</span><span class="off">10:00</span><span>10:30</span></div>
                    <div class="ph-btn">Confirmar agendamento</div>
                </div>
            </div>
            <div class="lp-spot-copy">
                <span class="lp-kicker">Agendamento online</span>
                <h2>Seu paciente marca a consulta <em>sozinho</em>, e ela cai direto na agenda.</h2>
                <p>Coloque o link na bio do Instagram, no perfil do Google ou na resposta automática do WhatsApp. O paciente vê apenas os horários realmente livres, escolhe e pronto.</p>
                <ul class="lp-checks">
                    <li><x-icon name="check-circle"/> Só mostra horários disponíveis, sem risco de encaixe duplo</li>
                    <li><x-icon name="check-circle"/> Cadastro do paciente criado automaticamente</li>
                    <li><x-icon name="check-circle"/> Confirmação pelo WhatsApp do consultório logo em seguida</li>
                    <li><x-icon name="check-circle"/> Página com o logo e as cores da sua clínica</li>
                </ul>
                <a href="{{ route('register') }}" class="lp-btn lp-btn-primary lp-btn-lg">Quero meu link de agendamento <x-icon name="arrow-right" size="sm"/></a>
            </div>
        </div>
    </section>

    {{-- ============ COMO FUNCIONA ============ --}}
    <section class="lp-section">
        <div class="lp-wrap">
            <div class="lp-head">
                <span class="lp-kicker">Como funciona</span>
                <h2>Comece a usar <em>hoje mesmo</em>.</h2>
            </div>
            <ol class="lp-steps">
                <li>
                    <span class="n">1</span>
                    <h3>Crie sua conta</h3>
                    <p>Cadastre o consultório em poucos minutos, sem cartão de crédito.</p>
                </li>
                <li>
                    <span class="n">2</span>
                    <h3>Personalize</h3>
                    <p>Adicione logo, cor, profissionais, horário de atendimento e o WhatsApp da clínica.</p>
                </li>
                <li>
                    <span class="n">3</span>
                    <h3>Compartilhe seu link</h3>
                    <p>Divulgue o link de agendamento e acompanhe tudo pelo painel.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- ============ PLANOS ============ --}}
    <section class="lp-section lp-pricing" id="planos">
        <div class="lp-wrap">
            <div class="lp-head">
                <span class="lp-kicker">Planos</span>
                <h2>Menos do que <em>uma consulta</em> por mês.</h2>
                <p>Todos os planos começam com teste grátis. Sem fidelidade.</p>
            </div>
            <div class="lp-plans">
                @foreach($plans as $plan)
                    <article @class(['lp-plan', 'is-featured' => $plan['featured']])>
                        @if($plan['featured'])<span class="lp-plan-tag">Mais escolhido</span>@endif
                        <h3>{{ $plan['name'] }}</h3>
                        <p class="for">{{ $plan['for'] }}</p>
                        <div class="price">
                            @if($plan['price'])
                                <small>R$</small><b>{{ $plan['price'] }}</b><small>/mês</small>
                            @else
                                <b class="custom">Sob consulta</b>
                            @endif
                        </div>
                        <ul>
                            @foreach($plan['items'] as $item)<li><x-icon name="check" size="sm"/> {{ $item }}</li>@endforeach
                        </ul>
                        @if($plan['price'])
                            <a href="{{ route('register') }}" @class(['lp-btn lp-btn-lg lp-btn-block', 'lp-btn-primary' => $plan['featured'], 'lp-btn-outline' => ! $plan['featured']])>Começar teste grátis</a>
                        @elseif($wa)
                            <a href="{{ WhatsApp::sales('Olá! Tenho interesse no plano Rede do '.$app.'.') }}" target="_blank" rel="noopener" class="lp-btn lp-btn-lg lp-btn-block lp-btn-outline"><x-whatsapp-icon/> Falar com um consultor</a>
                        @else
                            <a href="{{ route('register') }}" class="lp-btn lp-btn-lg lp-btn-block lp-btn-outline">Começar teste grátis</a>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section class="lp-section" id="duvidas">
        <div class="lp-wrap lp-faq-in">
            <div class="lp-head left">
                <span class="lp-kicker">Dúvidas frequentes</span>
                <h2>Ficou alguma <em>dúvida</em>?</h2>
                <p>Se não encontrar sua resposta aqui, fale com a gente pelo WhatsApp.</p>
                @if($wa)
                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="lp-btn lp-btn-wa mt"><x-whatsapp-icon/> Tirar dúvidas no WhatsApp</a>
                @endif
            </div>
            <div class="lp-faq">
                @foreach($faq as [$q, $a])
                    <details @if($loop->first) open @endif>
                        <summary>{{ $q }}<x-icon name="plus" size="sm"/></summary>
                        <p>{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ CTA FINAL ============ --}}
    <section class="lp-final">
        <div class="lp-wrap">
            <div class="lp-final-box">
                <h2>Seu consultório merece uma agenda <em>à altura</em> do seu atendimento.</h2>
                <p>Crie sua conta agora e veja sua agenda organizada ainda hoje.</p>
                <div class="lp-cta center">
                    <a href="{{ route('register') }}" class="lp-btn lp-btn-light lp-btn-lg">Começar teste grátis <x-icon name="arrow-right" size="sm"/></a>
                    @if($waDemo)
                        <a href="{{ $waDemo }}" target="_blank" rel="noopener" class="lp-btn lp-btn-wa lp-btn-lg"><x-whatsapp-icon/> Agendar demonstração</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="lp-footer">
    <div class="lp-wrap lp-footer-in">
        <a href="{{ route('home') }}" class="lp-logo"><span class="lp-logo-mark"><x-icon name="activity" size="sm"/></span>{{ $app }}</a>
        <nav>
            <a href="#recursos">Recursos</a>
            <a href="#planos">Planos</a>
            <a href="{{ route('login') }}">Entrar</a>
            <a href="{{ route('register') }}">Criar conta</a>
        </nav>
        <small>© {{ date('Y') }} {{ $app }}. Todos os direitos reservados.</small>
    </div>
</footer>

@if($wa)
    <a href="{{ $wa }}" target="_blank" rel="noopener" class="wa-float" aria-label="Conversar pelo WhatsApp">
        <x-whatsapp-icon size="28"/>
        <span>Fale conosco</span>
    </a>
@endif

<script>
    (function () {
        var nav = document.querySelector('[data-nav]');
        document.querySelector('[data-burger]').addEventListener('click', function () { nav.classList.toggle('open'); });
        nav.querySelectorAll('.lp-links a').forEach(function (a) { a.addEventListener('click', function () { nav.classList.remove('open'); }); });
        var onScroll = function () { nav.classList.toggle('scrolled', window.scrollY > 8); };
        window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    })();
</script>
</body>
</html>
