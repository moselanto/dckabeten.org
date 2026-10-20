(function () {
	var go = document.getElementById('dck-bf-go');
	var stop = document.getElementById('dck-bf-stop');
	var countEl = document.getElementById('dck-bf-count');
	var log = document.getElementById('dck-bf-log');
	if (!go || typeof DCK_BF === 'undefined') { return; }
	var running = false;

	function step() {
		if (!running) { return; }
		fetch(DCK_BF.rest, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': DCK_BF.nonce },
			body: '{}'
		}).then(function (r) { return r.json(); }).then(function (d) {
			if (d && typeof d.remaining !== 'undefined') {
				countEl.textContent = d.remaining;
				if (d.title) { log.textContent = 'Done: ' + d.title + ' (' + d.remaining + ' left)'; }
			}
			if (d && d.msg) { log.textContent = d.msg; }
			if (d && d.done) { finish('Finished. All sermons have a summary and study guide.'); return; }
			setTimeout(step, 900);
		}).catch(function () { finish('Stopped after a network error. You can run it again to continue.'); });
	}

	function finish(msg) {
		running = false;
		go.style.display = '';
		go.disabled = false;
		stop.style.display = 'none';
		if (msg) { log.textContent = msg; }
	}

	go.addEventListener('click', function () {
		running = true;
		go.style.display = 'none';
		stop.style.display = '';
		log.textContent = 'Working...';
		step();
	});
	stop.addEventListener('click', function () { finish('Stopped. Progress so far is saved.'); });
})();
