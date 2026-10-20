/* DCK church forms: any <form class="dck-ctform" data-endpoint="..."> posts JSON to the REST API. */
(function () {
	'use strict';
	var C = window.DCK_FORMS || {};
	var forms = document.querySelectorAll('form.dck-ctform');
	for (var i = 0; i < forms.length; i++) { wire(forms[i]); }

	function wire(form) {
		var started = Date.now();
		var status = form.querySelector('.dck-ctform__status');
		var btn = form.querySelector('button[type="submit"]');
		var label = btn ? btn.textContent : '';
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var req = form.querySelectorAll('[required]');
			for (var r = 0; r < req.length; r++) {
				if (!String(req[r].value || '').trim()) { say(C.errReq || 'Please fill in the required fields.', true); req[r].focus(); return; }
			}
			var data = { elapsed: Date.now() - started };
			var els = form.elements;
			for (var k = 0; k < els.length; k++) {
				var el = els[k];
				if (!el.name) { continue; }
				if (el.type === 'checkbox') {
					if (el.name.slice(-2) === '[]') {
						var key = el.name.slice(0, -2);
						data[key] = data[key] || [];
						if (el.checked) { data[key].push(el.value); }
					} else { data[el.name] = el.checked ? el.value : ''; }
				} else if (el.type === 'radio') {
					if (el.checked) { data[el.name] = el.value; }
				} else { data[el.name] = el.value; }
			}
			if (btn) { btn.disabled = true; btn.textContent = C.sending || 'Sending...'; }
			fetch(form.getAttribute('data-endpoint'), {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce || '' },
				body: JSON.stringify(data)
			}).then(function (r) { return r.json(); }).then(function (j) {
				if (j && j.ok) {
					var done = document.createElement('div');
					done.className = 'dck-ctform__done';
					done.setAttribute('role', 'status');
					var h = document.createElement('p');
					h.className = 'dck-ctform__done-title';
					h.textContent = j.message || 'Thank you.';
					done.appendChild(h);
					if (j.whatsapp) {
						var a = document.createElement('a');
						a.className = 'dck-btn dck-btn--outline';
						a.href = j.whatsapp; a.target = '_blank'; a.rel = 'noopener';
						a.textContent = j.whatsappLabel || 'Message us on WhatsApp';
						done.appendChild(a);
					}
					form.replaceWith(done);
					done.scrollIntoView({ behavior: 'smooth', block: 'center' });
				} else {
					say((j && j.message) || C.errNet, true);
					reset();
				}
			}).catch(function () { say(C.errNet, true); reset(); });
		});
		function reset() { if (btn) { btn.disabled = false; btn.textContent = label; } }
		function say(t, bad) { if (status) { status.textContent = t; status.classList.toggle('is-error', !!bad); } }
	}
})();
