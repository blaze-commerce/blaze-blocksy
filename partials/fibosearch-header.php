<?php
/**
 * FiboSearch Header Template — Renders full search bar in Blocksy header.
 *
 * Overrides FiboSearch's default Blocksy template which renders layout="icon".
 * This outputs the default FiboSearch search bar (Solaris style) matching the
 * live site. Used in both desktop (middle row) and mobile (bottom row).
 *
 * @package Blocksy_Child
 * @date    2026-04-15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<div data-id="search">';
// Default layout: always shows full search bar (desktop and mobile).
// Previously used icon-flexible with breakpoint 689 which showed only an
// icon on mobile — but live site shows a persistent full bar in the
// mobile bottom row.
echo do_shortcode( '[fibosearch]' );
echo '</div>';
