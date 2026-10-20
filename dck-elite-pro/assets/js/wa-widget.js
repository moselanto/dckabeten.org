/* DCK floating WhatsApp chat widget. Reads window.DCK_WAW. */
(function () {
	'use strict';
	var C = window.DCK_WAW || {};
	if (!C.wa) { return; }
	var SEEN = 'dckw_seen', TEASED = 'dckw_teased';
	var root, panel, input, teaser, badge, timer = null, opened = false;
	function q(s) { return root.querySelector(s); }
	function ss(k, v) { try { if (v === undefined) { return sessionStorage.getItem(k); } sessionStorage.setItem(k, v); } catch (e) {} return null; }
	function url(t) { return 'https://wa.me/' + String(C.wa).replace(/\D/g, '') + '?text=' + encodeURIComponent(t); }
	function hourNow() {
		try {
			var p = new Intl.DateTimeFormat('en-GB', { timeZone: C.tz || 'Africa/Nairobi', hour: 'numeric', hour12: false }).formatToParts(new Date());
			for (var i = 0; i < p.length; i++) { if (p[i].type === 'hour') { return parseInt(p[i].value, 10) % 24; } }
		} catch (e) {}
		return new Date().getHours();
	}
	function isOpen() { var h = hourNow(); return h >= (C.open || 8) && h < (C.close || 20); }
	function fmtHour(h) { var ap = h >= 12 ? 'pm' : 'am'; var x = h % 12 || 12; return x + ap; }
	function status() {
		var o = isOpen(), st = q('[data-dckw-status]'), dot = q('[data-dckw-dot]');
		if (st) { st.textContent = o ? (C.online || 'Online') : String(C.away || 'Away · we reply from %s').replace('%s', fmtHour(C.open || 8)); }
		if (dot) { dot.classList.toggle('is-away', !o); }
	}
	function stamp() { var d = new Date(); return (d.getHours() < 10 ? '0' : '') + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes(); }
	function go(t) {
		var u = url(t), w = null;
		if (!window.matchMedia || !window.matchMedia('(pointer: coarse)').matches) { w = window.open(u, '_blank', 'noopener'); }
		if (!w) { window.location.href = u; }
	}
	function hideTeaser() { if (timer) { clearTimeout(timer); timer = null; } if (teaser) { teaser.hidden = true; } }
	function setOpen(s) {
		panel.hidden = !s;
		root.classList.toggle('is-open', s);
		document.documentElement.classList.toggle('dckw-open', s);
		var t = root.querySelectorAll('[data-dckw-toggle]');
		for (var i = 0; i < t.length; i++) { t[i].setAttribute('aria-expanded', s ? 'true' : 'false'); }
		hideTeaser();
		if (s) {
			ss(SEEN, '1');
			if (badge) { badge.hidden = true; }
			status();
			if (!opened) { opened = true; q('[data-dckw-time]').textContent = stamp(); }
			if (input && window.innerWidth > 640) { input.focus(); }
		}
	}
	function init() {
		root = document.querySelector('[data-dckw]');
		if (!root) { return; }
		root.hidden = false;
		panel = q('[data-dckw-panel]'); input = q('[data-dckw-input]'); teaser = q('[data-dckw-teaser]'); badge = q('[data-dckw-badge]');
		q('[data-dckw-intro]').textContent = C.intro || 'Karibu! How can we help you today?';
		if (ss(SEEN) && badge) { badge.hidden = true; }
		var tp = root.querySelectorAll('[data-dckw-topic]');
		for (var i = 0; i < tp.length; i++) { tp[i].setAttribute('href', url(tp[i].getAttribute('data-dckw-topic'))); }
		var tg = root.querySelectorAll('[data-dckw-toggle]');
		for (var j = 0; j < tg.length; j++) { tg[j].addEventListener('click', function (e) { e.preventDefault(); setOpen(panel.hidden); }); }
		var tx = q('[data-dckw-teaser-close]');
		if (tx) { tx.addEventListener('click', function () { hideTeaser(); ss(TEASED, '1'); }); }
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { setOpen(false); } });
		q('[data-dckw-form]').addEventListener('submit', function (e) {
			e.preventDefault();
			var t = (input.value || '').trim();
			if (!t) { return; }
			input.value = '';
			go('Hello DCK, ' + t);
		});
		status();
		if (!ss(SEEN) && !ss(TEASED) && teaser) {
			timer = setTimeout(function () {
				if (panel.hidden && !document.documentElement.classList.contains('dck-menu-open')) { teaser.hidden = false; ss(TEASED, '1'); }
			}, 12000);
		}
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
