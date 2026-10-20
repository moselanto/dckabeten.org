/* DCK instant M-Pesa giving (Paystack STK push). Reads window.DCK_PAY. */
(function () {
	'use strict';
	var C = window.DCK_PAY || {};
	var form = document.querySelector('[data-dck-pay]');
	if (!form || !C.start) { return; }
	var card = form.closest('.dck-pay__card');
	var wait = card.querySelector('[data-pay-wait]'), done = card.querySelector('[data-pay-done]');
	var msg = form.querySelector('.dck-pay__msg'), btn = form.querySelector('.dck-pay__btn'), lbl = form.querySelector('[data-pay-label]');
	var otpForm = card.querySelector('[data-pay-otp]'), timerEl = card.querySelector('[data-pay-timer]'), retry = card.querySelector('[data-pay-retry]');
	var started = Date.now(), ref = '', poll = null, tick = null, deadline = 0, amountTxt = '';

	function post(url, data) {
		return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce || '' }, body: JSON.stringify(data) }).then(function (r) { return r.json(); });
	}
	function say(t, bad) { msg.textContent = t || ''; msg.classList.toggle('is-error', !!bad); }
	function busy(on) { btn.disabled = on; lbl.textContent = on ? 'Sending prompt...' : 'Send M-Pesa prompt'; }
	function show(el) { [form, wait, done].forEach(function (x) { x.hidden = x !== el; }); }
	function stop() { if (poll) { clearInterval(poll); poll = null; } if (tick) { clearInterval(tick); tick = null; } }

	form.addEventListener('click', function (e) {
		var b = e.target.closest && e.target.closest('[data-pay-amt]');
		if (!b) { return; }
		form.amount.value = b.getAttribute('data-pay-amt');
		var all = form.querySelectorAll('[data-pay-amt]');
		for (var i = 0; i < all.length; i++) { all[i].classList.toggle('is-active', all[i] === b); }
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var amt = parseInt(form.amount.value, 10);
		var ph = String(form.phone.value || '').replace(/\D/g, '');
		if (!amt || amt < 10) { say('Please enter an amount of at least KES 10.', true); form.amount.focus(); return; }
		if (ph.length < 9) { say('Please enter your M-Pesa phone number.', true); form.phone.focus(); return; }
		var purpose = (form.querySelector('input[name="purpose"]:checked') || {}).value || '';
		amountTxt = 'KES ' + amt.toLocaleString();
		busy(true); say('');
		post(C.start, { amount: amt, phone: form.phone.value, name: form.name.value, email: form.email.value, purpose: purpose, website: form.website.value, elapsed: Date.now() - started })
			.then(function (j) {
				busy(false);
				if (!j || !j.ok) { say((j && j.message) || 'Something went wrong. Please try again.', true); return; }
				ref = j.reference;
				card.querySelector('[data-pay-wait-title]').textContent = 'Check your phone';
				card.querySelector('[data-pay-wait-text]').textContent = j.message;
				otpForm.hidden = j.status !== 'send_otp';
				retry.hidden = true;
				show(wait);
				if (j.status === 'success') { paid(); return; }
				watch();
			})
			.catch(function () { busy(false); say('Network problem. Please check your connection and try again.', true); });
	});

	otpForm.addEventListener('submit', function (e) {
		e.preventDefault();
		post(C.otp, { reference: ref, otp: otpForm.otp.value, elapsed: 99999 }).then(function (j) {
			card.querySelector('[data-pay-wait-text]').textContent = (j && j.message) || '';
			if (j && j.ok) { otpForm.hidden = true; }
		});
	});

	function watch() {
		deadline = Date.now() + 180000;
		stop();
		tick = setInterval(function () {
			var s = Math.max(0, Math.round((deadline - Date.now()) / 1000));
			timerEl.textContent = Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
			if (s <= 0) { fail('The M-Pesa prompt expired. You can try again.'); }
		}, 1000);
		poll = setInterval(function () {
			post(C.status, { reference: ref }).then(function (j) {
				if (!j) { return; }
				if (j.state === 'paid') { paid(); } else if (j.state === 'failed') { fail('The payment was not completed (cancelled, wrong PIN or insufficient balance).'); }
			}).catch(function () {});
		}, 4000);
	}
	function paid() {
		stop();
		card.querySelector('[data-pay-ref]').textContent = ref;
		card.querySelector('[data-pay-done-text]').textContent = amountTxt + ' has been received. God bless you!' + (form.email.value ? ' A receipt is on its way to your email.' : '');
		show(done);
		try { if (typeof window.gtag === 'function') { window.gtag('event', 'donation', { value: parseInt(form.amount.value, 10), currency: 'KES' }); } } catch (e) {}
	}
	function fail(t) {
		stop();
		card.querySelector('[data-pay-wait-title]').textContent = 'Payment not completed';
		card.querySelector('[data-pay-wait-text]').textContent = t;
		otpForm.hidden = true;
		retry.hidden = false;
	}
	retry.addEventListener('click', function () { show(form); started = Date.now() - 5000; });
	card.querySelector('[data-pay-again]').addEventListener('click', function () { form.amount.value = ''; show(form); started = Date.now() - 5000; });
})();
