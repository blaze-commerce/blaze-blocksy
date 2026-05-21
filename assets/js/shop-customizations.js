/**
 * Shop Customizations — Update result count after AJAX load-more.
 *
 * Blocksy's infinite scroll / load-more appends products via AJAX but does not
 * update the static result count text. This script listens for the Blocksy
 * ct:infinite-scroll:load event and recalculates the visible product count.
 *
 * @date 2026-04-16
 */
(function () {
	document.addEventListener('ct:infinite-scroll:load', function () {
		var container = document.querySelector('.products');

		if (!container) {
			return;
		}

		var visibleCount = container.querySelectorAll('.product').length;
		var resultCounts = document.querySelectorAll('.woocommerce-result-count');

		resultCounts.forEach(function (el) {
			// Match "Showing X–Y of Z result(s)"
			var match = el.textContent.match(
				/Showing\s+(\d+)\s*[\u2013\-]\s*\d+\s+of\s+(\d+)\s+result/
			);

			if (match) {
				var first = parseInt(match[1], 10);
				var total = parseInt(match[2], 10);
				var last = Math.min(visibleCount, total);

				el.textContent =
					'Showing ' + first + '\u2013' + last + ' of ' + total + ' results';
			}
		});
	});
})();
