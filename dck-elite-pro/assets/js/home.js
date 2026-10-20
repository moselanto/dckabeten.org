(function () {
	'use strict';
	var hero = document.querySelector('.dck-hero2');
	if (!hero) { return; }
	var slides = Array.prototype.slice.call(hero.querySelectorAll('.dck-hero2__slide'));
	var dots = Array.prototype.slice.call(hero.querySelectorAll('.dck-dot'));
	var prev = hero.querySelector('.dck-hero2__prev');
	var next = hero.querySelector('.dck-hero2__next');
	if (slides.length < 2) { return; }
	var index = 0;
	var timer = null;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function show(n) {
		index = (n + slides.length) % slides.length;
		slides.forEach(function (s, i) {
			var on = i === index;
			s.classList.toggle('is-active', on);
			s.setAttribute('aria-hidden', on ? 'false' : 'true');
		});
		dots.forEach(function (d, i) {
			d.classList.toggle('is-active', i === index);
		});
	}
	function nextSlide() { show(index + 1); }
	function prevSlide() { show(index - 1); }
	function start() {
		if (reduce) { return; }
		stop();
		timer = window.setInterval(nextSlide, 6000);
	}
	function stop() {
		if (timer) { window.clearInterval(timer); timer = null; }
	}

	if (next) { next.addEventListener('click', function () { nextSlide(); start(); }); }
	if (prev) { prev.addEventListener('click', function () { prevSlide(); start(); }); }
	dots.forEach(function (d, i) {
		d.addEventListener('click', function () { show(i); start(); });
	});
	hero.addEventListener('mouseenter', stop);
	hero.addEventListener('mouseleave', start);
	hero.addEventListener('focusin', stop);
	hero.addEventListener('focusout', start);

	show(0);
	start();
})();
