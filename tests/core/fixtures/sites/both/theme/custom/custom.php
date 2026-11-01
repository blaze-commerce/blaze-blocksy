<?php
defined( 'ABSPATH' ) || exit;

// Declares the same function as blaze-site/site.php: loading both would be a redeclare fatal.
function bc_fixture_site_fn() {}
$GLOBALS['bc_site_loaded'][] = 'legacy';
