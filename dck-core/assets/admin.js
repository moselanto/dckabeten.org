/* DCK Core - Church Settings screen interactions. */
(function ($) {
	'use strict';

	/* Image picker */
	$(document).on('click', '.dck-image__pick', function (e) {
		e.preventDefault();
		var box = $(this).closest('.dck-image');
		var frame = wp.media({ title: DCK_CORE.choose, button: { text: DCK_CORE.use }, multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			box.find('input[type=hidden]').val(a.url);
			box.find('.dck-image__preview').attr('src', (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url).prop('hidden', false);
			box.find('.dck-image__clear').prop('hidden', false);
		});
		frame.open();
	});
	$(document).on('click', '.dck-image__clear', function (e) {
		e.preventDefault();
		var box = $(this).closest('.dck-image');
		box.find('input[type=hidden]').val('');
		box.find('.dck-image__preview').attr('src', '').prop('hidden', true);
		$(this).prop('hidden', true);
	});

	/* Repeaters */
	function reindex(rep) {
		var name = rep.data('name');
		rep.find('.dck-row').not('.dck-row--tpl').each(function (i) {
			$(this).find('[name]').each(function () {
				var key = $(this).attr('name').replace(/.*\]\[([a-z]+)\]$/, '$1');
				$(this).attr('name', name + '[' + i + '][' + key + ']');
			});
		});
	}
	$(document).on('click', '.dck-add', function (e) {
		e.preventDefault();
		var rep = $(this).closest('.dck-repeater');
		var row = rep.find('.dck-row--tpl').clone().removeClass('dck-row--tpl').prop('hidden', false);
		row.find('[data-name]').each(function () {
			$(this).attr('name', $(this).attr('data-name')).removeAttr('data-name');
		});
		rep.find('.dck-row--tpl').before(row);
		reindex(rep);
		row.find('input,textarea').first().trigger('focus');
	});
	$(document).on('click', '.dck-row__del', function (e) {
		e.preventDefault();
		if (!window.confirm(DCK_CORE.remove)) { return; }
		var rep = $(this).closest('.dck-repeater');
		$(this).closest('.dck-row').remove();
		reindex(rep);
	});
	$(document).on('click', '.dck-up, .dck-down', function (e) {
		e.preventDefault();
		var row = $(this).closest('.dck-row');
		var rep = row.closest('.dck-repeater');
		if ($(this).hasClass('dck-up')) {
			row.prev('.dck-row').before(row);
		} else {
			var n = row.next('.dck-row').not('.dck-row--tpl');
			if (n.length) { n.after(row); }
		}
		reindex(rep);
	});

	/* Quick search */
	$(document).on('input', '.dck-search', function () {
		var q = $(this).val().toLowerCase().trim();
		$('.dck-field').each(function () {
			var hit = !q || ($(this).data('search') || '').toString().indexOf(q) !== -1;
			$(this).toggleClass('is-hidden', !hit);
		});
		$('.dck-card').each(function () {
			$(this).toggle(!q || $(this).find('.dck-field').not('.is-hidden').length > 0 || $(this).find('h2').text().toLowerCase().indexOf(q) !== -1);
		});
	});

	/* Warn before leaving with unsaved changes */
	var dirty = false;
	$(document).on('input change', '.dck-form :input', function () { dirty = true; });
	$(document).on('submit', '.dck-form', function () { dirty = false; });
	$(window).on('beforeunload', function () { if (dirty) { return true; } });
})(jQuery);
