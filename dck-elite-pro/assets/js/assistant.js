/* DCK AI Church Assistant widget. Multilingual (Swahili-first). No dependencies. */
(function () {
  "use strict";
  if (typeof DCK_AI === "undefined") { return; }
  var PHONE = DCK_AI.phone || "+254 110 304521";

  function lang() { var l = window.DCK_LANG; return l ? l : "auto"; }
  function isSw() { return lang() === "sw"; }

  var T = {
    ask: { en: "Ask", sw: "Uliza" },
    close: { en: "Close", sw: "Funga" },
    send: { en: "Send", sw: "Tuma" },
    mic: { en: "Speak", sw: "Sema" },
    stop: { en: "Stop", sw: "Simama" },
    transcribing: { en: "Transcribing...", sw: "Inanukuu..." },
    audio: { en: "Audio", sw: "Sauti" },
    errVoice: { en: "Sorry, I could not hear that. Please try again or type your message.", sw: "Samahani, sikusikia vizuri. Tafadhali jaribu tena au andika ujumbe." },
    ph: { en: "Type your message...", sw: "Andika ujumbe wako..." },
    prayerIntro: { en: "I would be honoured to pass your request to our prayer team. Please share it below. You may keep it confidential.", sw: "Nitafurahi kupeleka ombi lako kwa timu yetu ya maombi. Tafadhali andika hapa chini. Unaweza kuliweka siri." },
    prName: { en: "Your name (optional)", sw: "Jina lako (hiari)" },
    prEmail: { en: "Your email (optional)", sw: "Barua pepe yako (hiari)" },
    prReq: { en: "Your prayer request", sw: "Ombi lako la maombi" },
    prConf: { en: "Keep this confidential", sw: "Weka hili kuwa siri" },
    prSend: { en: "Send to prayer team", sw: "Tuma kwa timu ya maombi" },
    sending: { en: "Sending...", sw: "Inatuma..." },
    thanks: { en: "Thank you, your request has been received.", sw: "Asante, ombi lako limepokelewa." },
    errConn: { en: "I could not connect right now. Please call us on " + PHONE + ".", sw: "Sikuweza kuunganika sasa. Tafadhali tupigie " + PHONE + "." },
    errSend: { en: "I could not send that. Please call " + PHONE + ".", sw: "Sikuweza kutuma. Tafadhali piga " + PHONE + "." }
  };
  var CHIPS = {
    en: ["Plan a visit", "Service times", "Prayer request", "I want to know Christ"],
    sw: ["Panga ziara", "Nyakati za ibada", "Ombi la maombi", "Nataka kumjua Kristo"]
  };
  function t(k) { var o = T[k]; if (o === undefined) { return ""; } return (isSw() && o.sw) ? o.sw : o.en; }
  function welcome() { return (isSw() && DCK_AI.welcome_sw) ? DCK_AI.welcome_sw : DCK_AI.welcome; }

  var history = [];
  var opened = false;
  var mode = "chat";
  var speakerOn = false;
  var logEl = null;
  var inputEl = null;

  var btn = document.createElement("button");
  btn.type = "button";
  btn.className = "dck-ai-btn";
  btn.setAttribute("aria-label", "Open church assistant");

  var panel = document.createElement("div");
  panel.className = "dck-ai-panel";
  panel.setAttribute("hidden", "");
  panel.setAttribute("role", "dialog");

  document.body.appendChild(btn);
  document.body.appendChild(panel);

  function esc(s) {
    var d = document.createElement("div");
    d.textContent = (s === null || s === undefined) ? "" : String(s);
    return d.innerHTML;
  }
  function chipHtml(label) {
    return "<button type='button' class='dck-ai-chip' data-chip='" + esc(label) + "'>" + esc(label) + "</button>";
  }
  function renderBtn() {
    btn.innerHTML = "<span class='dck-ai-dot'></span><span>" + esc(t("ask")) + " " + esc(DCK_AI.name) + "</span>";
  }
  function hasVoice() { return typeof window.DCK_VOICE === "object" && window.DCK_VOICE; }
  function renderPanel() {
    var chips = isSw() ? CHIPS.sw : CHIPS.en;
    var chipStr = "";
    for (var i = 0; i < chips.length; i++) { chipStr += chipHtml(chips[i]); }
    var spk = (hasVoice() && window.DCK_VOICE.canSpeak) ? "<button type='button' id='dck-ai-spk' class='dck-ai-spk" + (speakerOn ? " is-active" : "") + "' aria-label='" + esc(t("audio")) + "'>" + esc(t("audio")) + "</button>" : "";
    var mic = (hasVoice() && window.DCK_VOICE.canListen) ? "<button type='button' id='dck-ai-mic' class='dck-ai-mic'>" + esc(t("mic")) + "</button>" : "";
    panel.innerHTML =
      "<div class='dck-ai-head'><strong>" + esc(DCK_AI.name) + "</strong><span class='dck-ai-head-btns'>" + spk + "<button type='button' class='dck-ai-close' aria-label='" + esc(t("close")) + "'>&times;</button></span></div>" +
      "<div class='dck-ai-log' id='dck-ai-log'></div>" +
      "<div class='dck-ai-chips'>" + chipStr + "</div>" +
      "<div class='dck-ai-bar'>" + mic + "<input type='text' id='dck-ai-input' placeholder='" + esc(t("ph")) + "' autocomplete='off'><button type='button' id='dck-ai-send'>" + esc(t("send")) + "</button></div>";
    logEl = panel.querySelector("#dck-ai-log");
    inputEl = panel.querySelector("#dck-ai-input");
    inputEl.addEventListener("keydown", function (e) { if (e.key === "Enter") { e.preventDefault(); send(inputEl.value); } });
  }

  function addMsg(role, text) {
    var m = document.createElement("div");
    m.className = "dck-ai-msg dck-ai-" + role;
    m.textContent = text;
    logEl.appendChild(m);
    logEl.scrollTop = logEl.scrollHeight;
    return m;
  }
  function openPanel() {
    if (opened) { return; }
    opened = true;
    panel.removeAttribute("hidden");
    btn.setAttribute("hidden", "");
    if (logEl.childNodes.length === 0) { addMsg("assistant", welcome()); }
    setTimeout(function () { if (inputEl) { inputEl.focus(); } }, 50);
  }
  function closePanel() {
    opened = false;
    panel.setAttribute("hidden", "");
    btn.removeAttribute("hidden");
  }

  function send(text) {
    text = (text || "").trim();
    if (text.length === 0) { return; }
    addMsg("user", text);
    history.push({ role: "user", content: text });
    inputEl.value = "";
    var typing = addMsg("assistant", "...");
    fetch(DCK_AI.rest + "assistant", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": DCK_AI.nonce },
      body: JSON.stringify({ message: text, messages: history, lang: lang() })
    }).then(function (r) { return r.json(); }).then(function (d) {
      var reply = (d && d.reply) ? d.reply : "...";
      typing.textContent = reply;
      history.push({ role: "assistant", content: reply });
      if (speakerOn && hasVoice()) { window.DCK_VOICE.speak(reply, lang()); }
    }).catch(function () { typing.textContent = t("errConn"); });
  }

  function showPrayerForm() {
    if (mode === "prayer") { return; }
    mode = "prayer";
    addMsg("assistant", t("prayerIntro"));
    var form = document.createElement("div");
    form.className = "dck-ai-form";
    form.innerHTML =
      "<input type='text' id='dck-pr-name' placeholder='" + esc(t("prName")) + "' autocomplete='off'>" +
      "<input type='email' id='dck-pr-email' placeholder='" + esc(t("prEmail")) + "' autocomplete='off'>" +
      "<textarea id='dck-pr-req' rows='3' placeholder='" + esc(t("prReq")) + "'></textarea>" +
      "<label style='font-size:.85rem;color:#6B6862'><input type='checkbox' id='dck-pr-priv'> " + esc(t("prConf")) + "</label>" +
      "<button type='button' class='dck-btn dck-btn--primary' id='dck-pr-send'>" + esc(t("prSend")) + "</button>";
    logEl.appendChild(form);
    logEl.scrollTop = logEl.scrollHeight;
    form.querySelector("#dck-pr-send").addEventListener("click", function () {
      var req = form.querySelector("#dck-pr-req").value.trim();
      if (req.length === 0) { form.querySelector("#dck-pr-req").focus(); return; }
      var payload = {
        name: form.querySelector("#dck-pr-name").value,
        email: form.querySelector("#dck-pr-email").value,
        request: req,
        private: form.querySelector("#dck-pr-priv").checked,
        lang: lang()
      };
      var sending = addMsg("assistant", t("sending"));
      fetch(DCK_AI.rest + "prayer", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-WP-Nonce": DCK_AI.nonce },
        body: JSON.stringify(payload)
      }).then(function (r) { return r.json(); }).then(function (d) {
        sending.textContent = (d && d.message) ? d.message : t("thanks");
        mode = "chat";
        if (form.parentNode) { form.parentNode.removeChild(form); }
      }).catch(function () { sending.textContent = t("errSend"); });
    });
  }

  function micClick(tg) {
    if (hasVoice() === false) { return; }
    if (window.DCK_VOICE.isListening()) { window.DCK_VOICE.stopListen(); return; }
    window.DCK_VOICE.startListen(
      function (state) {
        if (state === "listening") { tg.textContent = t("stop"); tg.classList.add("is-active"); }
        else if (state === "transcribing") { tg.textContent = t("transcribing"); }
        else { tg.textContent = t("mic"); tg.classList.remove("is-active"); }
      },
      function (text) {
        tg.textContent = t("mic"); tg.classList.remove("is-active");
        if (inputEl) { inputEl.value = text; }
        send(text);
      },
      function () {
        tg.textContent = t("mic"); tg.classList.remove("is-active");
        if (logEl) { addMsg("assistant", t("errVoice")); }
      }
    );
  }
  btn.addEventListener("click", openPanel);
  panel.addEventListener("click", function (e) {
    var tg = e.target;
    if (tg && tg.classList && tg.classList.contains("dck-ai-close")) { closePanel(); return; }
    if (tg && tg.id === "dck-ai-send") { if (inputEl) { send(inputEl.value); } return; }
    if (tg && tg.id === "dck-ai-spk") { speakerOn = (speakerOn === false); tg.classList.toggle("is-active"); return; }
    if (tg && tg.id === "dck-ai-mic") { micClick(tg); return; }
    var c = (tg && tg.getAttribute) ? tg.getAttribute("data-chip") : null;
    if (c === null) { return; }
    if (c === "Prayer request" || c === "Ombi la maombi") { showPrayerForm(); return; }
    send(c);
  });
  window.addEventListener("dck-lang", function () {
    renderBtn();
    if (opened === false) { renderPanel(); }
  });

  renderBtn();
  renderPanel();
})();
