<?php
defined( 'ABSPATH' ) || exit;

return [
	'modules'    => [ 'alpha' => 1, 'beta' => 0, 'gamma' => 'yes', 7 => true ],
	'settings'   => [ 'alpha' => [ 'color' => 'blue' ], 'beta' => 'not-an-array' ],
	'on_failure' => 'sideways',
];
