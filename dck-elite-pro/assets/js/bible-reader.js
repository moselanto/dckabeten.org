/* DCK Bible Reader: multilingual reading, compare, verse actions, verse of the day. */
(function () {
  'use strict';
  var R = window.DCK_READER;
  if (!R) { return; }
  var $ = function (id) { return document.getElementById(id); };
  var selTr = $('dck-r-tr'), selTr2 = $('dck-r-tr2'), selBook = $('dck-r-book'), selCh = $('dck-r-ch');
  var text = $('dck-r-text'), title = $('dck-r-title'), sub = $('dck-r-sub'), credit = $('dck-r-credit');
  var pop = $('dck-vpop'), audio = $('dck-r-audio');
  if (!selTr || !text) { return; }
  var I = R.i18n || {};
  var LS = 'dck_reader_v1';
  var st = { tr: R.default || 'BSB', tr2: '', book: 'JHN', ch: 1, size: 1, night: false };
  try { var saved = JSON.parse(localStorage.getItem(LS) || '{}'); for (var k in saved) { st[k] = saved[k]; } } catch (e) {}
  var books = {}, bookCache = {}, chapterCache = {};
  var verName = {};
  (R.versions || []).forEach(function (v) { verName[v.id] = v; });
  if (!verName[st.tr]) { st.tr = (R.versions[0] || {}).id; }
  if (st.tr2 && !verName[st.tr2]) { st.tr2 = ''; }

  /* English + Kiswahili book names for the "Go to" box. */
  var CODES = 'GEN EXO LEV NUM DEU JOS JDG RUT 1SA 2SA 1KI 2KI 1CH 2CH EZR NEH EST JOB PSA PRO ECC SNG ISA JER LAM EZK DAN HOS JOL AMO OBA JON MIC NAM HAB ZEP HAG ZEC MAL MAT MRK LUK JHN ACT ROM 1CO 2CO GAL EPH PHP COL 1TH 2TH 1TI 2TI TIT PHM HEB JAS 1PE 2PE 1JN 2JN 3JN JUD REV'.split(' ');
  var EN = 'genesis exodus leviticus numbers deuteronomy joshua judges ruth 1samuel 2samuel 1kings 2kings 1chronicles 2chronicles ezra nehemiah esther job psalms proverbs ecclesiastes songofsongs isaiah jeremiah lamentations ezekiel daniel hosea joel amos obadiah jonah micah nahum habakkuk zephaniah haggai zechariah malachi matthew mark luke john acts romans 1corinthians 2corinthians galatians ephesians philippians colossians 1thessalonians 2thessalonians 1timothy 2timothy titus philemon hebrews james 1peter 2peter 1john 2john 3john jude revelation'.split(' ');
  var SW = 'mwanzo kutoka walawi hesabu kumbukumbu yoshua waamuzi ruthu 1samweli 2samweli 1wafalme 2wafalme 1mambo 2mambo ezra nehemia esta ayubu zaburi mithali mhubiri wimbo isaya yeremia maombolezo ezekieli danieli hosea yoeli amosi obadia yona mika nahumu habakuki sefania hagai zekaria malaki mathayo marko luka yohana matendo warumi 1wakorintho 2wakorintho wagalatia waefeso wafilipi wakolosai 1wathesalonike 2wathesalonike 1timotheo 2timotheo tito filemoni waebrania yakobo 1petro 2petro 1yohana 2yohana 3yohana yuda ufunuo'.split(' ');
  var ALIAS = { psalm: 'PSA', ps: 'PSA', song: 'SNG', songs: 'SNG', canticles: 'SNG', rev: 'REV', revelations: 'REV', gen: 'GEN', ex: 'EXO', exo: 'EXO', matt: 'MAT', mt: 'MAT', mk: 'MRK', lk: 'LUK', jn: 'JHN', rom: 'ROM', phil: 'PHP', eph: 'EPH', heb: 'HEB', prov: 'PRO', isa: 'ISA', jer: 'JER', zab: 'PSA' };
  function norm(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]/g, ''); }
  function findBook(name) {
    var n = norm(name);
    if (!n) { return null; }
    if (ALIAS[n]) { return ALIAS[n]; }
    var lists = [EN, SW];
    for (var l = 0; l < lists.length; l++) {
      for (var i = 0; i < lists[l].length; i++) { if (lists[l][i] === n) { return CODES[i]; } }
    }
    for (var id in books) { if (norm(books[id].name) === n) { return id; } }
    for (l = 0; l < lists.length; l++) {
      for (i = 0; i < lists[l].length; i++) { if (n.length >= 3 && lists[l][i].indexOf(n) === 0) { return CODES[i]; } }
    }
    for (id in books) { if (n.length >= 3 && norm(books[id].name).indexOf(n) === 0) { return id; } }
    return null;
  }

  function save() { try { localStorage.setItem(LS, JSON.stringify(st)); } catch (e) {} }
  function api(path) { return fetch(R.rest + path).then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); }); }
  function loadBooks(tr) {
    if (bookCache[tr]) { return Promise.resolve(bookCache[tr]); }
    return api('books?tr=' + encodeURIComponent(tr)).then(function (d) { bookCache[tr] = d; return d; });
  }
  function loadChapter(tr, book, ch) {
    var key = tr + '/' + book + '/' + ch;
    if (chapterCache[key]) { return Promise.resolve(chapterCache[key]); }
    return api('chapter?tr=' + encodeURIComponent(tr) + '&book=' + book + '&ch=' + ch).then(function (d) { chapterCache[key] = d; return d; });
  }

  function fillBooks(d) {
    books = {};
    selBook.innerHTML = '';
    var otDone = false;
    d.books.forEach(function (b) {
      books[b.id] = b;
      var o = document.createElement('option');
      o.value = b.id; o.textContent = b.name;
      selBook.appendChild(o);
    });
  }
  function fillChapters() {
    selCh.innerHTML = '';
    var n = books[st.book] ? books[st.book].chapters : 1;
    for (var i = 1; i <= n; i++) { var o = document.createElement('option'); o.value = i; o.textContent = i; selCh.appendChild(o); }
    selCh.value = st.ch;
  }

  function el(tag, cls, txt) { var e = document.createElement(tag); if (cls) { e.className = cls; } if (txt !== undefined) { e.textContent = txt; } return e; }

  function render(a, b) {
    text.innerHTML = '';
    text.classList.toggle('is-compare', !!b);
    var bv = {};
    if (b) { b.items.forEach(function (it) { if (it.n) { bv[it.n] = it.t; } }); }
    if (b) {
      var hd = el('div', 'dck-rrow dck-rrow--head');
      hd.appendChild(el('span', 'dck-rcol', verName[st.tr] ? verName[st.tr].name : st.tr));
      hd.appendChild(el('span', 'dck-rcol', verName[st.tr2] ? verName[st.tr2].name : st.tr2));
      text.appendChild(hd);
    }
    a.items.forEach(function (it) {
      if (it.h) { text.appendChild(el('h3', 'dck-rhead', it.h)); return; }
      var row = el('p', 'dck-rrow');
      row.setAttribute('data-v', it.n);
      row.id = 'v' + it.n;
      var c1 = el('span', 'dck-rcol');
      c1.appendChild(el('sup', 'dck-vnum', String(it.n)));
      c1.appendChild(document.createTextNode(' ' + it.t));
      row.appendChild(c1);
      if (b) {
        var c2 = el('span', 'dck-rcol dck-rcol--b');
        c2.appendChild(el('sup', 'dck-vnum', String(it.n)));
        c2.appendChild(document.createTextNode(' ' + (bv[it.n] || '')));
        row.appendChild(c2);
      }
      row.tabIndex = 0;
      text.appendChild(row);
    });
  }

  function setHash() {
    var h = '#read=' + st.tr + '/' + st.book + '/' + st.ch + (st.tr2 ? '/' + st.tr2 : '');
    if (history.replaceState) { history.replaceState(null, '', h); }
  }

  var reqId = 0, pendingVerse = 0;
  function open(scroll) {
    var my = ++reqId;
    text.innerHTML = '<p class="dck-bible-loading">' + (I.loading || 'Loading...') + '</p>';
    var v = verName[st.tr] || {};
    title.textContent = (books[st.book] ? books[st.book].name : st.book) + ' ' + st.ch;
    sub.textContent = v.name || '';
    selBook.value = st.book; fillChapters();
    var p1 = loadChapter(st.tr, st.book, st.ch);
    var p2 = st.tr2 ? loadChapter(st.tr2, st.book, st.ch).catch(function () { return { items: [] }; }) : Promise.resolve(null);
    Promise.all([p1, p2]).then(function (r) {
      if (my !== reqId) { return; }
      title.textContent = r[0].book.name + ' ' + st.ch;
      render(r[0], r[1]);
      if (r[0].audio) { audio.src = r[0].audio; audio.hidden = false; } else { audio.removeAttribute('src'); audio.hidden = true; }
      save(); setHash();
      if (pendingVerse) {
        var t = $('v' + pendingVerse);
        if (t) { t.classList.add('is-hl'); t.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        pendingVerse = 0;
      } else if (scroll) { $('read').scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    }).catch(function () {
      if (my !== reqId) { return; }
      text.innerHTML = '';
      text.appendChild(el('p', 'dck-rerr', books[st.book] ? (I.error || 'Error') : (I.notIn || 'Not available')));
    });
  }

  function switchTr(tr, keepPlace) {
    st.tr = tr; selTr.value = tr;
    return loadBooks(tr).then(function (d) {
      fillBooks(d);
      credit.innerHTML = '';
      if (d.license || d.website) {
        credit.appendChild(document.createTextNode((verName[tr] ? verName[tr].name : tr) + ' - '));
        var a = el('a', '', 'license / source'); a.href = d.license || d.website; a.target = '_blank'; a.rel = 'noopener';
        credit.appendChild(a);
        credit.appendChild(document.createTextNode('. Text via the Free Use Bible API (bible.helloao.org).'));
      }
      text.dir = d.dir === 'rtl' ? 'rtl' : 'ltr';
      if (!books[st.book]) { st.book = books.JHN ? 'JHN' : (books.MAT ? 'MAT' : d.books[0].id); st.ch = 1; }
      if (books[st.book] && st.ch > books[st.book].chapters) { st.ch = 1; }
      open(!keepPlace);
    }).catch(function () { text.innerHTML = ''; text.appendChild(el('p', 'dck-rerr', I.error || 'Error')); });
  }

  function step(dir) {
    var ids = Object.keys(books).sort(function (x, y) { return books[x].order - books[y].order; });
    var i = ids.indexOf(st.book);
    if (dir > 0) {
      if (st.ch < books[st.book].chapters) { st.ch++; } else if (i < ids.length - 1) { st.book = ids[i + 1]; st.ch = 1; } else { return; }
    } else {
      if (st.ch > 1) { st.ch--; } else if (i > 0) { st.book = ids[i - 1]; st.ch = books[st.book].chapters; } else { return; }
    }
    open(true);
  }

  /* Controls */
  selTr.addEventListener('change', function () { switchTr(selTr.value, true); });
  selTr2.addEventListener('change', function () { st.tr2 = selTr2.value; open(false); });
  selBook.addEventListener('change', function () { st.book = selBook.value; st.ch = 1; open(false); });
  selCh.addEventListener('change', function () { st.ch = parseInt(selCh.value, 10) || 1; open(false); });
  ['dck-r-prev', 'dck-r-prev2'].forEach(function (id) { var b = $(id); if (b) { b.addEventListener('click', function () { step(-1); }); } });
  ['dck-r-next', 'dck-r-next2'].forEach(function (id) { var b = $(id); if (b) { b.addEventListener('click', function () { step(1); }); } });
  document.addEventListener('keydown', function (e) {
    if (/input|textarea|select/i.test((e.target.tagName || ''))) { return; }
    if (e.key === 'ArrowRight' && e.altKey) { step(1); }
    if (e.key === 'ArrowLeft' && e.altKey) { step(-1); }
  });

  $('dck-r-go').addEventListener('submit', function (e) {
    e.preventDefault();
    var q = $('dck-r-ref').value.trim();
    var m = q.match(/^\s*([1-3]?\s*[^\d:.]+?)\s*(\d+)?(?:\s*[:.]\s*(\d+))?\s*$/);
    var code = m ? findBook(m[1]) : null;
    if (!code || !books[code]) { text.insertBefore(el('p', 'dck-rerr', I.notFound || ''), text.firstChild); return; }
    st.book = code; st.ch = Math.min(parseInt(m[2] || '1', 10), books[code].chapters); pendingVerse = parseInt(m[3] || '0', 10);
    open(true);
  });

  function applyLook() {
    text.style.setProperty('--dck-rsize', st.size);
    $('read').classList.toggle('is-night', !!st.night);
    $('dck-r-mode').setAttribute('aria-pressed', st.night ? 'true' : 'false');
  }
  $('dck-r-bigger').addEventListener('click', function () { st.size = Math.min(1.6, +(st.size + 0.1).toFixed(1)); applyLook(); save(); });
  $('dck-r-smaller').addEventListener('click', function () { st.size = Math.max(0.8, +(st.size - 0.1).toFixed(1)); applyLook(); save(); });
  $('dck-r-mode').addEventListener('click', function () { st.night = !st.night; applyLook(); save(); });

  /* Listen (device voice) */
  var speaking = false;
  $('dck-r-listen').addEventListener('click', function () {
    var b = this;
    if (!('speechSynthesis' in window)) { return; }
    if (speaking) { speechSynthesis.cancel(); speaking = false; b.classList.remove('is-active'); return; }
    var words = [];
    text.querySelectorAll('.dck-rrow .dck-rcol:first-child').forEach(function (c) { words.push(c.textContent.replace(/^\s*\d+\s*/, '')); });
    var u = new SpeechSynthesisUtterance(title.textContent + '. ' + words.join(' '));
    var lang = (verName[st.tr] || {}).lang || '';
    u.lang = /swahili|kiswahili/i.test(lang) ? 'sw-KE' : 'en';
    u.rate = 0.95;
    u.onend = function () { speaking = false; b.classList.remove('is-active'); };
    speechSynthesis.cancel(); speechSynthesis.speak(u); speaking = true; b.classList.add('is-active');
  });

  function ref(v) { return title.textContent + (v ? ':' + v : '') + ' (' + ((verName[st.tr] || {}).short || st.tr) + ')'; }
  function link(v) { return location.origin + location.pathname + '#read=' + st.tr + '/' + st.book + '/' + st.ch + (v ? '/' + v : ''); }
  function flash(btn) { var o = btn.textContent; btn.textContent = I.copied || 'Copied'; setTimeout(function () { btn.textContent = o; }, 1300); }
  function copy(t, btn) { if (navigator.clipboard) { navigator.clipboard.writeText(t).then(function () { flash(btn); }); } }
  $('dck-r-share').addEventListener('click', function () { window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(ref() + ' ' + link()), '_blank', 'noopener'); });

  /* Verse actions */
  var curV = null;
  function showPop(row) {
    text.querySelectorAll('.is-sel').forEach(function (x) { x.classList.remove('is-sel'); });
    row.classList.add('is-sel'); curV = row;
    pop.hidden = false;
    var r = row.getBoundingClientRect(), host = $('read').getBoundingClientRect();
    pop.style.top = (r.bottom - host.top + 6) + 'px';
    pop.style.left = Math.max(8, Math.min(r.left - host.left, host.width - pop.offsetWidth - 8)) + 'px';
  }
  text.addEventListener('click', function (e) { var row = e.target.closest('.dck-rrow[data-v]'); if (row) { showPop(row); } });
  text.addEventListener('keydown', function (e) { if (e.key === 'Enter') { var row = e.target.closest('.dck-rrow[data-v]'); if (row) { showPop(row); } } });
  pop.addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b || !curV) { return; }
    var v = curV.getAttribute('data-v');
    var t = curV.querySelector('.dck-rcol').textContent.replace(/^\s*\d+\s*/, '').trim();
    var act = b.getAttribute('data-act');
    if (act === 'copy') { copy('"' + t + '" ' + ref(v), b); return; }
    if (act === 'share') { window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent('"' + t + '" ' + ref(v) + ' ' + link(v)), '_blank', 'noopener'); }
    if (act === 'ask') {
      var inp = $('dck-bible-input'), go = $('dck-bible-go');
      if (inp && go) { inp.value = title.textContent + ':' + v + ' - "' + t + '"'; $('ask').scrollIntoView({ behavior: 'smooth' }); go.click(); }
    }
    pop.hidden = true; curV.classList.remove('is-sel');
  });
  document.addEventListener('click', function (e) { if (!pop.hidden && !e.target.closest('#dck-vpop') && !e.target.closest('.dck-rrow')) { pop.hidden = true; } });

  /* Verse of the day (same verse for everyone each day, in the reader's version) */
  function votd() {
    var list = R.votd || []; if (!list.length) { return; }
    var day = Math.floor(Date.now() / 86400000);
    var p = String(list[day % list.length]).split(/\s+/);
    if (p.length < 3) { return; }
    loadChapter(st.tr, p[0], parseInt(p[1], 10)).then(function (d) {
      var vt = '';
      d.items.forEach(function (it) { if (it.n === parseInt(p[2], 10)) { vt = it.t; } });
      if (!vt) { return; }
      $('dck-votd-t').textContent = vt;
      $('dck-votd-r').textContent = d.book.name + ' ' + p[1] + ':' + p[2] + ' (' + ((verName[st.tr] || {}).short || st.tr) + ')';
      $('dck-votd').hidden = false;
      $('dck-votd-open').onclick = function () { st.book = p[0]; st.ch = parseInt(p[1], 10); pendingVerse = parseInt(p[2], 10); open(true); };
    }).catch(function () {});
  }

  /* Start: deep link #read=TR/BOOK/CH[/VERSE or TR2] */
  var h = (location.hash || '').match(/^#read=([A-Za-z0-9_]+)\/([1-3A-Z][A-Z0-9]{2})\/(\d+)(?:\/(\w+))?/);
  if (h && verName[h[1]]) {
    st.tr = h[1]; st.book = h[2]; st.ch = parseInt(h[3], 10);
    if (h[4]) { if (/^\d+$/.test(h[4])) { pendingVerse = parseInt(h[4], 10); } else if (verName[h[4]]) { st.tr2 = h[4]; } }
  }
  selTr2.value = st.tr2 || '';
  applyLook();
  switchTr(st.tr, !h).then(votd);
})();
