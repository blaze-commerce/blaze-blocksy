<?php
/**
 * Async Style Handles — stylesheets safe to load non-render-blocking.
 *
 * PURPOSE: data file for the `async-styles` module (`inc/perf/async-styles.php`),
 * which swaps each listed handle's `<link>` to `media="print"
 * onload="this.media='all'"` + a `<noscript>` original. Only list a
 * stylesheet whose above-the-fold rules are already covered elsewhere (the
 * Perfmatters used-CSS, or a RUCSS excluded selector) — everything it styles
 * must be fine unstyled for the few hundred ms until it applies.
 *
 * Never list the used-CSS or any core Blocksy stylesheet: the module refuses
 * `ct-main-styles`, `blocksy-child-style`, `perfmatters-used-css`, any `ct-*`
 * / `blocksy-child-*` handle and any `main.min.css` href regardless
 * (Global Constraint 11).
 *
 * PROMOTED FROM: `bc-site-customizations/sites/austinnaturalmattress/custom/async-fibosearch-css.php`
 * (FiboSearch, ~41 KB `style.min.css`; its visible search bar survives via
 * the RUCSS safelist, only the suggestions dropdown waits for this file).
 *
 * MEASURED EVIDENCE: see `inc/perf/async-styles.php`'s docblock.
 *
 * WHAT DOES NOT TRAVEL WITH THE THEME: a client-specific plugin stylesheet
 * is added with the `blocksy_child_perf_async_style_handles` filter, not here.
 *
 * @package Blocksy_Child
 */

return [
	'dgwt-wcas-style',     // FiboSearch (Ajax Search for WooCommerce).
	'brb-public-main-css', // Business Reviews Bundle widget.
];
