/* DCK inner pages: ministry finder, FAQ search/expand, pastor bios. */
(function () {
  'use strict';
  var P = window.DCK_PAGES || {};
  function norm(s) { return String(s || '').toLowerCase().trim(); }

  /* Ministries finder */
  var mIn = document.getElementById('dck-min-search');
  var cards = document.querySelectorAll('.dck-mcard');
  var mNone = document.getElementById('dck-min-none');
  var chips = document.querySelectorAll('.dck-min2 .dck-chip[data-q]');
  function filterM(q) {
    q = norm(q); var shown = 0;
    cards.forEach(function (c) {
      var hit = !q || (c.getAttribute('data-s') || '').indexOf(q) !== -1;
      c.hidden = !hit; if (hit) { shown++; }
    });
    if (mNone) { mNone.hidden = shown > 0; }
  }
  if (mIn) {
    mIn.addEventListener('input', function () {
      chips.forEach(function (c) { c.classList.toggle('is-active', c.getAttribute('data-q') === ''); });
      filterM(mIn.value);
    });
  }
  chips.forEach(function (c) {
    c.addEventListener('click', function () {
      chips.forEach(function (x) { x.classList.remove('is-active'); });
      c.classList.add('is-active');
      if (mIn) { mIn.value = ''; }
      filterM(c.getAttribute('data-q'));
    });
  });

  /* FAQ search + expand all */
  var fIn = document.getElementById('dck-faq-search');
  var items = document.querySelectorAll('#dck-faq-groups .dck-faq-item');
  var groups = document.querySelectorAll('#dck-faq-groups .dck-faq-group');
  var fNone = document.getElementById('dck-faq-none');
  var tog = document.getElementById('dck-faq-toggle');
  if (fIn) {
    fIn.addEventListener('input', function () {
      var q = norm(fIn.value), shown = 0;
      items.forEach(function (it) {
        var hit = !q || norm(it.textContent).indexOf(q) !== -1;
        it.hidden = !hit; it.open = !!(q && hit); if (hit) { shown++; }
      });
      groups.forEach(function (g) { g.hidden = !g.querySelector('.dck-faq-item:not([hidden])'); });
      if (fNone) { fNone.hidden = shown > 0; }
    });
  }
  if (tog) {
    tog.addEventListener('click', function () {
      var open = tog.getAttribute('data-open') !== '1';
      items.forEach(function (it) { if (!it.hidden) { it.open = open; } });
      tog.setAttribute('data-open', open ? '1' : '0');
      tog.textContent = open ? (P.collapse || 'Collapse all') : (P.expand || 'Expand all');
    });
  }
  /* Open a question linked by #hash */
  if (window.location.hash) {
    var t = document.querySelector(window.location.hash);
    if (t && t.tagName === 'DETAILS') { t.open = true; }
  }

  /* Pastor biographies */
  document.querySelectorAll('.dck-bio-toggle').forEach(function (b) {
    b.addEventListener('click', function () {
      var bio = document.getElementById(b.getAttribute('aria-controls'));
      if (!bio) { return; }
      var open = bio.hidden;
      bio.hidden = !open;
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
      b.textContent = open ? b.getAttribute('data-less') : b.getAttribute('data-more');
    });
  });
})();
