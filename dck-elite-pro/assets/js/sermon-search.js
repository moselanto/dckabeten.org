/* DCK AI Sermon Search. No dependencies. */
(function () {
  "use strict";
  if (typeof DCK_SS === "undefined") { return; }
  var input = document.getElementById("dck-ss-input");
  var go = document.getElementById("dck-ss-go");
  var out = document.getElementById("dck-ss-results");
  if (input === null || go === null || out === null) { return; }

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

  function search() {
    var q = input.value.trim();
    if (q.length === 0) { input.focus(); return; }
    out.innerHTML = "";
    var loading = document.createElement("p");
    loading.className = "dck-bible-loading";
    loading.textContent = "Searching messages...";
    out.appendChild(loading);
    fetch(DCK_SS.rest + "sermon-search", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": DCK_SS.nonce },
      body: JSON.stringify({ query: q, lang: (window.DCK_LANG ? window.DCK_LANG : "auto") })
    }).then(function (r) { return r.json(); }).then(function (d) {
      out.innerHTML = "";
      if (d && d.note) {
        var note = document.createElement("div");
        note.className = "dck-ss-note";
        note.textContent = d.note;
        out.appendChild(note);
      }
      var list = (d && d.results) ? d.results : [];
      if (list.length === 0) {
        var none = document.createElement("p");
        none.textContent = "No messages matched yet. Try another topic, or browse the library below.";
        out.appendChild(none);
        return;
      }
      var wrap = document.createElement("div");
      wrap.className = "dck-sermon-hits";
      for (var i = 0; i < list.length; i++) { wrap.appendChild(hitCard(list[i])); }
      out.appendChild(wrap);
    }).catch(function () {
      out.innerHTML = "";
      var e = document.createElement("p");
      e.textContent = "Search is unavailable right now. Please try again shortly.";
      out.appendChild(e);
    });
  }

  go.addEventListener("click", search);
  input.addEventListener("keydown", function (e) {
    if (e.key === "Enter") { e.preventDefault(); search(); }
  });
})();
