// Interações leves, sem dependências.
(function () {
  // Menu lateral no celular
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-toggle-nav]')) {
      document.body.classList.toggle('nav-open');
    } else if (document.body.classList.contains('nav-open') && !e.target.closest('.sidebar')) {
      document.body.classList.remove('nav-open');
    }
  });

  // Dropdowns
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-dropdown]');
    document.querySelectorAll('.dropdown.open').forEach(function (d) {
      if (!trigger || d !== trigger.parentElement) d.classList.remove('open');
    });
    if (trigger) {
      e.preventDefault();
      trigger.parentElement.classList.toggle('open');
    }
  });

  // Confirmação antes de excluir
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  // Máscaras simples (CPF e telefone)
  function mask(el, fn) { el.addEventListener('input', function () { el.value = fn(el.value.replace(/\D/g, '')); }); el.value = fn(el.value.replace(/\D/g, '')); }
  document.querySelectorAll('[data-mask="cpf"]').forEach(function (el) {
    mask(el, function (d) {
      d = d.slice(0, 11);
      return d.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    });
  });
  document.querySelectorAll('[data-mask="phone"]').forEach(function (el) {
    mask(el, function (d) {
      d = d.slice(0, 11);
      if (d.length <= 2) return d.length ? '(' + d : '';
      if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
      if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
      return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
    });
  });

  // Pré-visualização ao vivo da cor (tela de personalização)
  var root = document.documentElement;
  function applyAccent(hex) {
    if (!/^#[0-9a-f]{6}$/i.test(hex)) return;
    var r = parseInt(hex.substr(1, 2), 16), g = parseInt(hex.substr(3, 2), 16), b = parseInt(hex.substr(5, 2), 16);
    var lum = [r, g, b].map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
    var L = 0.2126 * lum[0] + 0.7152 * lum[1] + 0.0722 * lum[2];
    root.style.setProperty('--accent', hex);
    root.style.setProperty('--accent-rgb', r + ',' + g + ',' + b);
    root.style.setProperty('--accent-contrast', L > 0.55 ? '#0B0B12' : '#FFFFFF');
  }
  var colorInput = document.querySelector('[data-accent-input]');
  if (colorInput) {
    var hexText = document.querySelector('[data-accent-hex]');
    function sync(hex) {
      colorInput.value = hex;
      if (hexText) hexText.value = hex.toUpperCase();
      document.querySelectorAll('input[name="primary_color_preset"]').forEach(function (r) { r.checked = r.value.toLowerCase() === hex.toLowerCase(); });
      applyAccent(hex);
    }
    colorInput.addEventListener('input', function () { sync(colorInput.value); });
    if (hexText) hexText.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(hexText.value)) sync(hexText.value); });
    document.querySelectorAll('input[name="primary_color_preset"]').forEach(function (r) {
      r.addEventListener('change', function () { sync(r.value); });
    });
  }

  // Prévia do logo enviado
  var logoInput = document.querySelector('[data-logo-input]');
  if (logoInput) {
    logoInput.addEventListener('change', function () {
      var file = logoInput.files && logoInput.files[0];
      if (!file) return;
      var url = URL.createObjectURL(file);
      document.querySelectorAll('[data-logo-preview]').forEach(function (el) { el.innerHTML = '<img src="' + url + '" alt="">'; });
    });
  }

  // Nome da clínica ao vivo na prévia
  var nameInput = document.querySelector('[data-clinic-name]');
  if (nameInput) {
    nameInput.addEventListener('input', function () {
      document.querySelectorAll('[data-clinic-name-preview]').forEach(function (el) { el.textContent = nameInput.value || 'Sua clínica'; });
    });
  }

  // Busca de paciente no formulário de agendamento (filtra o select)
  var filter = document.querySelector('[data-filter-select]');
  if (filter) {
    var select = document.getElementById(filter.getAttribute('data-filter-select'));
    filter.addEventListener('input', function () {
      var t = filter.value.toLowerCase();
      Array.prototype.forEach.call(select.options, function (o) {
        if (!o.value) return;
        o.hidden = t && o.text.toLowerCase().indexOf(t) === -1;
      });
    });
  }

  // Copiar link
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) return;
    navigator.clipboard && navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(function () {
      var old = btn.innerHTML; btn.textContent = 'Copiado!'; setTimeout(function () { btn.innerHTML = old; }, 1500);
    });
  });
})();
