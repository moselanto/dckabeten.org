/* DCK Elite - Swahili-first language toggle + chrome translation. */
(function () {
  "use strict";

  var DEF = "auto";
  if (typeof DCK_I18N === "object" && DCK_I18N && DCK_I18N.def) { DEF = DCK_I18N.def; }

  var stored = null;
  try { stored = window.localStorage.getItem("dck_lang"); } catch (e) {}
  window.DCK_LANG = stored ? stored : DEF;

  var NAV = {
    "Home": "Nyumbani",
    "About": "Kuhusu",
    "About Us": "Kuhusu Sisi",
    "Ministries": "Huduma",
    "Sermons": "Mahubiri",
    "Sermon Library": "Maktaba ya Mahubiri",
    "Bible": "Biblia",
    "Events": "Matukio",
    "Give": "Toa",
    "Give Online": "Toa Mtandaoni",
    "Contact": "Wasiliana",
    "Contact Us": "Wasiliana Nasi",
    "News & Blog": "Habari na Blogu",
    "Watch Live": "Tazama Moja kwa Moja",
    "Live": "Moja kwa Moja",
    "Plan a Visit": "Panga Ziara",
    "Plan Your Visit": "Panga Ziara Yako",
    "Watch / Listen": "Tazama / Sikiliza",
    "Back to Sermons": "Rudi kwa Mahubiri",
    "Download Notes": "Pakua Maandishi",
    "Share on WhatsApp": "Shiriki WhatsApp",
    "Share on Facebook": "Shiriki Facebook",
    "Read More": "Soma Zaidi",
    "Learn More": "Jifunze Zaidi",
    "Filter": "Chuja",
    "Request Prayer": "Omba Maombi",
    "Prayer Request": "Ombi la Maombi"
  };

  var STR = {
    "bible_title": { en: "Bible Study Assistant", sw: "Msaidizi wa Kujifunza Biblia" },
    "bible_lead": { en: "Ask about any verse or topic and receive a clear, Scripture-grounded explanation with cross references, application, and prayer.", sw: "Uliza kuhusu andiko au mada yoyote upate maelezo wazi yenye msingi wa Maandiko, pamoja na marejeo, matumizi, na sala." },
    "ask": { en: "Ask", sw: "Uliza" },
    "depth": { en: "Depth:", sw: "Kina:" },
    "lvl_balanced": { en: "Balanced", sw: "Wastani" },
    "lvl_simple": { en: "Simple", sw: "Rahisi" },
    "lvl_youth": { en: "Youth", sw: "Vijana" },
    "lvl_children": { en: "Children", sw: "Watoto" },
    "lvl_theological": { en: "Theological", sw: "Kitheolojia" },
    "lvl_original": { en: "Original Language", sw: "Lugha ya Asili" },
    "bible_ph": { en: "Ask about a verse (Romans 8:28) or a topic (anxiety, forgiveness, hope)...", sw: "Uliza kuhusu andiko (Warumi 8:28) au mada (wasiwasi, msamaha, tumaini)..." },
    "ss_label": { en: "Ask for a message. Try: sermons about forgiveness, or messages on faith and hope", sw: "Tafuta ujumbe. Jaribu: mahubiri kuhusu msamaha, au ujumbe kuhusu imani na tumaini" },
    "ss_ph": { en: "Describe what you need...", sw: "Eleza unachohitaji..." },
    "ss_btn": { en: "AI Search", sw: "Tafuta kwa AI" },
    "summary_label": { en: "Sermon Summary", sw: "Muhtasari wa Hubiri" },
    "guide_label": { en: "Small-Group Study Guide", sw: "Mwongozo wa Kikundi Kidogo" },
    "transcript_label": { en: "Full Transcript", sw: "Nakala Kamili" },
    "tr_search": { en: "Search the sermon...", sw: "Tafuta ndani ya hubiri..." }
  };

  function pick(o, l) { return (l === "sw" && o.sw) ? o.sw : o.en; }

  function applyChrome(l) {
    var links = document.querySelectorAll("header a, nav a, footer a, .dck-footer a");
    for (var i = 0; i < links.length; i++) {
      var a = links[i];
      if (a.childElementCount > 0) { continue; }
      var en = a.getAttribute("data-en");
      if (en === null) { en = a.textContent.trim(); a.setAttribute("data-en", en); }
      if (l === "sw" && NAV[en]) { a.textContent = NAV[en]; } else { a.textContent = en; }
    }
    var els = document.querySelectorAll("[data-i18n]");
    for (var j = 0; j < els.length; j++) {
      var key = els[j].getAttribute("data-i18n");
      var o = STR[key];
      if (o === undefined) { continue; }
      var val = pick(o, l);
      if (val) { els[j].textContent = val; }
    }
    var phs = document.querySelectorAll("[data-i18n-ph]");
    for (var k = 0; k < phs.length; k++) {
      var pk = phs[k].getAttribute("data-i18n-ph");
      var po = STR[pk];
      if (po === undefined) { continue; }
      phs[k].setAttribute("placeholder", pick(po, l));
    }
  }

  function markActive() {
    var active = (window.DCK_LANG === "sw") ? "sw" : "en";
    var btns = document.querySelectorAll(".dck-lang-toggle button");
    for (var i = 0; i < btns.length; i++) {
      if (btns[i].getAttribute("data-lang") === active) { btns[i].classList.add("is-active"); }
      else { btns[i].classList.remove("is-active"); }
    }
  }

  function setLang(code) {
    window.DCK_LANG = code;
    try { window.localStorage.setItem("dck_lang", code); } catch (e) {}
    applyChrome(code);
    markActive();
    try { window.dispatchEvent(new CustomEvent("dck-lang", { detail: { lang: code } })); } catch (e) {}
  }

  function makeBtn(label, code) {
    var b = document.createElement("button");
    b.type = "button";
    b.textContent = label;
    b.setAttribute("data-lang", code);
    b.addEventListener("click", function () { setLang(code); });
    return b;
  }

  function init() {
    var wrap = document.createElement("div");
    wrap.className = "dck-lang-toggle";
    wrap.setAttribute("aria-label", "Language");
    wrap.appendChild(makeBtn("SW", "sw"));
    wrap.appendChild(makeBtn("EN", "en"));
    document.body.appendChild(wrap);
    applyChrome(window.DCK_LANG);
    markActive();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
