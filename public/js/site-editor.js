// Editor do site: fotos, blocos repetíveis, paletas e aviso de alterações.
(function () {
  var form = document.getElementById('site-form');
  if (!form) return;
  var dirty = false, bar = document.querySelector('[data-save-bar]');
  function markDirty() { if (!dirty) { dirty = true; bar.hidden = false; } }
  form.addEventListener('input', markDirty);
  form.addEventListener('change', markDirty);
  form.addEventListener('submit', function () { dirty = false; });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  // Fotos: prévia ao escolher, remover.
  form.addEventListener('change', function (e) {
    var input = e.target.closest('[data-img-input]');
    if (!input || !input.files[0]) return;
    var field = input.closest('[data-img-field]');
    var img = field.querySelector('[data-img-preview]');
    img.src = URL.createObjectURL(input.files[0]); img.hidden = false;
    field.querySelector('.img-empty').hidden = true;
    field.querySelector('[data-img-remove]').hidden = false;
  });
  form.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-img-remove]');
    if (!btn) return;
    var field = btn.closest('[data-img-field]');
    field.querySelector('[data-img-value]').value = '';
    field.querySelector('[data-img-input]').value = '';
    field.querySelector('[data-img-preview]').hidden = true;
    field.querySelector('.img-empty').hidden = false;
    btn.hidden = true; markDirty();
  });

  // Blocos repetíveis.
  var seq = Date.now() % 100000;
  form.addEventListener('click', function (e) {
    var add = e.target.closest('[data-add]');
    if (add) {
      var rep = add.closest('[data-repeater]');
      var html = rep.querySelector('template').innerHTML.replace(/IDX/g, String(seq++));
      var list = rep.querySelector('.repeater-list');
      list.insertAdjacentHTML('beforeend', html);
      var first = list.lastElementChild.querySelector('input:not([type=hidden]):not([type=file]), textarea');
      if (first) first.focus();
      markDirty();
      return;
    }
    var row = e.target.closest('[data-row]');
    if (!row) return;
    if (e.target.closest('[data-remove]')) { row.remove(); markDirty(); }
    else if (e.target.closest('[data-up]') && row.previousElementSibling) { row.parentNode.insertBefore(row, row.previousElementSibling); markDirty(); }
    else if (e.target.closest('[data-down]') && row.nextElementSibling) { row.parentNode.insertBefore(row.nextElementSibling, row); markDirty(); }
  });

  // Paletas e cores.
  function setColor(key, value) {
    form.querySelector('[data-color-hex="' + key + '"]').value = value.toUpperCase();
    form.querySelector('[data-color-for="' + key + '"]').value = value;
  }
  form.querySelectorAll('[data-palette]').forEach(function (r) {
    r.addEventListener('change', function () {
      var p = JSON.parse(r.getAttribute('data-palette'));
      setColor('accent', p.accent); setColor('dark', p.dark); setColor('light', p.light);
    });
  });
  form.querySelectorAll('[data-color-for]').forEach(function (pick) {
    pick.addEventListener('input', function () {
      form.querySelector('[data-color-hex="' + pick.getAttribute('data-color-for') + '"]').value = pick.value.toUpperCase();
    });
  });
  form.querySelectorAll('[data-color-hex]').forEach(function (hex) {
    hex.addEventListener('input', function () {
      if (/^#[0-9a-f]{6}$/i.test(hex.value)) form.querySelector('[data-color-for="' + hex.getAttribute('data-color-hex') + '"]').value = hex.value;
    });
  });

  // Abas: destaca a seção visível e volta para ela depois de salvar.
  var tabs = document.querySelectorAll('[data-tab]'), secInput = form.querySelector('[data-section-input]');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === en.target.id); });
        secInput.value = en.target.id;
      });
    }, { rootMargin: '-35% 0px -60% 0px' });
    document.querySelectorAll('.editor-sec').forEach(function (s) { io.observe(s); });
  }
  var start = new URLSearchParams(location.search).get('secao');
  if (start && document.getElementById(start)) document.getElementById(start).scrollIntoView();
})();
