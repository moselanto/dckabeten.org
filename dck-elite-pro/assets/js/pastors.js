/* DCK Our Pastors: profile dialog with deep links and keyboard support. */
(function () {
  'use strict';
  var dlg = document.getElementById('dck-pdlg');
  var box = document.getElementById('dck-pdlg-content');
  if (!dlg || !box) { return; }
  var keys = Array.prototype.map.call(document.querySelectorAll('.dck-pro'), function (a) { return a.id.replace('pastor-', ''); });
  var cur = -1, opener = null;
  function show(k) {
    var t = document.getElementById('dck-bio-' + k);
    if (!t) { return; }
    box.innerHTML = '';
    box.appendChild(t.content.cloneNode(true));
    cur = keys.indexOf(k);
    if (typeof dlg.showModal === 'function') { if (!dlg.open) { dlg.showModal(); } } else { dlg.setAttribute('open', ''); }
    document.documentElement.classList.add('dck-noscroll');
    dlg.scrollTop = 0;
    if (history.replaceState) { history.replaceState(null, '', '#pastor-' + k); }
  }
  function close() {
    if (typeof dlg.close === 'function') { dlg.close(); } else { dlg.removeAttribute('open'); }
  }
  dlg.addEventListener('close', function () {
    document.documentElement.classList.remove('dck-noscroll');
    if (opener) { opener.focus(); }
  });
  document.querySelectorAll('[data-pastor]').forEach(function (b) {
    b.addEventListener('click', function () { opener = b; show(b.getAttribute('data-pastor')); });
  });
  document.getElementById('dck-pdlg-x').addEventListener('click', close);
  dlg.addEventListener('click', function (e) { if (e.target === dlg) { close(); } });
  document.getElementById('dck-pdlg-prev').addEventListener('click', function () { show(keys[(cur - 1 + keys.length) % keys.length]); });
  document.getElementById('dck-pdlg-next').addEventListener('click', function () { show(keys[(cur + 1) % keys.length]); });
  dlg.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') { show(keys[(cur + 1) % keys.length]); }
    if (e.key === 'ArrowLeft') { show(keys[(cur - 1 + keys.length) % keys.length]); }
  });
  var m = (location.hash || '').match(/^#pastor-([a-z0-9_-]+)/);
  if (m && keys.indexOf(m[1]) > -1) { setTimeout(function () { show(m[1]); }, 300); }
})();
