/* DCK app: register the service worker and offer "Install". */
(function () {
	'use strict';
	var cfg = window.DCK_PWA || {};
	if ('serviceWorker' in navigator && cfg.sw && location.protocol === 'https:') {
		window.addEventListener('load', function () {
			navigator.serviceWorker.register(cfg.sw, { scope: '/' }).catch(function () {});
		});
	}
	var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
	if (standalone) { return; }
	var boxes = document.querySelectorAll('.dck-install');
	if (!boxes.length) { return; }
	var deferred = null;
	var ua = navigator.userAgent || '';
	var isIOS = /iphone|ipad|ipod/i.test(ua) && !window.MSStream;
	function show(hint) {
		for (var i = 0; i < boxes.length; i++) {
			boxes[i].hidden = false;
			if (hint) {
				var h = boxes[i].querySelector('.dck-install__hint');
				var b = boxes[i].querySelector('.dck-install__btn');
				if (h) { h.textContent = hint; }
				if (b) { b.hidden = true; }
			}
		}
	}
	window.addEventListener('beforeinstallprompt', function (e) {
		e.preventDefault();
		deferred = e;
		show('');
	});
	if (isIOS) { show('Tap the Share button, then "Add to Home Screen"'); }
	document.addEventListener('click', function (e) {
		var b = e.target.closest ? e.target.closest('.dck-install__btn') : null;
		if (!b || !deferred) { return; }
		deferred.prompt();
		deferred.userChoice.then(function () {
			deferred = null;
			for (var i = 0; i < boxes.length; i++) { boxes[i].hidden = true; }
		});
	});
	window.addEventListener('appinstalled', function () { for (var i = 0; i < boxes.length; i++) { boxes[i].hidden = true; } });
})();
