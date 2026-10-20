/* DCK Elite — Unlimited Gallery: lightbox + filter + load-more (v3.7.0) */
(function () {
  'use strict';

  var CFG  = window.DCK_GALLERY || {};
  var grid = document.getElementById('dck-gal-grid');
  if (!grid) { return; }

  /* ── element refs ── */
  var lb       = document.getElementById('dck-lb');
  var lbImg    = document.getElementById('dck-lb-img');
  var lbCap    = document.getElementById('dck-lb-cap');
  var lbPrev   = document.getElementById('dck-lb-prev');
  var lbNext   = document.getElementById('dck-lb-next');
  var lbClose  = document.getElementById('dck-lb-close');
  var lbBack   = document.getElementById('dck-lb-back');
  var moreWrap = document.getElementById('dck-gal-more');
  var moreBtn  = document.getElementById('dck-gal-loadmore');
  var moreCnt  = document.getElementById('dck-gal-count');

  var lbIdx     = 0;
  var activeCat = '';
  var activePg  = 1;
  var busy      = false;

  /* ── helpers ── */
  function items() {
    return Array.prototype.slice.call(grid.querySelectorAll('.dck-gal-item'));
  }

  /* ── LIGHTBOX ─────────────────────────────────────────────────────────── */
  function lbRender() {
    var all = items();
    var el  = all[lbIdx];
    if (!el) { return; }
    lbImg.removeAttribute('src');
    lbImg.alt = el.dataset.caption || '';
    lbImg.src = el.dataset.full;
    if (lbCap) { lbCap.textContent = el.dataset.caption || ''; }
    var cnt = document.getElementById('dck-lb-count');
    if (cnt) { cnt.textContent = (lbIdx + 1) + ' / ' + all.length; }
    var dl = document.getElementById('dck-lb-dl');
    if (dl) { dl.href = el.dataset.full; }
    if (lbPrev) { lbPrev.style.visibility = (lbIdx === 0) ? 'hidden' : ''; }
    if (lbNext) { lbNext.style.visibility = (lbIdx === all.length - 1) ? 'hidden' : ''; }
  }

  function lbOpen(idx) {
    var all = items();
    lbIdx = Math.max(0, Math.min(idx, all.length - 1));
    lbRender();
    lb.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
    if (lbClose) { lbClose.focus(); }
  }

  function lbDismiss() {
    lb.setAttribute('hidden', '');
    document.body.style.overflow = '';
    var all = items();
    if (all[lbIdx]) { all[lbIdx].focus(); }
  }

  function lbStep(d) {
    var all = items();
    var n   = lbIdx + d;
    if (n < 0 || n >= all.length) { return; }
    lbIdx = n;
    lbRender();
  }

  if (lbClose) { lbClose.addEventListener('click', lbDismiss); }
  var lbShare = document.getElementById('dck-lb-share');
  if (lbShare) {
    lbShare.addEventListener('click', function () {
      var el = items()[lbIdx]; if (!el) { return; }
      var url = el.dataset.full, title = el.dataset.caption || document.title;
      if (navigator.share) { navigator.share({ title: title, url: url }).catch(function () {}); return; }
      window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(title + ' ' + url), '_blank', 'noopener');
    });
  }
  if (lbBack)  { lbBack.addEventListener('click', lbDismiss); }
  if (lbPrev)  { lbPrev.addEventListener('click', function () { lbStep(-1); }); }
  if (lbNext)  { lbNext.addEventListener('click', function () { lbStep(1); }); }

  document.addEventListener('keydown', function (e) {
    if (!lb || lb.hasAttribute('hidden')) { return; }
    switch (e.key) {
      case 'Escape':     lbDismiss(); break;
      case 'ArrowLeft':  lbStep(-1);  break;
      case 'ArrowRight': lbStep(1);   break;
    }
  });

  /* Touch swipe */
  var tX = 0;
  if (lb) {
    lb.addEventListener('touchstart', function (e) {
      tX = e.changedTouches[0].clientX;
    }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - tX;
      if (Math.abs(dx) > 48) { lbStep(dx < 0 ? 1 : -1); }
    }, { passive: true });
  }

  /* ── Grid interaction (event delegation) ─────────────────────────────── */
  function handleGridActivate(e) {
    var el = e.target.closest ? e.target.closest('.dck-gal-item') : null;
    if (!el) { return; }
    if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') { return; }
    if (e.type === 'keydown') { e.preventDefault(); }
    var idx = items().indexOf(el);
    if (idx >= 0) { lbOpen(idx); }
  }
  grid.addEventListener('click', handleGridActivate);
  grid.addEventListener('keydown', handleGridActivate);

  /* ── Category filter ─────────────────────────────────────────────────── */
  var filterBtns = Array.prototype.slice.call(document.querySelectorAll('.dck-gf-btn'));
  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterBtns.forEach(function (b) {
        b.classList.remove('is-active');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('is-active');
      btn.setAttribute('aria-selected', 'true');
      activeCat = btn.getAttribute('data-cat') || '';
      activePg  = 1;
      fetchPhotos(1, activeCat, true);
    });
  });

  /* ── Load more ───────────────────────────────────────────────────────── */
  if (moreBtn) {
    moreBtn.addEventListener('click', function () {
      if (busy) { return; }
      fetchPhotos(activePg + 1, activeCat, false);
    });
  }

  /* ── Build a gallery card element ────────────────────────────────────── */
  function buildCard(p) {
    var el = document.createElement('div');
    el.className = 'dck-gal-item' + (p.shape ? ' is-' + p.shape : '');
    el.dataset.full    = p.full;
    el.dataset.caption = p.caption || '';
    el.setAttribute('tabindex', '0');
    el.setAttribute('role', 'button');
    el.setAttribute('aria-label', p.caption || 'View photo');

    var img = new Image();
    img.src      = p.thumb;
    img.alt      = p.caption || '';
    img.loading  = 'lazy';
    img.decoding = 'async';
    el.appendChild(img);

    if (p.caption) {
      var cap = document.createElement('div');
      cap.className = 'dck-gal-caption';
      var sp = document.createElement('span');
      sp.textContent = p.caption;
      cap.appendChild(sp);
      el.appendChild(cap);
    }

    var ov = document.createElement('div');
    ov.className = 'dck-gal-overlay';
    ov.setAttribute('aria-hidden', 'true');
    ov.innerHTML = '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>';
    el.appendChild(ov);
    return el;
  }

  /* ── AJAX fetch (filter or load-more) ───────────────────────────────── */
  function fetchPhotos(page, cat, replace) {
    busy = true;
    var base = CFG.rest || '/wp-json/dck/v1/';
    var url  = base + 'gallery?page=' + page + '&per_page=20';
    if (cat) { url += '&category=' + encodeURIComponent(cat); }
    if (moreBtn) {
      moreBtn.disabled    = true;
      moreBtn.textContent = 'Loading…';
    }

    fetch(url, { headers: { 'X-WP-Nonce': CFG.nonce || '' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var photos = Array.isArray(data.photos) ? data.photos : [];

        if (replace) {
          grid.innerHTML = '';
          if (photos.length === 0) {
            grid.innerHTML = '<div class="dck-gal-empty"><p>No photos in this category yet.</p></div>';
          }
        }

        photos.forEach(function (p) {
          var el = buildCard(p);
          grid.appendChild(el);
          /* double-rAF ensures layout pass before CSS transition fires */
          requestAnimationFrame(function () {
            requestAnimationFrame(function () { el.classList.add('dck-gal-visible'); });
          });
        });

        activePg = data.page || page;
        var shown = items().length;
        if (moreBtn) {
          moreBtn.disabled    = false;
          moreBtn.textContent = 'Load More Photos';
        }
        if (moreCnt && data.total) {
          moreCnt.textContent = 'Showing ' + shown + ' of ' + data.total + ' photos';
        }
        if (moreWrap) {
          moreWrap.style.display = data.has_more ? '' : 'none';
        }
        busy = false;
      })
      .catch(function () {
        if (moreBtn) {
          moreBtn.disabled    = false;
          moreBtn.textContent = 'Load More Photos';
        }
        busy = false;
      });
  }

  /* ── Scroll-in animation for server-rendered initial items ──────────── */
  if (window.IntersectionObserver) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('dck-gal-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });
    items().forEach(function (el) {
      if (!el.classList.contains('dck-gal-visible')) { io.observe(el); }
    });
  }
})();
