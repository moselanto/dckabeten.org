/* DCK Contact page: message form + local time. */
(function () {
  'use strict';
  var C = window.DCK_CONTACT || {};
  var f = document.getElementById('dck-contact-form');
  if (f) {
    var T0 = Date.now();
    var status = document.getElementById('dck-contact-status');
    var val = function (n) { return f.elements[n] ? String(f.elements[n].value).trim() : ''; };
    var say = function (m, ok) { status.textContent = m; status.className = 'dck-prayer__status ' + (ok ? 'is-ok' : 'is-err'); };
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = val('name'), email = val('email'), msg = val('message');
      if (!name || !email || !msg) { say(C.errRequired); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { say(C.errEmail); return; }
      var btn = f.querySelector('button[type=submit]');
      var prev = btn.textContent;
      btn.disabled = true; btn.textContent = C.sending;
      fetch(f.getAttribute('data-endpoint'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': f.getAttribute('data-nonce') },
        body: JSON.stringify({ name: name, email: email, phone: val('phone'), topic: val('topic'), message: msg, website: val('website'), elapsed: Date.now() - T0 })
      }).then(function (r) { return r.json(); }).then(function (res) {
        say((res && res.message) ? res.message : C.thanks, res && res.ok);
        if (res && res.ok) {
          f.reset();
          if (res.whatsapp) {
            var old = document.getElementById('dck-contact-wa');
            if (old) { old.parentNode.removeChild(old); }
            var wa = document.createElement('a');
            wa.id = 'dck-contact-wa'; wa.className = 'dck-btn dck-btn--gold dck-btn--block';
            wa.href = res.whatsapp; wa.target = '_blank'; wa.rel = 'noopener'; wa.textContent = C.alsoWa;
            status.parentNode.insertBefore(wa, status.nextSibling);
          }
        }
      }).catch(function () {
        say(C.errNet + ' ' + C.phone + '.');
      }).then(function () { btn.disabled = false; btn.textContent = prev; });
    });
  }

  var lt = document.querySelector('.dck-localtime');
  if (lt && window.Intl && Intl.DateTimeFormat) {
    try {
      var fmt = new Intl.DateTimeFormat(undefined, { timeZone: lt.getAttribute('data-tz'), weekday: 'short', hour: 'numeric', minute: '2-digit' });
      var out = lt.querySelector('strong');
      var tick = function () { out.textContent = fmt.format(new Date()); };
      tick(); setInterval(tick, 30000); lt.hidden = false;
    } catch (err) { /* unsupported time zone: keep hidden */ }
  }
})();
