/* Services & Events: highlight today (Nairobi time) and run event countdowns. */
(function () {
  'use strict';
  var now = new Date(Date.now() + 3 * 3600000); /* EAT, UTC+3 */
  var dow = now.getUTCDay();
  document.querySelectorAll('.dck-week3__day[data-dow="' + dow + '"]').forEach(function (d) { d.classList.add('is-today'); });
  /* On phones, scroll the week strip so today is visible. */
  var today = document.querySelector('.dck-week3__day.is-today');
  if (today && window.matchMedia('(max-width: 900px)').matches && today.parentNode.scrollTo) {
    today.parentNode.scrollTo({ left: today.offsetLeft - 16, behavior: 'smooth' });
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  var cds = document.querySelectorAll('[data-dck-until]');
  if (!cds.length) { return; }
  function tick() {
    cds.forEach(function (el) {
      var diff = parseInt(el.getAttribute('data-dck-until'), 10) - Date.now();
      if (diff <= 0) { el.textContent = el.getAttribute('data-done') || ''; return; }
      var d = Math.floor(diff / 86400000), h = Math.floor(diff / 3600000) % 24, m = Math.floor(diff / 60000) % 60, s = Math.floor(diff / 1000) % 60;
      el.innerHTML = '<span><b>' + d + '</b><i>' + (el.getAttribute('data-d') || 'days') + '</i></span><span><b>' + pad(h) + '</b><i>' + (el.getAttribute('data-h') || 'hrs') + '</i></span><span><b>' + pad(m) + '</b><i>' + (el.getAttribute('data-m') || 'min') + '</i></span><span><b>' + pad(s) + '</b><i>' + (el.getAttribute('data-s') || 'sec') + '</i></span>';
    });
  }
  tick(); setInterval(tick, 1000);
})();
