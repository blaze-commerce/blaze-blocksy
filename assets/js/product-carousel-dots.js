/**
 * Product Carousel Dots — PDP Related Products & Recently Viewed
 *
 * Renders bottom dot pagination for the two PDP product carousels and
 * navigates them by programmatically clicking Blocksy's existing Flexy
 * prev/next arrow buttons (which are visually hidden off-screen via CSS).
 *
 * Why this approach (and not Flexy's native `.flexy-pills` mechanism):
 *   Flexy's pill code assumes 1 pill per slide-item (gallery model). For a
 *   multi-column product carousel (8 items, 4 visible at once → 5 valid
 *   leading positions), we want 5 pills. Letting Flexy own the pills with
 *   only 5 children causes it to crash inside its draw loop with
 *   "Cannot read properties of undefined (reading 'classList')" the moment
 *   Flexy's internal currentIndex advances past 4 — it does
 *   `pillsContainerSelector.children[previousCurrentIndex].classList.add('active')`
 *   and there is no children[5..7].
 *
 *   Naming our container `.bc-carousel-dots` (not `.flexy-pills`) makes
 *   Flexy ignore it (its early-out is `if (!options.pillsContainerSelector) return`).
 *   We own click navigation entirely.
 *
 * Strategy:
 *   1. Render `<ul class="bc-carousel-dots"><li>×N</li></ul>` next to .flexy.
 *   2. Pre-mount Flexy via Blocksy's `forcedMount` helper so the off-screen
 *      arrows have working click listeners.
 *   3. Click handler: read current index, compute diff to target, click the
 *      appropriate arrow `abs(diff)` times in a synchronous loop.
 *   4. Update active dot on `blocksy:frontend:flexy:slide-change`.
 *   5. Recompute pill count on resize (visible-cols changes per breakpoint).
 *
 * QA reference: ClickUp 86exbzf96
 */
( function () {
	'use strict';

	var SELECTOR = '.related.products.is-layout-slider, .bc-recently-viewed';
	var DEBOUNCE_MS = 150;

	/**
	 * Read the visible column count for a Flexy carousel from the
	 * --flexy-item-width CSS var Blocksy sets on .flexy-items (resolves to
	 * --grid-columns-width in slider layout, e.g. "25%" for 4 cols).
	 */
	function getVisibleCols( flexyItems ) {
		if ( ! flexyItems ) {
			return 1;
		}
		var raw = window.getComputedStyle( flexyItems ).getPropertyValue( '--flexy-item-width' ).trim();
		var pct = parseFloat( raw );
		if ( raw.endsWith( '%' ) && pct > 0 ) {
			return Math.max( 1, Math.round( 100 / pct ) );
		}
		var firstItem = flexyItems.firstElementChild;
		if ( ! firstItem ) {
			return 1;
		}
		var containerW = flexyItems.clientWidth;
		var itemW = firstItem.getBoundingClientRect().width;
		if ( ! itemW ) {
			return 1;
		}
		return Math.max( 1, Math.round( containerW / itemW ) );
	}

	/**
	 * Build or rebuild the .bc-carousel-dots element for a single carousel.
	 * Idempotent — only updates the <li> count if it differs from the target.
	 */
	function renderDots( section ) {
		var flexy = section.querySelector( '.flexy' );
		var flexyItems = section.querySelector( '.flexy-items' );
		if ( ! flexy || ! flexyItems ) {
			return;
		}

		var totalItems = flexyItems.children.length;
		var visibleCols = getVisibleCols( flexyItems );
		var dotCount = Math.max( 1, totalItems - visibleCols + 1 );
		var noPaging = totalItems <= visibleCols;

		var dots = section.querySelector( ':scope .bc-carousel-dots' );
		if ( ! dots ) {
			dots = document.createElement( 'ul' );
			dots.className = 'bc-carousel-dots';
			// Place after .flexy inside .flexy-container so it sits at the
			// bottom of the carousel block.
			flexy.parentNode.appendChild( dots );
			bindDotClicks( section, dots );
		}

		dots.setAttribute( 'data-bc-empty', noPaging ? '1' : '0' );

		if ( dots.children.length !== dotCount ) {
			dots.innerHTML = '';
			var currentIdx = clampIndex( getCurrentIndex( section ), dotCount );
			for ( var i = 0; i < dotCount; i++ ) {
				var li = document.createElement( 'li' );
				li.setAttribute( 'aria-label', 'Slide ' + ( i + 1 ) );
				li.setAttribute( 'role', 'button' );
				li.setAttribute( 'tabindex', '0' );
				if ( i === currentIdx ) {
					li.className = 'active';
				}
				dots.appendChild( li );
			}
		}
	}

	function clampIndex( idx, count ) {
		if ( idx < 0 ) {
			return 0;
		}
		if ( idx >= count ) {
			return count - 1;
		}
		return idx;
	}

	/**
	 * Wire click + keyboard navigation on the dot row. Bound once per dots
	 * element; uses delegation so re-rendered <li>s still work.
	 */
	function bindDotClicks( section, dots ) {
		var handle = function ( e ) {
			var li = e.target.closest( 'li' );
			if ( ! li || ! dots.contains( li ) ) {
				return;
			}
			if ( e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ' ) {
				return;
			}
			e.preventDefault();
			var index = Array.prototype.indexOf.call( dots.children, li );
			if ( index < 0 ) {
				return;
			}
			navigateTo( section, index );
		};
		dots.addEventListener( 'click', handle );
		dots.addEventListener( 'keydown', handle );
	}

	/**
	 * Navigate by clicking the (off-screen) Flexy arrows abs(diff) times.
	 * Coalesces in-flight mounts so two rapid clicks don't double-mount.
	 */
	function navigateTo( section, targetIndex ) {
		var container = section.querySelector( '.flexy-container' );
		if ( ! container ) {
			return;
		}

		section.bcLatestTarget = targetIndex;

		var doNav = function () {
			var idx = ( typeof section.bcLatestTarget === 'number' )
				? section.bcLatestTarget
				: targetIndex;
			var current = getCurrentIndex( section );
			var diff = idx - current;
			section.bcLatestTarget = null;
			section.bcMountPending = false;
			if ( diff === 0 ) {
				updateActive( section, idx );
				return;
			}
			var arrow = section.querySelector(
				diff > 0 ? '.flexy-arrow-next' : '.flexy-arrow-prev'
			);
			if ( ! arrow ) {
				return;
			}
			var steps = Math.abs( diff );
			for ( var i = 0; i < steps; i++ ) {
				arrow.click();
			}
			// Active state will follow via the slide-change listener; set it
			// here too for snappier feel in case the event lags.
			updateActive( section, idx );
		};

		if ( container.flexy ) {
			doNav();
			return;
		}

		if ( section.bcMountPending ) {
			// Mount in flight from an earlier click — that handler will pick
			// up our updated bcLatestTarget when it resolves.
			return;
		}

		if ( typeof container.forcedMount === 'function' ) {
			section.bcMountPending = true;
			var p = container.forcedMount();
			if ( p && typeof p.then === 'function' ) {
				p.then( doNav, function () {
					section.bcMountPending = false;
				} );
			} else {
				doNav();
			}
			return;
		}

		// Last-resort: poll briefly for forcedMount in case Blocksy's lazy
		// registration hasn't run yet.
		section.bcMountPending = true;
		var attempts = 0;
		var poll = setInterval( function () {
			attempts++;
			if ( typeof container.forcedMount === 'function' ) {
				clearInterval( poll );
				var pp = container.forcedMount();
				if ( pp && typeof pp.then === 'function' ) {
					pp.then( doNav, function () {
						section.bcMountPending = false;
					} );
				} else {
					doNav();
				}
			} else if ( attempts > 40 ) {
				clearInterval( poll );
				section.bcMountPending = false;
			}
		}, 50 );
	}

	/**
	 * Read the current slide index. Tries the --current-item CSS var
	 * (works pre-mount), falls back to .flexy-item-is-visible (post-mount).
	 */
	function getCurrentIndex( section ) {
		var items = section.querySelector( '.flexy-items' );
		if ( ! items ) {
			return 0;
		}
		var raw = window.getComputedStyle( items ).getPropertyValue( '--current-item' ).trim();
		var idx = parseInt( raw, 10 );
		if ( ! isNaN( idx ) && idx >= 0 ) {
			return idx;
		}
		var children = items.children;
		for ( var i = 0; i < children.length; i++ ) {
			if ( children[ i ].classList.contains( 'flexy-item-is-visible' ) ) {
				return i;
			}
		}
		return 0;
	}

	function updateActive( section, index ) {
		var dots = section.querySelector( '.bc-carousel-dots' );
		if ( ! dots ) {
			return;
		}
		var clamped = clampIndex( index, dots.children.length );
		Array.prototype.forEach.call( dots.children, function ( li, i ) {
			li.classList.toggle( 'active', i === clamped );
		} );
	}

	/**
	 * Pre-mount Flexy on init so the off-screen arrows have wired click
	 * listeners by the time the user clicks a dot. Trade-off: defeats
	 * Blocksy's lazy-load for these two carousels (Flexy chunk loads on PDP
	 * page load instead of on first interaction). We accept this — the
	 * carousels are below the fold and Flexy is ~10-30KB.
	 */
	function preMountFlexy( section ) {
		var container = section.querySelector( '.flexy-container' );
		if ( ! container || container.flexy ) {
			return;
		}
		if ( typeof container.forcedMount === 'function' ) {
			container.forcedMount();
			return;
		}
		var attempts = 0;
		var poll = setInterval( function () {
			attempts++;
			if ( typeof container.forcedMount === 'function' ) {
				clearInterval( poll );
				container.forcedMount();
			} else if ( attempts > 40 ) {
				clearInterval( poll );
			}
		}, 50 );
	}

	function init() {
		var sections = document.querySelectorAll( SELECTOR );
		if ( ! sections.length ) {
			return;
		}

		Array.prototype.forEach.call( sections, function ( section ) {
			renderDots( section );
			preMountFlexy( section );
		} );

		// Sync active dot with Flexy's own slide-change events — fires on
		// arrow clicks (incl. our programmatic ones) and on swipe gestures.
		document.addEventListener( 'blocksy:frontend:flexy:slide-change', function ( e ) {
			var detail = e.detail || {};
			var instance = detail.instance;
			if ( ! instance ) {
				return;
			}
			var sliderEl = instance.flexyAttributeEl || instance.el || null;
			var section = sliderEl && sliderEl.closest ? sliderEl.closest( SELECTOR ) : null;
			if ( ! section ) {
				return;
			}
			updateActive( section, getCurrentIndex( section ) );
		} );

		var resizeTimer = null;
		window.addEventListener( 'resize', function () {
			if ( resizeTimer ) {
				clearTimeout( resizeTimer );
			}
			resizeTimer = setTimeout( function () {
				Array.prototype.forEach.call(
					document.querySelectorAll( SELECTOR ),
					function ( section ) {
						renderDots( section );
					}
				);
			}, DEBOUNCE_MS );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
