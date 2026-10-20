/* DCK Elite - sermon transcription runner (admin). No dependencies. */
(function () {
  "use strict";
  if (typeof DCK_TR === "undefined") { return; }
  var go = document.getElementById("dck-tr-go");
  var status = document.getElementById("dck-tr-status");
  var textEl = document.getElementById("dck-tr-text");
  if (go === null) { return; }

  function setStatus(s) { if (status) { status.textContent = s; } }
  function done(d) {
    setStatus("Done. " + (d && d.count ? (d.count + " segments.") : ""));
    if (textEl && d && d.text) { textEl.value = d.text; }
    go.disabled = false;
  }

  function poll(tries) {
    if (tries <= 0) { setStatus("Still processing. Refresh this page in a moment to view the transcript."); go.disabled = false; return; }
    fetch(DCK_TR.rest + "transcribe-status?post_id=" + encodeURIComponent(DCK_TR.postId), { headers: { "X-WP-Nonce": DCK_TR.nonce } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.status === "done") { done(d); return; }
        if (d && d.status === "error") { setStatus(d.msg ? d.msg : "Error."); go.disabled = false; return; }
        setTimeout(function () { poll(tries - 1); }, 5000);
      })
      .catch(function () { setTimeout(function () { poll(tries - 1); }, 5000); });
  }

  go.addEventListener("click", function () {
    if (String(DCK_TR.postId) === "0") { window.alert("Please save the sermon as a draft first, then transcribe."); return; }
    go.disabled = true;
    setStatus("Transcribing... this can take a minute for long sermons.");
    fetch(DCK_TR.rest + "transcribe", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": DCK_TR.nonce },
      body: JSON.stringify({ post_id: DCK_TR.postId })
    }).then(function (r) { return r.json(); }).then(function (d) {
      if (d && d.status === "done") { done(d); return; }
      if (d && d.status === "error") { setStatus(d.msg ? d.msg : "Error."); go.disabled = false; return; }
      poll(24);
    }).catch(function () { poll(24); });
  });
})();
