/* DCK church app service worker (v__VERSION__). Served from /?dck_sw=1 by inc/pwa.php. */
var VERSION = '__VERSION__';
var PAGES = 'dck-pages-' + VERSION;
var ASSETS = 'dck-assets-' + VERSION;
var OFFLINE = '__OFFLINE__';
var PRECACHE = __PRECACHE__;

self.addEventListener('install', function (e) {
	e.waitUntil(caches.open(ASSETS).then(function (c) { return c.addAll(PRECACHE); }).then(function () { return self.skipWaiting(); }));
});

self.addEventListener('activate', function (e) {
	e.waitUntil(caches.keys().then(function (keys) {
		return Promise.all(keys.filter(function (k) { return k.indexOf('dck-') === 0 && k !== PAGES && k !== ASSETS; }).map(function (k) { return caches.delete(k); }));
	}).then(function () { return self.clients.claim(); }));
});

function skip(url) {
	return /\/wp-(admin|login|json)|[?&](dck_sw|dck_manifest|preview|s)=|wc-ajax|\/feed\b|admin-ajax/.test(url.pathname + url.search);
}

function trim(cacheName, max) {
	caches.open(cacheName).then(function (c) {
		c.keys().then(function (keys) { if (keys.length > max) { c.delete(keys[0]).then(function () { trim(cacheName, max); }); } });
	});
}

self.addEventListener('fetch', function (e) {
	var req = e.request;
	if (req.method !== 'GET') { return; }
	var url = new URL(req.url);
	if (url.origin !== self.location.origin || skip(url)) { return; }

	// Pages: network first, fall back to the last copy, then the offline page.
	if (req.mode === 'navigate') {
		e.respondWith(fetch(req).then(function (res) {
			if (res.ok && res.type === 'basic') {
				var copy = res.clone();
				caches.open(PAGES).then(function (c) { c.put(req, copy); trim(PAGES, 40); });
			}
			return res;
		}).catch(function () {
			return caches.match(req).then(function (hit) { return hit || caches.match(OFFLINE); });
		}));
		return;
	}

	// Theme files, images and fonts: cache first, refresh in the background.
	if (/\.(css|js|woff2?|png|jpe?g|webp|svg|gif|ico)$/i.test(url.pathname)) {
		e.respondWith(caches.open(ASSETS).then(function (c) {
			return c.match(req).then(function (hit) {
				var net = fetch(req).then(function (res) { if (res.ok) { c.put(req, res.clone()); trim(ASSETS, 150); } return res; }).catch(function () { return hit; });
				return hit || net;
			});
		}));
	}
});
