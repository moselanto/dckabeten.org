/* DCK Elite - AI Voice client. Exposes window.DCK_VOICE. No dependencies. */
(function () {
  "use strict";
  var CFG = (typeof DCK_VOICE_CFG === "object" && DCK_VOICE_CFG) ? DCK_VOICE_CFG : {};
  var serverVoice = (CFG.server === 1 || CFG.server === "1" || CFG.server === true);
  var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  var canRecord = (navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.MediaRecorder) ? true : false;
  var canBrowserTTS = (typeof window.speechSynthesis !== "undefined");

  var recorder = null;
  var srInstance = null;
  var chunks = [];
  var stream = null;
  var listening = false;

  function langCode() {
    var l = window.DCK_LANG;
    if (l === "sw") { return "sw"; }
    if (l === "en") { return "en"; }
    return "";
  }
  function stopStream() {
    if (stream) {
      var tr = stream.getTracks();
      for (var i = 0; i < tr.length; i++) { tr[i].stop(); }
      stream = null;
    }
  }

  function startListen(onState, onResult, onError) {
    if (listening) { return; }
    if (serverVoice && canRecord) {
      navigator.mediaDevices.getUserMedia({ audio: true }).then(function (s) {
        stream = s;
        chunks = [];
        recorder = new MediaRecorder(s);
        recorder.ondataavailable = function (ev) { if (ev.data && ev.data.size > 0) { chunks.push(ev.data); } };
        recorder.onstop = function () {
          stopStream();
          onState("transcribing");
          var mime = recorder.mimeType ? recorder.mimeType : "audio/webm";
          var blob = new Blob(chunks, { type: mime });
          var fd = new FormData();
          fd.append("audio", blob, "audio.webm");
          var lc = langCode();
          if (lc) { fd.append("lang", lc); }
          fetch(CFG.rest + "stt", { method: "POST", headers: { "X-WP-Nonce": CFG.nonce }, body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
              onState("idle");
              var txt = (d && d.text) ? String(d.text).trim() : "";
              if (txt.length > 0) { onResult(txt); } else { onError(); }
            })
            .catch(function () { onState("idle"); onError(); });
        };
        recorder.start();
        listening = true;
        onState("listening");
      }).catch(function () { onError(); });
      return;
    }
    if (SR) {
      srInstance = new SR();
      var lc2 = langCode();
      srInstance.lang = (lc2 === "sw") ? "sw-KE" : "en-KE";
      srInstance.interimResults = false;
      srInstance.maxAlternatives = 1;
      srInstance.onresult = function (ev) {
        var txt = ev.results[0][0].transcript;
        if (txt && String(txt).length > 0) { onResult(String(txt)); }
      };
      srInstance.onerror = function () { onError(); };
      srInstance.onend = function () { listening = false; onState("idle"); };
      srInstance.start();
      listening = true;
      onState("listening");
      return;
    }
    onError();
  }

  function stopListen() {
    if (recorder && recorder.stop && listening) { try { recorder.stop(); } catch (e) {} }
    if (srInstance && srInstance.stop) { try { srInstance.stop(); } catch (e) {} }
    listening = false;
  }

  function browserSpeak(text, lang) {
    if (canBrowserTTS === false) { return; }
    try {
      var u = new SpeechSynthesisUtterance(String(text));
      u.lang = (lang === "sw" || langCode() === "sw") ? "sw-KE" : "en-KE";
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(u);
    } catch (e) {}
  }
  function speak(text, lang) {
    if (text === null || text === undefined || String(text).length === 0) { return; }
    if (serverVoice) {
      fetch(CFG.rest + "tts", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-WP-Nonce": CFG.nonce },
        body: JSON.stringify({ text: String(text), lang: lang ? lang : langCode() })
      }).then(function (r) { return r.json(); }).then(function (d) {
        if (d && d.audio) { var a = new Audio(d.audio); a.play(); } else { browserSpeak(text, lang); }
      }).catch(function () { browserSpeak(text, lang); });
      return;
    }
    browserSpeak(text, lang);
  }

  window.DCK_VOICE = {
    canListen: (serverVoice && canRecord) || (SR ? true : false),
    canSpeak: serverVoice || canBrowserTTS,
    isListening: function () { return listening; },
    startListen: startListen,
    stopListen: stopListen,
    speak: speak
  };
})();
