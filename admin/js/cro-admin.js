/**
 * Admin JavaScript
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize color pickers on boosters/settings pages.
		if ($('.cro-color-picker').length && $.fn.wpColorPicker) {
			$('.cro-color-picker').wpColorPicker();
		}

		// Sticky nav: add .is-stuck when nav has scrolled past sentinel (for shadow).
		var sentinel = document.getElementById('cro-admin-layout-nav-sentinel');
		var nav = sentinel ? sentinel.nextElementSibling : null;
		if (sentinel && nav && nav.classList.contains('cro-admin-layout__nav')) {
			var observer = new IntersectionObserver(
				function(entries) {
					entries.forEach(function(entry) {
						if (entry.target === sentinel) {
							if (entry.intersectionRatio === 0) {
								nav.classList.add('is-stuck');
							} else {
								nav.classList.remove('is-stuck');
							}
						}
					});
				},
				{ root: null, rootMargin: '0px', threshold: 0 }
			);
			observer.observe(sentinel);
		}
	});

})(jQuery);
