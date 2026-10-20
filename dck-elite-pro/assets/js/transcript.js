/* DCK Elite - on-page sermon transcript: search + jump. No dependencies. */
(function () {
  "use strict";
  var input = document.getElementById("dck-tr-input");
  var list = document.querySelector(".dck-tr-list");
  var audio = document.getElementById("dck-sermon-audio");
  if (list === null) { return; }
  var segs = list.querySelectorAll(".dck-seg");

  function norm(s) { return String(s).toLowerCase(); }

  if (input) {
    input.addEventListener("input", function () {
      var q = norm(input.value.trim());
      for (var i = 0; i < segs.length; i++) {
        var txt = norm(segs[i].textContent);
        if (q.length === 0 || txt.indexOf(q) >= 0) { segs[i].style.display = ""; }
        else { segs[i].style.display = "none"; }
      }
    });
  }

  for (var j = 0; j < segs.length; j++) {
    segs[j].addEventListener("click", function () {
      var st = parseFloat(this.getAttribute("data-start"));
      if (audio && isFinite(st)) {
        try {
          audio.currentTime = st;
          audio.play();
          audio.scrollIntoView({ behavior: "smooth", block: "center" });
        } catch (e) {}
      }
    });
  }
})();
