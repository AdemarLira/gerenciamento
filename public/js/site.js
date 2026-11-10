// Efeitos do site público — sem dependências.
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  // Navegação flutuante: some ao descer, volta ao subir.
  var nav = $('[data-nav]'), lastY = 0;
  function onScroll() {
    var y = window.scrollY;
    nav.classList.toggle('scrolled', y > 20);
    if (!nav.classList.contains('open')) nav.classList.toggle('hidden', y > 400 && y > lastY);
    lastY = y;
  }
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  $('[data-burger]').addEventListener('click', function () { nav.classList.toggle('open'); nav.classList.remove('hidden'); });
  $$('.st-links a, .st-nav-cta', nav).forEach(function (a) { a.addEventListener('click', function () { nav.classList.remove('open'); }); });

  // Título: palavras surgindo em sequência.
  $$('[data-words]').forEach(function (h) {
    $$('.w', h).forEach(function (w, i) { w.style.setProperty('--i', i); });
    setTimeout(function () { h.classList.add('in'); }, 30);
  });

  // Revelação ao rolar.
  var io = 'IntersectionObserver' in window && !reduce ? new IntersectionObserver(function (entries) {
    entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px', threshold: .08 }) : null;
  $$('[data-reveal]').forEach(function (el) { io ? io.observe(el) : el.classList.add('in'); });

  // Contadores dos números.
  $$('[data-count]').forEach(function (el) {
    var m = el.textContent.trim().match(/^(\D*)([\d.,]+)(.*)$/);
    if (!m || reduce) return;
    var target = parseFloat(m[2].replace(/\./g, '').replace(',', '.')), dec = (m[2].split(',')[1] || '').length;
    var fmt = function (n) { return m[1] + n.toLocaleString('pt-BR', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + m[3]; };
    el.textContent = fmt(0);
    var obs = new IntersectionObserver(function (es) {
      if (!es[0].isIntersecting) return; obs.disconnect();
      var t0 = performance.now();
      (function tick(t) {
        var p = Math.min(1, (t - t0) / 1600), e = 1 - Math.pow(1 - p, 3);
        el.textContent = fmt(target * e);
        if (p < 1) requestAnimationFrame(tick);
      })(t0);
    }, { threshold: .6 });
    obs.observe(el);
  });

  // Parallax da foto do topo.
  var par = $('[data-parallax] img');
  if (par && !reduce) {
    window.addEventListener('scroll', function () {
      var y = Math.min(window.scrollY, 900);
      par.style.setProperty('--py', (-y * 0.08) + 'px');
    }, { passive: true });
  }

  // Card 3D que acompanha o mouse.
  $$('[data-tilt]').forEach(function (wrap) {
    var card = wrap.firstElementChild;
    if (reduce || !window.matchMedia('(hover: hover)').matches) return;
    wrap.addEventListener('mousemove', function (e) {
      var r = wrap.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
      card.style.setProperty('--ry', ((x - .5) * 14) + 'deg');
      card.style.setProperty('--rx', ((.5 - y) * 12) + 'deg');
      card.style.setProperty('--gx', (x * 100) + '%');
      card.style.setProperty('--gy', (y * 100) + '%');
    });
    wrap.addEventListener('mouseleave', function () { card.style.setProperty('--rx', '0deg'); card.style.setProperty('--ry', '0deg'); });
  });

  // Tratamentos: card abre ao passar o mouse, clicar ou focar.
  $$('.st-expand').forEach(function (group) {
    var items = $$('.st-x', group);
    var open = function (it) { items.forEach(function (x) { x.classList.toggle('is-open', x === it); }); };
    items.forEach(function (it) {
      it.addEventListener('mouseenter', function () { if (window.matchMedia('(hover: hover)').matches) open(it); });
      it.addEventListener('click', function () { open(it); });
      it.addEventListener('focus', function () { open(it); });
    });
  });

  // Antes e depois.
  $$('[data-compare]').forEach(function (box) {
    var input = $('input', box);
    input.addEventListener('input', function () { box.style.setProperty('--pos', input.value + '%'); });
  });

  // Linha do tempo: feixe que preenche conforme a rolagem.
  var tl = $('[data-timeline]');
  if (tl) {
    var beam = $('.st-beam i', tl), steps = $$('li', tl);
    var updateBeam = function () {
      var r = tl.getBoundingClientRect(), mid = window.innerHeight * .6;
      var p = Math.max(0, Math.min(1, (mid - r.top) / r.height));
      beam.style.setProperty('--fill', (p * 100) + '%');
      steps.forEach(function (li) { li.classList.toggle('lit', li.getBoundingClientRect().top < mid); });
    };
    window.addEventListener('scroll', updateBeam, { passive: true }); updateBeam();
  }

  // Máscara de telefone.
  $$('[data-mask="phone"]').forEach(function (el) {
    el.addEventListener('input', function () {
      var d = el.value.replace(/\D/g, '').slice(0, 11);
      el.value = d.length > 10 ? d.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3')
        : d.length > 6 ? d.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3')
        : d.length > 2 ? d.replace(/(\d{2})(\d+)/, '($1) $2') : d;
    });
  });
})();
