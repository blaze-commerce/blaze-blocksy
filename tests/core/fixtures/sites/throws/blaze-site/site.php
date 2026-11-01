<?php
defined( 'ABSPATH' ) || exit;

// Tries to switch every module on while site-config.php is broken: must not boot anything.
add_filter( 'blaze_child/module_enabled', '__return_true' );
$GLOBALS['bc_site_loaded'][] = 'blaze-site';
