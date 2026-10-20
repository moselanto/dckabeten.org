/* DCK Prayer Request form. */
(function () {
  'use strict';
  var P = window.DCK_PRAYER || {};
  var f = document.getElementById('dck-prayer-form');
  if (!f) { return; }
  var T0 = Date.now();
  var status = document.getElementById('dck-prayer-status');
  var topic = '';
  var chips = f.querySelectorAll('.dck-ptopics .dck-chip');
  chips.forEach(function (c) {
    c.addEventListener('click', function () {
      var on = c.getAttribute('aria-pressed') !== 'true';
      chips.forEach(function (x) { x.setAttribute('aria-pressed', 'false'); x.classList.remove('is-active'); });
      if (on) { c.setAttribute('aria-pressed', 'true'); c.classList.add('is-active'); topic = c.getAttribute('data-topic'); } else { topic = ''; }
    });
  });
  function val(n) { return f.elements[n] ? String(f.elements[n].value).trim() : ''; }
  function say(m, ok) { status.textContent = m; status.className = 'dck-prayer__status ' + (ok ? 'is-ok' : 'is-err'); }
  f.addEventListener('submit', function (e) {
    e.preventDefault();
    var name = val('name'), phone = val('phone'), email = val('email'), req = val('request');
    if (!name || !phone || !email || !req) { say(P.errFill); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { say(P.errEmail); return; }
    var btn = f.querySelector('button[type=submit]'), prev = btn.textContent;
    btn.disabled = true; btn.textContent = P.sending;
    fetch(f.getAttribute('data-endpoint'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': f.getAttribute('data-nonce') },
      body: JSON.stringify({ name: name, phone: phone, email: email, request: (topic ? '[' + topic + '] ' : '') + req, 'private': !!(f.elements['private'] && f.elements['private'].checked), lang: window.DCK_LANG || '', website: val('website'), elapsed: Date.now() - T0 })
    }).then(function (r) { return r.json(); }).then(function (res) {
      say((res && res.message) ? res.message : P.thanks, res && res.ok);
      if (res && res.ok) { f.reset(); topic = ''; chips.forEach(function (x) { x.setAttribute('aria-pressed', 'false'); x.classList.remove('is-active'); }); status.classList.add('is-done'); }
    }).catch(function () { say(P.errNet + ' ' + P.phone + '.'); })
      .then(function () { btn.disabled = false; btn.textContent = prev; });
  });
})();
