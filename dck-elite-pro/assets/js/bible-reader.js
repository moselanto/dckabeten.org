/* DCK Bible Reader v2: full-page app layout, book navigator, compare, verse actions, verse of the day. */
(function () {
  'use strict';
  var R = window.DCK_READER;
  if (!R) { return; }
  var $ = function (id) { return document.getElementById(id); };
  var reader = $('dck-reader'), selTr = $('dck-r-tr'), selTr2 = $('dck-r-tr2');
  var text = $('dck-r-text'), title = $('dck-r-title'), sub = $('dck-r-sub'), credit = $('dck-r-credit');
  var side = $('dck-r-side'), scrim = $('dck-r-scrim'), blist = $('dck-r-blist'), bfilter = $('dck-r-bfilter');
  var pop = $('dck-vpop'), audio = $('dck-r-audio');
  if (!reader || !selTr || !text) { return; }
  var I = R.i18n || {};
  var LS = 'dck_reader_v1';
  var st = { tr: R.default || 'BSB', tr2: '', book: 'JHN', ch: 1, size: 1, night: false, votdHidden: '', verse: false, sans: false };
  var MK = 'dck_reader_marks';
  var marks = { hl: {}, bm: [], recent: [] };
  try { var mm = JSON.parse(localStorage.getItem(MK) || '{}'); marks.hl = mm.hl || {}; marks.bm = mm.bm || []; marks.recent = mm.recent || []; } catch (e) {}
  function saveMarks() { try { localStorage.setItem(MK, JSON.stringify(marks)); } catch (e) {} }
  try { var saved = JSON.parse(localStorage.getItem(LS) || '{}'); for (var k in saved) { st[k] = saved[k]; } } catch (e) {}
  var books = {}, order = [], bookCache = {}, chapterCache = {}, verName = {}, testament = 'all';
  (R.versions || []).forEach(function (v) { verName[v.id] = v; });
  if (!verName[st.tr]) { st.tr = (R.versions[0] || {}).id; }
  if (st.tr2 && !verName[st.tr2]) { st.tr2 = ''; }

  var CODES = 'GEN EXO LEV NUM DEU JOS JDG RUT 1SA 2SA 1KI 2KI 1CH 2CH EZR NEH EST JOB PSA PRO ECC SNG ISA JER LAM EZK DAN HOS JOL AMO OBA JON MIC NAM HAB ZEP HAG ZEC MAL MAT MRK LUK JHN ACT ROM 1CO 2CO GAL EPH PHP COL 1TH 2TH 1TI 2TI TIT PHM HEB JAS 1PE 2PE 1JN 2JN 3JN JUD REV'.split(' ');
  var EN = 'genesis exodus leviticus numbers deuteronomy joshua judges ruth 1samuel 2samuel 1kings 2kings 1chronicles 2chronicles ezra nehemiah esther job psalms proverbs ecclesiastes songofsongs isaiah jeremiah lamentations ezekiel daniel hosea joel amos obadiah jonah micah nahum habakkuk zephaniah haggai zechariah malachi matthew mark luke john acts romans 1corinthians 2corinthians galatians ephesians philippians colossians 1thessalonians 2thessalonians 1timothy 2timothy titus philemon hebrews james 1peter 2peter 1john 2john 3john jude revelation'.split(' ');
  var SW = 'mwanzo kutoka walawi hesabu kumbukumbu yoshua waamuzi ruthu 1samweli 2samweli 1wafalme 2wafalme 1mambo 2mambo ezra nehemia esta ayubu zaburi mithali mhubiri wimbo isaya yeremia maombolezo ezekieli danieli hosea yoeli amosi obadia yona mika nahumu habakuki sefania hagai zekaria malaki mathayo marko luka yohana matendo warumi 1wakorintho 2wakorintho wagalatia waefeso wafilipi wakolosai 1wathesalonike 2wathesalonike 1timotheo 2timotheo tito filemoni waebrania yakobo 1petro 2petro 1yohana 2yohana 3yohana yuda ufunuo'.split(' ');
  var ALIAS = { psalm: 'PSA', ps: 'PSA', song: 'SNG', songs: 'SNG', rev: 'REV', revelations: 'REV', gen: 'GEN', ex: 'EXO', exo: 'EXO', matt: 'MAT', mt: 'MAT', mk: 'MRK', lk: 'LUK', jn: 'JHN', rom: 'ROM', phil: 'PHP', eph: 'EPH', heb: 'HEB', prov: 'PRO', isa: 'ISA', jer: 'JER', zab: 'PSA' };
  function norm(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]/g, ''); }
  function enName(id) { var i = CODES.indexOf(id); return i < 0 ? '' : EN[i]; }
  function findBook(name) {
    var n = norm(name), l, i, id;
    if (!n) { return null; }
    if (ALIAS[n]) { return ALIAS[n]; }
    var lists = [EN, SW];
    for (l = 0; l < 2; l++) { for (i = 0; i < CODES.length; i++) { if (lists[l][i] === n) { return CODES[i]; } } }
    for (id in books) { if (norm(books[id].name) === n) { return id; } }
    for (l = 0; l < 2; l++) { for (i = 0; i < CODES.length; i++) { if (n.length >= 3 && lists[l][i].indexOf(n) === 0) { return CODES[i]; } } }
    for (id in books) { if (n.length >= 3 && norm(books[id].name).indexOf(n) === 0) { return id; } }
    return null;
  }

  function save() { try { localStorage.setItem(LS, JSON.stringify(st)); } catch (e) {} }
  function api(p) { return fetch(R.rest + p).then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); }); }
  function loadBooks(tr) { return bookCache[tr] ? Promise.resolve(bookCache[tr]) : api('books?tr=' + encodeURIComponent(tr)).then(function (d) { bookCache[tr] = d; return d; }); }
  function loadChapter(tr, b, c) {
    var key = tr + '/' + b + '/' + c;
    return chapterCache[key] ? Promise.resolve(chapterCache[key]) : api('chapter?tr=' + encodeURIComponent(tr) + '&book=' + b + '&ch=' + c).then(function (d) { chapterCache[key] = d; return d; });
  }
  function el(tag, cls, txt) { var e = document.createElement(tag); if (cls) { e.className = cls; } if (txt !== undefined) { e.textContent = txt; } return e; }
  function isMobile() { return window.matchMedia('(max-width: 1023px)').matches; }

  /* ---------- Book navigator ---------- */
  function renderSaved() {
    blist.innerHTML = '';
    var box = function (title, list, empty) {
      blist.appendChild(el('p', 'dck-rside__grp', title));
      if (!list.length) { blist.appendChild(el('p', 'dck-rside__empty', empty)); return; }
      list.forEach(function (it) {
        var b = el('button', 'dck-rsaved');
        b.type = 'button';
        b.appendChild(el('strong', '', it.label));
        if (it.t) { b.appendChild(el('span', '', it.t.length > 90 ? it.t.slice(0, 90) + '...' : it.t)); }
        b.addEventListener('click', function () {
          var p = it.ref.split('/');
          if (!books[p[0]]) { return; }
          pendingVerse = parseInt(p[2] || '0', 10);
          go(p[0], parseInt(p[1], 10));
        });
        if (it.del) {
          var x = el('span', 'dck-rsaved__x', '\u00d7'); x.setAttribute('role', 'button'); x.setAttribute('aria-label', 'Remove');
          x.addEventListener('click', function (e) { e.stopPropagation(); marks.bm = marks.bm.filter(function (m) { return m.ref !== it.ref; }); saveMarks(); renderSaved(); });
          b.appendChild(x);
        }
        blist.appendChild(b);
      });
    };
    box('Saved verses', marks.bm.map(function (m) { return { ref: m.ref, label: m.label, t: m.t, del: true }; }), 'Tap any verse and choose Save to keep it here.');
    box('Recently read', marks.recent.map(function (r) { return { ref: r.ref, label: r.label }; }), 'Chapters you read will appear here.');
  }
  function renderList() {
    if (testament === 'saved') { renderSaved(); return; }
    blist.innerHTML = '';
    var q = norm(bfilter.value);
    var lastT = '';
    order.forEach(function (id) {
      var b = books[id];
      var t = CODES.indexOf(id) > -1 && CODES.indexOf(id) < 39 ? 'ot' : 'nt';
      if (testament !== 'all' && testament !== t) { return; }
      if (q && norm(b.name).indexOf(q) === -1 && enName(id).indexOf(q) === -1) { return; }
      if (testament === 'all' && t !== lastT && !q) { blist.appendChild(el('p', 'dck-rside__grp', t === 'ot' ? 'Old Testament' : 'New Testament')); lastT = t; }
      var wrap = el('div', 'dck-rbook' + (id === st.book ? ' is-open' : ''));
      var btn = el('button', 'dck-rbook__btn');
      btn.type = 'button';
      btn.setAttribute('aria-expanded', id === st.book ? 'true' : 'false');
      btn.appendChild(el('span', 'dck-rbook__name', b.name));
      btn.appendChild(el('span', 'dck-rbook__n', String(b.chapters)));
      btn.addEventListener('click', function () {
        var open = wrap.classList.contains('is-open');
        blist.querySelectorAll('.dck-rbook.is-open').forEach(function (x) { if (x !== wrap) { x.classList.remove('is-open'); x.firstChild.setAttribute('aria-expanded', 'false'); } });
        wrap.classList.toggle('is-open', !open);
        btn.setAttribute('aria-expanded', !open ? 'true' : 'false');
        if (b.chapters === 1 && !open) { go(id, 1); }
      });
      wrap.appendChild(btn);
      var grid = el('div', 'dck-rchgrid');
      for (var c = 1; c <= b.chapters; c++) {
        var cb = el('button', 'dck-rch' + (id === st.book && c === st.ch ? ' is-cur' : ''), String(c));
        cb.type = 'button';
        cb.setAttribute('data-c', c);
        cb.addEventListener('click', function () { go(id, parseInt(this.getAttribute('data-c'), 10)); });
        grid.appendChild(cb);
      }
      wrap.appendChild(grid);
      blist.appendChild(wrap);
    });
  }
  function revealCurrent() {
    var cur = blist.querySelector('.dck-rbook.is-open');
    if (cur && !isMobile()) { blist.scrollTop = cur.offsetTop - 60; }
    if (cur && isMobile()) { setTimeout(function () { cur.scrollIntoView({ block: 'start' }); }, 50); }
  }
  bfilter.addEventListener('input', renderList);
  side.querySelectorAll('.dck-rside__tab').forEach(function (t) {
    t.addEventListener('click', function () {
      testament = t.getAttribute('data-test');
      side.querySelectorAll('.dck-rside__tab').forEach(function (x) { x.classList.toggle('is-active', x === t); x.setAttribute('aria-selected', x === t ? 'true' : 'false'); });
      renderList();
    });
  });
  function openNav() { reader.classList.add('nav-open'); scrim.hidden = false; $('dck-r-navbtn').setAttribute('aria-expanded', 'true'); document.documentElement.classList.add('dck-noscroll'); revealCurrent(); }
  function closeNav() { reader.classList.remove('nav-open'); scrim.hidden = true; $('dck-r-navbtn').setAttribute('aria-expanded', 'false'); document.documentElement.classList.remove('dck-noscroll'); }
  $('dck-r-navbtn').addEventListener('click', function () {
    if (isMobile()) { if (reader.classList.contains('nav-open')) { closeNav(); } else { openNav(); } }
    else { reader.classList.toggle('side-hidden'); $('dck-r-navbtn').setAttribute('aria-expanded', reader.classList.contains('side-hidden') ? 'false' : 'true'); }
  });
  $('dck-r-bcur').addEventListener('click', openNav);
  $('dck-r-sideclose').addEventListener('click', closeNav);
  scrim.addEventListener('click', closeNav);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeNav(); pop.hidden = true; } });

  var moreBtn = $('dck-r-morebtn');
  moreBtn.addEventListener('click', function () {
    var on = !reader.classList.contains('more-open');
    reader.classList.toggle('more-open', on);
    moreBtn.setAttribute('aria-expanded', on ? 'true' : 'false');
  });

  /* ---------- Chapter rendering ---------- */
  function render(a, b) {
    text.innerHTML = '';
    text.classList.toggle('is-compare', !!b);
    var bv = {};
    if (b) {
      b.items.forEach(function (it) { if (it.n) { bv[it.n] = it.t; } });
      var hd = el('div', 'dck-rrow dck-rrow--head');
      hd.appendChild(el('span', 'dck-rcol', (verName[st.tr] || {}).name || st.tr));
      hd.appendChild(el('span', 'dck-rcol', (verName[st.tr2] || {}).name || st.tr2));
      text.appendChild(hd);
    }
    a.items.forEach(function (it) {
      if (it.h) { text.appendChild(el('h3', 'dck-rhead', it.h)); return; }
      var row = el(b ? 'div' : 'span', 'dck-rrow');
      row.setAttribute('data-v', it.n); row.id = 'v' + it.n; row.tabIndex = 0;
      var hk = st.book + '/' + st.ch + '/' + it.n;
      if (marks.hl[hk]) { row.classList.add('hl-' + marks.hl[hk]); }
      if (marks.bm.some(function (m) { return m.ref === hk; })) { row.classList.add('is-bm'); }
      var c1 = el('span', 'dck-rcol');
      c1.appendChild(el('sup', 'dck-vnum', String(it.n)));
      c1.appendChild(document.createTextNode(it.t + ' '));
      row.appendChild(c1);
      if (b) {
        var c2 = el('span', 'dck-rcol dck-rcol--b');
        c2.appendChild(el('sup', 'dck-vnum', String(it.n)));
        c2.appendChild(document.createTextNode(bv[it.n] || ''));
        row.appendChild(c2);
      }
      text.appendChild(row);
    });
  }
  function labelFor(id, c) { return (books[id] ? books[id].name : id) + ' ' + c; }
  function neighbours() {
    var i = order.indexOf(st.book), p = null, n = null;
    if (st.ch > 1) { p = [st.book, st.ch - 1]; } else if (i > 0) { p = [order[i - 1], books[order[i - 1]].chapters]; }
    if (books[st.book] && st.ch < books[st.book].chapters) { n = [st.book, st.ch + 1]; } else if (i > -1 && i < order.length - 1) { n = [order[i + 1], 1]; }
    return { p: p, n: n };
  }
  function updateChrome() {
    var cur = labelFor(st.book, st.ch);
    $('dck-r-cur').textContent = cur;
    $('dck-r-bcur').textContent = cur;
    var nb = neighbours();
    ['dck-r-prev', 'dck-r-prev2', 'dck-r-bprev'].forEach(function (id) { $(id).disabled = !nb.p; });
    ['dck-r-next', 'dck-r-next2', 'dck-r-bnext'].forEach(function (id) { $(id).disabled = !nb.n; });
    $('dck-r-prevlbl').textContent = nb.p ? labelFor(nb.p[0], nb.p[1]) : '';
    $('dck-r-nextlbl').textContent = nb.n ? labelFor(nb.n[0], nb.n[1]) : '';
    blist.querySelectorAll('.dck-rch.is-cur').forEach(function (x) { x.classList.remove('is-cur'); });
    blist.querySelectorAll('.dck-rbook').forEach(function (w) {
      var isCur = w.querySelector('.dck-rch') && w.querySelector('.dck-rbook__name').textContent === (books[st.book] || {}).name;
      if (isCur) { w.classList.add('is-open'); var c = w.querySelector('.dck-rch[data-c="' + st.ch + '"]'); if (c) { c.classList.add('is-cur'); } }
    });
  }
  function setHash() { if (history.replaceState) { history.replaceState(null, '', '#read=' + st.tr + '/' + st.book + '/' + st.ch + (st.tr2 ? '/' + st.tr2 : '')); } }
  function scrollToReader() {
    var top = reader.getBoundingClientRect().top + window.pageYOffset - 70;
    if (window.pageYOffset > top || window.pageYOffset < top - 400) { window.scrollTo({ top: top, behavior: 'smooth' }); }
  }

  var reqId = 0, pendingVerse = 0;
  function open(scroll) {
    var my = ++reqId;
    text.innerHTML = '<p class="dck-bible-loading">' + (I.loading || 'Loading...') + '</p>';
    title.textContent = labelFor(st.book, st.ch);
    sub.textContent = (verName[st.tr] || {}).name || '';
    updateChrome();
    var p1 = loadChapter(st.tr, st.book, st.ch);
    var p2 = st.tr2 ? loadChapter(st.tr2, st.book, st.ch).catch(function () { return { items: [] }; }) : Promise.resolve(null);
    Promise.all([p1, p2]).then(function (r) {
      if (my !== reqId) { return; }
      title.textContent = r[0].book.name + ' ' + st.ch;
      sub.textContent = ((verName[st.tr] || {}).name || '') + (st.tr2 ? '  +  ' + ((verName[st.tr2] || {}).name || '') : '');
      render(r[0], r[1]);
      if (r[0].audio) { audio.src = r[0].audio; audio.hidden = false; } else { audio.removeAttribute('src'); audio.hidden = true; }
      save(); setHash();
      var rref = st.book + '/' + st.ch;
      marks.recent = [{ ref: rref, label: r[0].book.name + ' ' + st.ch }].concat(marks.recent.filter(function (x) { return x.ref !== rref; })).slice(0, 8);
      saveMarks();
      if (pendingVerse) {
        var t = $('v' + pendingVerse);
        if (t) { t.classList.add('is-hl'); t.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        pendingVerse = 0;
      } else if (scroll) { scrollToReader(); }
      prefetch();
    }).catch(function () {
      if (my !== reqId) { return; }
      text.innerHTML = '';
      text.appendChild(el('p', 'dck-rerr', books[st.book] ? (I.error || 'Error') : (I.notIn || 'Not available')));
    });
  }
  function prefetch() { var n = neighbours().n; if (n) { loadChapter(st.tr, n[0], n[1]).catch(function () {}); } }
  function go(b, c) { st.book = b; st.ch = c; closeNav(); open(true); }
  function step(d) { var nb = neighbours(); var t = d > 0 ? nb.n : nb.p; if (t) { go(t[0], t[1]); } }

  function switchTr(tr, keepPlace) {
    st.tr = tr; selTr.value = tr;
    return loadBooks(tr).then(function (d) {
      books = {}; order = [];
      d.books.slice().sort(function (x, y) { return x.order - y.order; }).forEach(function (b) { books[b.id] = b; order.push(b.id); });
      credit.innerHTML = '';
      if (d.license || d.website) {
        credit.appendChild(document.createTextNode(((verName[tr] || {}).name || tr) + ' · '));
        var a = el('a', '', 'license / source'); a.href = d.license || d.website; a.target = '_blank'; a.rel = 'noopener';
        credit.appendChild(a);
        credit.appendChild(document.createTextNode(' · Free Use Bible API (bible.helloao.org)'));
      }
      text.dir = d.dir === 'rtl' ? 'rtl' : 'ltr';
      if (!books[st.book]) { st.book = books.JHN ? 'JHN' : (books.MAT ? 'MAT' : order[0]); st.ch = 1; }
      if (st.ch > books[st.book].chapters) { st.ch = 1; }
      renderList();
      open(!keepPlace);
    }).catch(function () { text.innerHTML = ''; text.appendChild(el('p', 'dck-rerr', I.error || 'Error')); });
  }

  /* ---------- Controls ---------- */
  selTr.addEventListener('change', function () { switchTr(selTr.value, true); });
  selTr2.addEventListener('change', function () { st.tr2 = selTr2.value; open(false); });
  [['dck-r-prev', -1], ['dck-r-prev2', -1], ['dck-r-bprev', -1], ['dck-r-next', 1], ['dck-r-next2', 1], ['dck-r-bnext', 1]].forEach(function (p) {
    $(p[0]).addEventListener('click', function () { step(p[1]); });
  });
  document.addEventListener('keydown', function (e) {
    if (/input|textarea|select/i.test(e.target.tagName || '') || e.metaKey || e.ctrlKey) { return; }
    var r = reader.getBoundingClientRect();
    if (r.bottom < 0 || r.top > window.innerHeight) { return; }
    if (e.key === 'ArrowRight') { step(1); }
    if (e.key === 'ArrowLeft') { step(-1); }
  });
  /* Swipe between chapters on touch screens */
  var sx = 0, sy = 0;
  text.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
  text.addEventListener('touchend', function (e) {
    var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
    if (Math.abs(dx) > 80 && Math.abs(dx) > Math.abs(dy) * 2) { step(dx < 0 ? 1 : -1); }
  }, { passive: true });

  $('dck-r-go').addEventListener('submit', function (e) {
    e.preventDefault();
    var q = $('dck-r-ref').value.trim();
    var m = q.match(/^\s*([1-3]?\s*[^\d:.]+?)\s*(\d+)?(?:\s*[:.]\s*(\d+))?\s*$/);
    var code = m ? findBook(m[1]) : null;
    if (!code || !books[code]) { text.insertBefore(el('p', 'dck-rerr', I.notFound || ''), text.firstChild); scrollToReader(); return; }
    pendingVerse = parseInt(m[3] || '0', 10);
    go(code, Math.min(parseInt(m[2] || '1', 10), books[code].chapters));
    $('dck-r-ref').blur();
  });

  function applyLook() {
    text.classList.toggle('is-verse', !!st.verse);
    text.classList.toggle('is-sans', !!st.sans);
    $('dck-r-layout').setAttribute('aria-pressed', st.verse ? 'true' : 'false');
    $('dck-r-font').setAttribute('aria-pressed', st.sans ? 'true' : 'false');
    text.style.setProperty('--dck-rsize', st.size);
    reader.classList.toggle('is-night', !!st.night);
    $('dck-r-mode').setAttribute('aria-pressed', st.night ? 'true' : 'false');
  }
  $('dck-r-bigger').addEventListener('click', function () { st.size = Math.min(1.7, +(st.size + 0.1).toFixed(1)); applyLook(); save(); });
  $('dck-r-smaller').addEventListener('click', function () { st.size = Math.max(0.8, +(st.size - 0.1).toFixed(1)); applyLook(); save(); });
  $('dck-r-mode').addEventListener('click', function () { st.night = !st.night; applyLook(); save(); });
  $('dck-r-layout').addEventListener('click', function () { st.verse = !st.verse; applyLook(); save(); });
  $('dck-r-font').addEventListener('click', function () { st.sans = !st.sans; applyLook(); save(); });
  $('dck-r-full').addEventListener('click', function () {
    var on = !document.documentElement.classList.contains('dck-focus');
    document.documentElement.classList.toggle('dck-focus', on);
    this.setAttribute('aria-pressed', on ? 'true' : 'false');
    scrollToReader();
  });

  var speaking = false;
  $('dck-r-listen').addEventListener('click', function () {
    var b = this;
    if (!('speechSynthesis' in window)) { return; }
    if (speaking) { speechSynthesis.cancel(); speaking = false; b.setAttribute('aria-pressed', 'false'); return; }
    var words = [];
    text.querySelectorAll('.dck-rrow[data-v] .dck-rcol:first-child').forEach(function (c) { words.push(c.textContent.replace(/^\s*\d+\s*/, '')); });
    var u = new SpeechSynthesisUtterance(title.textContent + '. ' + words.join(' '));
    u.lang = /swahili/i.test((verName[st.tr] || {}).lang || '') ? 'sw-KE' : 'en';
    u.rate = 0.95;
    u.onend = function () { speaking = false; b.setAttribute('aria-pressed', 'false'); };
    speechSynthesis.cancel(); speechSynthesis.speak(u); speaking = true; b.setAttribute('aria-pressed', 'true');
  });

  function ref(v) { return title.textContent + (v ? ':' + v : '') + ' (' + ((verName[st.tr] || {}).short || st.tr) + ')'; }
  function link(v) { return location.origin + location.pathname + '#read=' + st.tr + '/' + st.book + '/' + st.ch + (v ? '/' + v : ''); }
  $('dck-r-share').addEventListener('click', function () {
    var url = link(), t = ref();
    if (navigator.share) { navigator.share({ title: t, url: url }).catch(function () {}); return; }
    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(t + ' ' + url), '_blank', 'noopener');
  });

  /* Reading progress */
  var bar = $('dck-r-progress');
  function progress() {
    var r = text.getBoundingClientRect();
    var total = r.height - window.innerHeight * 0.5;
    var p = total > 0 ? Math.min(1, Math.max(0, (-r.top + 120) / total)) : 0;
    bar.style.width = (p * 100) + '%';
  }
  window.addEventListener('scroll', progress, { passive: true });

  /* ---------- Verse actions ---------- */
  var curV = null;
  function showPop(row) {
    text.querySelectorAll('.is-sel').forEach(function (x) { x.classList.remove('is-sel'); });
    row.classList.add('is-sel'); curV = row;
    pop.hidden = false;
    if (isMobile()) { pop.classList.add('is-sheet'); return; }
    pop.classList.remove('is-sheet');
    var r = row.getBoundingClientRect(), host = reader.querySelector('.dck-rmain').getBoundingClientRect();
    var rects = row.getClientRects(), last = rects[rects.length - 1] || r;
    pop.style.top = (last.bottom - host.top + 8) + 'px';
    pop.style.left = Math.max(8, Math.min(last.left - host.left, host.width - pop.offsetWidth - 8)) + 'px';
  }
  text.addEventListener('click', function (e) { var row = e.target.closest('.dck-rrow[data-v]'); if (row) { showPop(row); } });
  text.addEventListener('keydown', function (e) { if (e.key === 'Enter') { var row = e.target.closest('.dck-rrow[data-v]'); if (row) { showPop(row); } } });
  pop.addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b || !curV) { return; }
    var v = curV.getAttribute('data-v');
    var t = curV.querySelector('.dck-rcol').textContent.replace(/^\s*\d+\s*/, '').trim();
    var act = b.getAttribute('data-act');
    if (act === 'copy') {
      if (navigator.clipboard) { navigator.clipboard.writeText('"' + t + '" ' + ref(v)).then(function () { var o = b.textContent; b.textContent = I.copied || 'Copied'; setTimeout(function () { b.textContent = o; }, 1200); }); }
      return;
    }
    var key = st.book + '/' + st.ch + '/' + v;
    if (act === 'hl') {
      var c = b.getAttribute('data-c');
      ['y', 'g', 'b'].forEach(function (x) { curV.classList.remove('hl-' + x); });
      if (c) { marks.hl[key] = c; curV.classList.add('hl-' + c); } else { delete marks.hl[key]; }
      saveMarks(); pop.hidden = true; curV.classList.remove('is-sel'); return;
    }
    if (act === 'bm') {
      if (!marks.bm.some(function (m) { return m.ref === key; })) {
        marks.bm.unshift({ ref: key, label: title.textContent + ':' + v, t: t, tr: st.tr });
        marks.bm = marks.bm.slice(0, 100);
      }
      curV.classList.add('is-bm'); saveMarks();
      if (testament === 'saved') { renderSaved(); }
      var o = b.textContent; b.textContent = '\u2713'; setTimeout(function () { b.textContent = o; }, 1100);
      return;
    }
    if (act === 'img') { verseImage(t, ref(v)); }
    if (act === 'share') { window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent('"' + t + '" ' + ref(v) + ' ' + link(v)), '_blank', 'noopener'); }
    if (act === 'ask') {
      var inp = $('dck-bible-input'), goBtn = $('dck-bible-go');
      if (inp && goBtn) { inp.value = title.textContent + ':' + v + ' - "' + t + '"'; $('ask').scrollIntoView({ behavior: 'smooth' }); goBtn.click(); }
    }
    pop.hidden = true; curV.classList.remove('is-sel');
  });
  document.addEventListener('click', function (e) { if (!pop.hidden && !e.target.closest('#dck-vpop') && !e.target.closest('.dck-rrow')) { pop.hidden = true; if (curV) { curV.classList.remove('is-sel'); } } });

  /* ---------- Share a verse as an image (1080x1080) ---------- */
  function wrap(ctx, text, maxW) {
    var words = text.split(' '), lines = [], line = '';
    words.forEach(function (w) { var test = line ? line + ' ' + w : w; if (ctx.measureText(test).width > maxW && line) { lines.push(line); line = w; } else { line = test; } });
    if (line) { lines.push(line); }
    return lines;
  }
  function verseImage(t, r) {
    var cv = document.createElement('canvas'); cv.width = 1080; cv.height = 1080;
    var x = cv.getContext('2d');
    var g = x.createLinearGradient(0, 0, 1080, 1080); g.addColorStop(0, '#0E1C4D'); g.addColorStop(1, '#24409A');
    x.fillStyle = g; x.fillRect(0, 0, 1080, 1080);
    x.fillStyle = 'rgba(231,200,106,.18)'; x.font = 'bold 420px Georgia, serif'; x.fillText('\u201C', 60, 400);
    var size = t.length > 260 ? 40 : (t.length > 160 ? 48 : 58);
    x.font = size + 'px "Playfair Display", Georgia, serif'; x.fillStyle = '#ffffff'; x.textAlign = 'center';
    var lines = wrap(x, t, 860), lh = size * 1.4, y = 540 - (lines.length * lh) / 2 + size * 0.4;
    lines.forEach(function (l) { x.fillText(l, 540, y); y += lh; });
    x.font = '600 34px Inter, Arial, sans-serif'; x.fillStyle = '#E7C86A'; x.fillText(r, 540, y + 40);
    x.font = '500 26px Inter, Arial, sans-serif'; x.fillStyle = 'rgba(255,255,255,.75)'; x.fillText(location.host, 540, 1010);
    cv.toBlob(function (blob) {
      if (!blob) { return; }
      var file = new File([blob], 'verse.png', { type: 'image/png' });
      if (navigator.canShare && navigator.canShare({ files: [file] })) { navigator.share({ files: [file], title: r }).catch(function () {}); return; }
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = r.replace(/[^\w]+/g, '-') + '.png';
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
    });
  }

  /* ---------- Verse of the day ---------- */
  function votd() {
    var list = R.votd || []; if (!list.length) { return; }
    var day = Math.floor((Date.now() + 3 * 3600000) / 86400000);
    if (st.votdHidden === String(day)) { return; }
    var p = String(list[day % list.length]).split(/\s+/); if (p.length < 3) { return; }
    loadChapter(st.tr, p[0], parseInt(p[1], 10)).then(function (d) {
      var vt = '';
      d.items.forEach(function (it) { if (it.n === parseInt(p[2], 10)) { vt = it.t; } });
      if (!vt) { return; }
      $('dck-votd-t').textContent = vt;
      $('dck-votd-r').textContent = d.book.name + ' ' + p[1] + ':' + p[2] + ' · ' + ((verName[st.tr] || {}).short || st.tr);
      $('dck-votd').hidden = false;
      $('dck-votd-open').onclick = function () { pendingVerse = parseInt(p[2], 10); go(p[0], parseInt(p[1], 10)); };
      $('dck-votd-x').onclick = function () { $('dck-votd').hidden = true; st.votdHidden = String(day); save(); };
    }).catch(function () {});
  }

  /* ---------- Start ---------- */
  var h = (location.hash || '').match(/^#read=([A-Za-z0-9_]+)\/([1-3A-Z][A-Z0-9]{2})\/(\d+)(?:\/(\w+))?/);
  if (h && verName[h[1]]) {
    st.tr = h[1]; st.book = h[2]; st.ch = parseInt(h[3], 10);
    if (h[4]) { if (/^\d+$/.test(h[4])) { pendingVerse = parseInt(h[4], 10); } else if (verName[h[4]]) { st.tr2 = h[4]; } }
  }
  selTr2.value = st.tr2 || '';
  applyLook();
  switchTr(st.tr, !h).then(votd);
})();
