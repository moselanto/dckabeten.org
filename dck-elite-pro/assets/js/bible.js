/* DCK Bible Study Assistant. No dependencies. */
(function () {
  "use strict";
  if (typeof DCK_BIBLE === "undefined") { return; }
  var input = document.getElementById("dck-bible-input");
  var go = document.getElementById("dck-bible-go");
  var out = document.getElementById("dck-bible-out");
  var serm = document.getElementById("dck-bible-sermons");
  var levels = document.querySelectorAll(".dck-level");
  if (input === null || go === null || out === null) { return; }

  var NL = String.fromCharCode(10);
  var currentLevel = "Balanced";
  var lastQuery = "";

  function clean(s) { return String(s === null || s === undefined ? "" : s).split("**").join(""); }

  function renderReply(text) {
    out.innerHTML = "";
    var lines = clean(text).split(NL);
    var ul = null;
    for (var i = 0; i < lines.length; i++) {
      var ln = String(lines[i]).trim();
      if (ln.length === 0) { ul = null; continue; }
      if (ln.charAt(0) === "#") {
        ul = null;
        var t = ln;
        while (t.length > 0 && (t.charAt(0) === "#" || t.charAt(0) === " ")) { t = t.substring(1); }
        var h = document.createElement("h4");
        h.textContent = t;
        out.appendChild(h);
      } else if (ln.charAt(0) === "-" && ln.charAt(1) === " ") {
        if (ul === null) { ul = document.createElement("ul"); out.appendChild(ul); }
        var li = document.createElement("li");
        li.textContent = ln.substring(2).trim();
        ul.appendChild(li);
      } else {
        ul = null;
        var p = document.createElement("p");
        p.textContent = ln;
        out.appendChild(p);
      }
    }
  }

  function hitCard(s) {
    var card = document.createElement("div");
    card.className = "dck-hit";
    var h = document.createElement("h5");
    var a = document.createElement("a");
    a.href = s.url;
    a.textContent = s.title;
    h.appendChild(a);
    card.appendChild(h);
    var meta = [];
    if (s.date) { meta.push(s.date); }
    if (s.speaker) { meta.push(s.speaker); }
    if (s.scripture) { meta.push(s.scripture); }
    if (meta.length > 0) {
      var m = document.createElement("div");
      m.className = "dck-hit-meta";
      m.textContent = meta.join("  -  ");
      card.appendChild(m);
    }
    if (s.excerpt) {
      var pp = document.createElement("p");
      pp.textContent = s.excerpt;
      card.appendChild(pp);
    }
    return card;
  }

  function renderSermons(list) {
    serm.innerHTML = "";
    if (typeof list === "undefined" || list === null || list.length === 0) { return; }
    var head = document.createElement("h4");
    head.textContent = "Related messages from our church";
    serm.appendChild(head);
    var wrap = document.createElement("div");
    wrap.className = "dck-sermon-hits";
    for (var i = 0; i < list.length; i++) { wrap.appendChild(hitCard(list[i])); }
    serm.appendChild(wrap);
  }

  function ask() {
    var q = input.value.trim();
    if (q.length === 0) { input.focus(); return; }
    lastQuery = q;
    out.innerHTML = "";
    serm.innerHTML = "";
    var loading = document.createElement("p");
    loading.className = "dck-bible-loading";
    loading.textContent = "Searching the Scriptures...";
    out.appendChild(loading);
    fetch(DCK_BIBLE.rest + "bible", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": DCK_BIBLE.nonce },
      body: JSON.stringify({ query: q, level: currentLevel, lang: (window.DCK_LANG ? window.DCK_LANG : "auto") })
    }).then(function (r) { return r.json(); }).then(function (d) {
      renderReply(d && d.reply ? d.reply : "Please try again.");
      renderSermons(d && d.sermons ? d.sermons : []);
      var rb = document.querySelector(".dck-read[data-read-scope]");
      if (rb) { rb.hidden = false; }
    }).catch(function () {
      out.innerHTML = "";
      var e = document.createElement("p");
      e.textContent = "I could not respond right now. Please try again shortly.";
      out.appendChild(e);
    });
  }

  go.addEventListener("click", ask);
  input.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && e.shiftKey === false) { e.preventDefault(); ask(); }
  });
  for (var k = 0; k < levels.length; k++) {
    levels[k].addEventListener("click", function () {
      var lv = this.getAttribute("data-level");
      currentLevel = lv ? lv : "Balanced";
      for (var j = 0; j < levels.length; j++) { levels[j].classList.remove("is-active"); }
      this.classList.add("is-active");
      if (lastQuery.length > 0) { ask(); }
    });
  }
})();
