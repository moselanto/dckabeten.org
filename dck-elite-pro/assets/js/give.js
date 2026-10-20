/* DCK Give page: tabs, copy buttons. */
(function () {
  'use strict';
  var G = window.DCK_GIVE || {};

  /* Tabs (keyboard accessible, deep-linkable with #paybill, #till, #bank, #abroad). */
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.dck-gv__tab'));
  function activate(tab, focus) {
    tabs.forEach(function (t) {
      var on = t === tab;
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.tabIndex = on ? 0 : -1;
      var p = document.getElementById(t.getAttribute('aria-controls'));
      if (p) { p.hidden = !on; }
    });
    if (focus) { tab.focus(); }
  }
  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { activate(t); });
    t.addEventListener('keydown', function (e) {
      var n = null;
      if (e.key === 'ArrowRight') { n = tabs[(i + 1) % tabs.length]; }
      if (e.key === 'ArrowLeft') { n = tabs[(i - 1 + tabs.length) % tabs.length]; }
      if (e.key === 'Home') { n = tabs[0]; }
      if (e.key === 'End') { n = tabs[tabs.length - 1]; }
      if (n) { e.preventDefault(); activate(n, true); }
    });
  });
  var h = (window.location.hash || '').replace('#', '');
  tabs.forEach(function (t) { if (t.getAttribute('data-tab') === h) { activate(t); } });

  /* Copy buttons */
  function copy(text, btn, label) {
    var done = function () {
      var o = btn.textContent;
      btn.textContent = label; btn.classList.add('is-copied');
      setTimeout(function () { btn.textContent = o; btn.classList.remove('is-copied'); }, 1500);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(done).catch(done);
    } else {
      var ta = document.createElement('textarea');
      ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'absolute'; ta.style.left = '-9999px';
      document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); } catch (err) {}
      document.body.removeChild(ta); done();
    }
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.dck-copy, .dck-copy-all');
    if (!b) { return; }
    copy(b.getAttribute('data-copy') || '', b, b.classList.contains('dck-copy-all') ? (G.copyAll || 'Copied') : (G.copied || 'Copied'));
  });
})();
