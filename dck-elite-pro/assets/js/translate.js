/* Full-site language switcher via Google website translator, loaded on demand. */
function googleTranslateElementInit() {
	new google.translate.TranslateElement(
		{
			pageLanguage: 'en',
			includedLanguages: 'en,sw,fr,ar,de,es,pt,zh-CN,it,ru,hi,nl',
			layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
			autoDisplay: false
		},
		'google_translate_element'
	);
}
(function () {
	var loaded = false;
	function loadGT() {
		if (loaded) { return; }
		loaded = true;
		var s = document.createElement('script');
		s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
		s.async = true;
		document.head.appendChild(s);
	}
	var evs = ['scroll', 'keydown', 'pointerdown', 'touchstart', 'mousemove'];
	function once() { evs.forEach(function (e) { window.removeEventListener(e, once); }); loadGT(); }
	evs.forEach(function (e) { window.addEventListener(e, once, { once: true, passive: true }); });
	document.addEventListener('DOMContentLoaded', function () {
		var host = document.querySelector('.dck-lang');
		if (host) { host.addEventListener('focusin', loadGT, { once: true }); }
	});
})();
