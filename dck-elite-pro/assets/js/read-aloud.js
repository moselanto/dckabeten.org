/* DCK Elite - Reading assistance (browser text-to-speech). No dependencies, no API. */
(function () {
	"use strict";
	var synth = window.speechSynthesis;
	var widgets = document.querySelectorAll(".dck-read");
	if (!synth || !("SpeechSynthesisUtterance" in window)) {
		for (var w = 0; w < widgets.length; w++) { widgets[w].style.display = "none"; }
		return;
	}
	var active = null;

	function collectText(el) {
		if (!el) { return ""; }
		var clone = el.cloneNode(true);
		var strip = clone.querySelectorAll("script,style,button,audio,video,iframe,.dck-read");
		for (var i = 0; i < strip.length; i++) {
			if (strip[i].parentNode) { strip[i].parentNode.removeChild(strip[i]); }
		}
		var t = clone.textContent || "";
		return t.replace(/\s+/g, " ").trim();
	}

	function setLabel(btn, txt) {
		var l = btn.querySelector(".dck-read__label");
		if (l) { l.textContent = txt; }
	}

	function stopAll(exceptBtn) {
		synth.cancel();
		for (var i = 0; i < widgets.length; i++) {
			var b = widgets[i].querySelector(".dck-read__btn");
			if (b && b !== exceptBtn) { setLabel(b, b.getAttribute("data-label-idle") || "Listen"); b.classList.remove("is-playing"); }
			var s = widgets[i].querySelector(".dck-read__stop");
			if (s && b !== exceptBtn) { s.hidden = true; }
		}
	}

	for (var k = 0; k < widgets.length; k++) {
		(function (widget) {
			var btn = widget.querySelector(".dck-read__btn");
			var stop = widget.querySelector(".dck-read__stop");
			if (!btn) { return; }
			var idle = btn.getAttribute("data-label-idle") || "Listen";
			setLabel(btn, idle);

			btn.addEventListener("click", function () {
				if (synth.speaking && !synth.paused && active === btn) {
					synth.pause();
					setLabel(btn, "Resume");
					return;
				}
				if (synth.paused && active === btn) {
					synth.resume();
					setLabel(btn, "Pause");
					return;
				}
				var target = document.querySelector(btn.getAttribute("data-target"));
				var text = collectText(target);
				if (text === "") {
					setLabel(btn, "Nothing to read yet");
					setTimeout(function () { setLabel(btn, idle); }, 1800);
					return;
				}
				stopAll(btn);
				var chunks = text.match(/[^.!?]+[.!?]*\s*/g) || [text];
				var idx = 0;
				function speakNext() {
					if (idx >= chunks.length) {
						setLabel(btn, idle);
						btn.classList.remove("is-playing");
						if (stop) { stop.hidden = true; }
						active = null;
						return;
					}
					var u = new SpeechSynthesisUtterance(chunks[idx]);
					u.rate = 1; u.pitch = 1;
					u.lang = document.documentElement.getAttribute("lang") || "en-US";
					u.onend = function () { idx++; speakNext(); };
					synth.speak(u);
				}
				active = btn;
				btn.classList.add("is-playing");
				setLabel(btn, "Pause");
				if (stop) { stop.hidden = false; }
				speakNext();
			});

			if (stop) {
				stop.addEventListener("click", function () {
					stopAll(null);
					active = null;
				});
			}
		})(widgets[k]);
	}

	window.addEventListener("beforeunload", function () { synth.cancel(); });
})();
