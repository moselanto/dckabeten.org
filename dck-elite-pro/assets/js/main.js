/* DCK Elite - front-end interactions (no dependencies, reduced-motion aware). */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Mobile nav toggle.
  var burger = document.querySelector('.dck-burger');
  var nav = document.getElementById('dck-nav');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Sticky header shadow.
  var header = document.querySelector('.dck-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-stuck', window.scrollY > 30); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Scroll reveal.
  var revealables = document.querySelectorAll('.dck-reveal');
  if (revealables.length && !reduceMotion && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealables.forEach(function (el) { io.observe(el); });
  } else {
    revealables.forEach(function (el) { el.classList.add('is-visible'); });
  }

  // Animated counters.
  var counters = document.querySelectorAll('[data-dck-count]');
  counters.forEach(function (el) {
    var target = parseInt(el.getAttribute('data-dck-count'), 10) || 0;
    var suffix = el.getAttribute('data-dck-suffix') || '';
    var finalText = target.toLocaleString() + suffix;
    // The real number is already in the HTML; only animate when motion is allowed.
    if (reduceMotion || !target) { el.textContent = finalText; return; }
    var started = false;
    var run = function () {
      if (started) { return; } started = true;
      var dur = 1600, t0 = performance.now();
      var step = function (now) {
        var p = Math.min((now - t0) / dur, 1);
        el.textContent = p < 1 ? Math.floor(target * p).toLocaleString() : finalText;
        if (p < 1) { requestAnimationFrame(step); }
      };
      requestAnimationFrame(step);
    };
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (e, obs) {
        if (e[0].isIntersecting) { run(); obs.disconnect(); }
      }).observe(el);
    } else { run(); }
  });
})();

/* Countdown to next live service. */
(function () {
  'use strict';
  var box = document.querySelector('[data-dck-countdown]');
  if (!box) { return; }
  var target = parseInt(box.getAttribute('data-dck-countdown'), 10);
  if (!target) { return; }
  var out = box.querySelector('[data-dck-cd-out]') || box;
  function pad(n){ return (n < 10 ? '0' : '') + n; }
  function tick() {
    var diff = target - Date.now();
    if (diff <= 0) { out.textContent = 'We are live now'; return; }
    var d = Math.floor(diff / 86400000);
    var h = Math.floor(diff / 3600000) % 24;
    var m = Math.floor(diff / 60000) % 60;
    var s = Math.floor(diff / 1000) % 60;
    out.textContent = d + 'd ' + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
  }
  tick();
  setInterval(tick, 1000);
})();

/* Church local time (any page with .dck-localtime, except where a page script already runs). */
(function () {
  'use strict';
  if (window.DCK_CONTACT) { return; }
  var lt = document.querySelector('.dck-localtime');
  if (!lt || !window.Intl) { return; }
  try {
    var fmt = new Intl.DateTimeFormat(undefined, { timeZone: lt.getAttribute('data-tz'), weekday: 'short', hour: 'numeric', minute: '2-digit' });
    var out = lt.querySelector('strong');
    var tick = function () { out.textContent = fmt.format(new Date()); };
    tick(); setInterval(tick, 30000); lt.hidden = false;
  } catch (e) {}
})();

/* Generic copy-link buttons outside the Give page. */
(function () {
  'use strict';
  if (window.DCK_GIVE) { return; }
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('.dck-copy');
    if (!b || !navigator.clipboard) { return; }
    var o = b.textContent;
    navigator.clipboard.writeText(b.getAttribute('data-copy') || '').then(function () {
      b.textContent = '\u2713'; setTimeout(function () { b.textContent = o; }, 1400);
    });
  });
})();
