/* DCK Watch Live: live status, countdown, time-zone converter, calendar links, tabs, share. */
(function () {
  'use strict';
  var L = window.DCK_LIVE;
  if (!L) { return; }
  var I = L.i18n || {};
  var $ = function (id) { return document.getElementById(id); };
  function hm(s) { var p = String(s).split(':'); return (parseInt(p[0], 10) || 0) * 60 + (parseInt(p[1], 10) || 0); }
  var startMin = hm(L.start), endMin = hm(L.end), dur = Math.max(30, endMin - startMin);
  var EAT = 3 * 3600000; /* Nairobi is UTC+3 all year (no daylight saving). */

  function nairobiNow() { var d = new Date(Date.now() + EAT); return { dow: d.getUTCDay(), min: d.getUTCHours() * 60 + d.getUTCMinutes() }; }
  function isLive() { var n = nairobiNow(); return n.dow === parseInt(L.day, 10) && n.min >= startMin && n.min < endMin; }
  function nextStart() {
    var t = parseInt(L.next, 10);
    while (t + dur * 60000 < Date.now()) { t += 7 * 86400000; }
    return t;
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }

  var status = $('dck-live-status'), statusT = $('dck-live-status-t'), cdLabel = $('dck-live-cdlabel');
  function tick() {
    var live = isLive();
    status.setAttribute('data-state', live ? 'live' : 'next');
    statusT.textContent = live ? I.live : I.next;
    var cd = $('dck-live-cd');
    if (live) { cdLabel.textContent = I.started; cd.classList.add('is-live'); return; }
    cd.classList.remove('is-live');
    cdLabel.textContent = I.in;
    var diff = Math.max(0, nextStart() - Date.now());
    var v = { d: Math.floor(diff / 86400000), h: Math.floor(diff / 3600000) % 24, m: Math.floor(diff / 60000) % 60, s: Math.floor(diff / 1000) % 60 };
    cd.querySelectorAll('b[data-u]').forEach(function (b) { var u = b.getAttribute('data-u'); b.textContent = u === 'd' ? v.d : pad(v[u]); });
  }
  tick(); setInterval(tick, 1000);

  /* Time-zone converter */
  var ns = nextStart();
  try {
    var opt = { weekday: 'long', hour: 'numeric', minute: '2-digit' };
    $('dck-live-eat').textContent = new Intl.DateTimeFormat(undefined, Object.assign({ timeZone: 'Africa/Nairobi' }, opt)).format(ns);
    var local = new Intl.DateTimeFormat(undefined, Object.assign({ timeZoneName: 'short' }, opt)).format(ns);
    $('dck-live-local').textContent = local;
    var tzName = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
    if (tzName) { $('dck-live-yourlbl').textContent = I.yours + ' (' + tzName.replace(/_/g, ' ') + ')'; }
    if (tzName === 'Africa/Nairobi') { $('dck-live-local').parentNode.style.display = 'none'; }
  } catch (e) {}

  /* Calendar links (weekly, recurring) */
  function stamp(ms) { return new Date(ms).toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, ''); }
  var days = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'], byday = days[parseInt(L.day, 10) || 0];
  var g = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' + encodeURIComponent(L.title) +
    '&dates=' + stamp(ns) + '/' + stamp(ns + dur * 60000) + '&details=' + encodeURIComponent('Watch live: ' + L.url) +
    '&location=' + encodeURIComponent(L.url) + '&recur=' + encodeURIComponent('RRULE:FREQ=WEEKLY;BYDAY=' + byday);
  var gl = $('dck-live-gcal'); if (gl) { gl.href = g; }
  var ib = $('dck-live-ics');
  if (ib) {
    ib.addEventListener('click', function () {
      var ics = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//DCK//Live//EN', 'BEGIN:VEVENT', 'UID:dck-live-' + ns + '@dckabeten.org', 'DTSTAMP:' + stamp(Date.now()),
        'DTSTART:' + stamp(ns), 'DTEND:' + stamp(ns + dur * 60000), 'RRULE:FREQ=WEEKLY;BYDAY=' + byday,
        'SUMMARY:' + L.title.replace(/[,;]/g, ' '), 'DESCRIPTION:Watch live: ' + L.url, 'URL:' + L.url, 'LOCATION:' + String(L.addr).replace(/[,;]/g, ' '),
        'BEGIN:VALARM', 'TRIGGER:-PT15M', 'ACTION:DISPLAY', 'DESCRIPTION:Service starts soon', 'END:VALARM', 'END:VEVENT', 'END:VCALENDAR'].join('\r\n');
      var a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([ics], { type: 'text/calendar' }));
      a.download = 'dck-sunday-service.ics';
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
    });
  }

  /* Invite / share */
  var sh = $('dck-live-share');
  if (sh) {
    sh.addEventListener('click', function () {
      var msg = 'Join me to worship live with Deliverance Church Kabete N: ' + L.url;
      if (navigator.share) { navigator.share({ title: L.title, text: msg, url: L.url }).catch(function () {}); return; }
      window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(msg), '_blank', 'noopener');
    });
  }

  /* Past services tabs */
  var tabs = document.querySelectorAll('.dck-seg [role=tab]');
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      tabs.forEach(function (x) {
        var on = x === t;
        x.classList.toggle('is-active', on); x.setAttribute('aria-selected', on ? 'true' : 'false');
        var p = $(x.getAttribute('aria-controls')); if (p) { p.hidden = !on; }
      });
    });
  });
})();
