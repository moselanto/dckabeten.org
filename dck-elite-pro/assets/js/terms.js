/* DCK Terms page: signature pad, preference form, table-of-contents highlight. */
(function () {
  'use strict';
  var T = window.DCK_TERMS || {};

  /* Signature pad (pointer events, crisp on high-DPI screens). */
  var canvas = document.getElementById('dck-sig-canvas');
  var sigErr = document.getElementById('dck-sig-err');
  var clear = document.getElementById('dck-sig-clear');
  var signed = false, drawing = false, ctx = null, last = null;

  function setup() {
    var r = canvas.getBoundingClientRect();
    var dpr = window.devicePixelRatio || 1;
    canvas.width = Math.round(r.width * dpr);
    canvas.height = Math.round(r.height * dpr);
    ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);
    ctx.strokeStyle = '#0E1C4D';
    ctx.lineWidth = 2.2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    signed = false;
  }
  function pos(e) { var r = canvas.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; }

  if (canvas) {
    setup();
    var rt;
    window.addEventListener('resize', function () { if (!signed) { clearTimeout(rt); rt = setTimeout(setup, 150); } });
    canvas.addEventListener('pointerdown', function (e) { drawing = true; last = pos(e); canvas.setPointerCapture(e.pointerId); });
    canvas.addEventListener('pointermove', function (e) {
      if (!drawing) { return; }
      var p = pos(e);
      ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(p.x, p.y); ctx.stroke();
      last = p; signed = true;
      if (sigErr) { sigErr.hidden = true; }
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) { canvas.addEventListener(ev, function () { drawing = false; }); });
    if (clear) { clear.addEventListener('click', function () { ctx.clearRect(0, 0, canvas.width, canvas.height); signed = false; }); }
  }

  /* Form */
  var form = document.getElementById('dck-optout-form');
  var status = document.getElementById('dck-optout-status');
  if (form) {
    var T0 = Date.now();
    var val = function (n) { return form.elements[n] ? String(form.elements[n].value).trim() : ''; };
    var say = function (msg, ok) { if (status) { status.textContent = msg; status.className = 'dck-prayer__status ' + (ok ? 'is-ok' : 'is-err'); } };

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = val('name'), phone = val('phone'), email = val('email');
      var consent = form.elements.consent && form.elements.consent.checked;
      if (!name) { say(T.errName); return; }
      if (!phone) { say(T.errPhone); return; }
      if (!email || email.indexOf('@') < 1) { say(T.errEmail); return; }
      if (!signed) { if (sigErr) { sigErr.hidden = false; } say(T.errSig); return; }
      if (!consent) { say(T.errTick); return; }

      var btn = form.querySelector('button[type=submit]');
      var prev = btn.textContent;
      btn.disabled = true; btn.textContent = T.sending || 'Sending...';
      say('', true);

      fetch(form.getAttribute('data-ep'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': form.getAttribute('data-nc') },
        body: JSON.stringify({ name: name, phone: phone, email: email, service: val('service'), signature: canvas.toDataURL('image/png'), website: val('website'), elapsed: Date.now() - T0 })
      }).then(function (r) { return r.json(); }).then(function (res) {
        say((res && res.message) ? res.message : (T.thanks || 'Thank you.'), res && res.ok);
        if (res && res.ok) { form.reset(); setup(); }
      }).catch(function () {
        say((T.errNet || 'Something went wrong. Please call') + ' ' + (T.phone || '') + '.');
      }).then(function () { btn.disabled = false; btn.textContent = prev; });
    });
  }

  /* Highlight the current section in the table of contents. */
  var links = document.querySelectorAll('.dck-legal-toc a');
  if (links.length && 'IntersectionObserver' in window) {
    var map = {};
    links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting && map[en.target.id]) {
          links.forEach(function (a) { a.classList.remove('is-active'); });
          map[en.target.id].classList.add('is-active');
        }
      });
    }, { rootMargin: '-30% 0px -60% 0px' });
    Object.keys(map).forEach(function (id) { var el = document.getElementById(id); if (el) { io.observe(el); } });
  }
})();
