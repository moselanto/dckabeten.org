/* DCK AI Content Studio (admin). No dependencies. */
(function () {
  "use strict";
  if (typeof DCK_STUDIO === "undefined") { return; }
  var buttons = document.querySelectorAll(".dck-gen");
  var copybtns = document.querySelectorAll(".dck-copy");

  function setBusy(btn, busy) {
    var sp = btn.parentNode ? btn.parentNode.querySelector(".dck-sp") : null;
    if (sp) { sp.style.visibility = busy ? "visible" : "hidden"; }
    btn.disabled = busy;
  }

  function gen(btn) {
    var kind = btn.getAttribute("data-kind");
    var target = document.getElementById(btn.getAttribute("data-target"));
    if (target === null) { return; }
    if (String(DCK_STUDIO.postId) === "0") {
      window.alert("Please save a draft of the sermon first, then generate.");
      return;
    }
    setBusy(btn, true);
    fetch(DCK_STUDIO.rest + "generate", {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-WP-Nonce": DCK_STUDIO.nonce },
      body: JSON.stringify({ post_id: DCK_STUDIO.postId, kind: kind, lang: (function(){ var s = document.getElementById("dck_gen_lang"); return s ? s.value : ""; })() })
    }).then(function (r) { return r.json(); }).then(function (d) {
      setBusy(btn, false);
      target.value = (d && d.text) ? d.text : "No content returned.";
    }).catch(function () {
      setBusy(btn, false);
      window.alert("Generation failed. Please try again.");
    });
  }

  function copy(btn) {
    var target = document.getElementById(btn.getAttribute("data-target"));
    if (target === null) { return; }
    target.focus();
    target.select();
    try {
      document.execCommand("copy");
      var old = btn.textContent;
      btn.textContent = "Copied";
      setTimeout(function () { btn.textContent = old; }, 1500);
    } catch (e) {}
  }

  for (var i = 0; i < buttons.length; i++) {
    buttons[i].addEventListener("click", function () { gen(this); });
  }
  for (var j = 0; j < copybtns.length; j++) {
    copybtns[j].addEventListener("click", function () { copy(this); });
  }
})();
