(function () {
	'use strict';
	var root = document.documentElement;
	var wrap = document.getElementById('dck-a11y');
	if (!wrap) { return; }
	var toggle = wrap.querySelector('.dck-a11y__toggle');
	var panel = document.getElementById('dck-a11y-panel');
	if (!toggle || !panel) { return; }

	var STEP = 'dckA11yStep', CONTRAST = 'dckA11yContrast', LINKS = 'dckA11yLinks';
	var step = parseInt(localStorage.getItem(STEP) || '0', 10) || 0;

	function applyStep() {
		root.classList.remove('dck-txt-1', 'dck-txt-2', 'dck-txt-3');
		if (step > 0) { root.classList.add('dck-txt-' + step); }
	}
	function applyFlag(key, cls) {
		if (localStorage.getItem(key) === '1') { root.classList.add(cls); }
		else { root.classList.remove(cls); }
	}
	function openPanel(open) {
		if (open) { panel.removeAttribute('hidden'); toggle.setAttribute('aria-expanded', 'true'); }
		else { panel.setAttribute('hidden', ''); toggle.setAttribute('aria-expanded', 'false'); }
	}

	applyStep();
	applyFlag(CONTRAST, 'dck-contrast');
	applyFlag(LINKS, 'dck-ulinks');

	toggle.addEventListener('click', function () {
		openPanel(panel.hasAttribute('hidden'));
	});

	panel.addEventListener('click', function (e) {
		var b = e.target.closest('button[data-a11y]');
		if (!b) { return; }
		var a = b.getAttribute('data-a11y');
		if (a === 'text-up') { step = Math.min(3, step + 1); localStorage.setItem(STEP, step); applyStep(); }
		else if (a === 'text-down') { step = Math.max(0, step - 1); localStorage.setItem(STEP, step); applyStep(); }
		else if (a === 'contrast') { localStorage.setItem(CONTRAST, localStorage.getItem(CONTRAST) === '1' ? '0' : '1'); applyFlag(CONTRAST, 'dck-contrast'); }
		else if (a === 'links') { localStorage.setItem(LINKS, localStorage.getItem(LINKS) === '1' ? '0' : '1'); applyFlag(LINKS, 'dck-ulinks'); }
		else if (a === 'reset') { step = 0; localStorage.removeItem(STEP); localStorage.removeItem(CONTRAST); localStorage.removeItem(LINKS); applyStep(); applyFlag(CONTRAST, 'dck-contrast'); applyFlag(LINKS, 'dck-ulinks'); }
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { openPanel(false); }
	});
})();
