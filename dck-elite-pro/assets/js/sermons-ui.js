/* Sermon library: grid / list view toggle (remembered). */
(function () {
  'use strict';
  var grid = document.getElementById('dck-sgrid');
  var btns = document.querySelectorAll('.dck-view__btn');
  if (!grid || !btns.length) { return; }
  function set(v) {
    grid.classList.toggle('is-list', v === 'list');
    btns.forEach(function (b) { var on = b.getAttribute('data-view') === v; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
    try { localStorage.setItem('dck_sview', v); } catch (e) {}
  }
  btns.forEach(function (b) { b.addEventListener('click', function () { set(b.getAttribute('data-view')); }); });
  var saved = 'grid';
  try { saved = localStorage.getItem('dck_sview') || 'grid'; } catch (e) {}
  set(saved);
})();
