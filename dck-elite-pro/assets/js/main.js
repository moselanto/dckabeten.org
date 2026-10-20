/* DCK Elite - front-end interactions (no dependencies, reduced-motion aware). */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Mobile menu: burger + bottom "Menu" tab open a full-screen sheet.
  var nav = document.getElementById('dck-nav');
  var toggles = document.querySelectorAll('.dck-burger, .dck-tabbar__menu');
  var root = document.documentElement;
  var mq = window.matchMedia ? window.matchMedia('(max-width: 1024px)') : null;
  function setMenu(open) {
    if (!nav) { return; }
    if (open) {
      var hdr = document.querySelector('.dck-header');
      if (hdr) { root.style.setProperty('--dck-hdr-b', Math.max(0, Math.round(hdr.getBoundingClientRect().bottom)) + 'px'); }
    }
    nav.classList.toggle('is-open', open);
    root.classList.toggle('dck-menu-open', open);
    for (var i = 0; i < toggles.length; i++) {
      toggles[i].setAttribute('aria-expanded', open ? 'true' : 'false');
      toggles[i].classList.toggle('is-active', open);
    }
    var b = document.querySelector('.dck-burger');
    if (b) { b.setAttribute('aria-label', open ? 'Close menu' : 'Open menu'); }
  }
  if (nav) {
    for (var t = 0; t < toggles.length; t++) {
      toggles[t].addEventListener('click', function () { setMenu(!nav.classList.contains('is-open')); });
    }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && nav.classList.contains('is-open')) { setMenu(false); } });
    nav.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a') : null;
      if (a && !a.parentNode.classList.contains('menu-item-has-children')) { setMenu(false); }
    });
    if (mq && mq.addEventListener) { mq.addEventListener('change', function (m) { if (!m.matches) { setMenu(false); } }); }
    // Sub-menus become tap-to-expand sections on mobile.
    var parents = nav.querySelectorAll('.menu-item-has-children');
    for (var p = 0; p < parents.length; p++) {
      (function (li) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dck-subtoggle';
        btn.setAttribute('aria-expanded', 'false');
        var link = li.querySelector('a');
        btn.setAttribute('aria-label', 'Show ' + (link ? link.textContent.trim() : 'more') + ' links');
        btn.addEventListener('click', function () {
          var o = li.classList.toggle('is-sub-open');
          btn.setAttribute('aria-expanded', o ? 'true' : 'false');
        });
        if (link) { link.insertAdjacentElement('afterend', btn); } else { li.insertBefore(btn, li.firstChild); }
      })(parents[p]);
    }
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

/* YouTube click-to-play: load the real player only when asked. */
(function () {
	document.addEventListener('click', function (e) {
		var b = e.target.closest ? e.target.closest('.dck-yt') : null;
		if (!b) { return; }
		var src = b.getAttribute('data-src') || '';
		if (!/^https:\/\/www\.youtube(-nocookie)?\.com\/embed\//.test(src)) { return; }
		var f = document.createElement('iframe');
		f.src = src + (src.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1&rel=0';
		f.title = b.getAttribute('data-title') || 'Video';
		f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
		f.setAttribute('allowfullscreen', '');
		f.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:0';
		b.replaceWith(f);
		f.focus();
	});
})();
