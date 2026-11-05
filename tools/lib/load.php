<?php
/**
 * Loads the gate library (tools/check.php, tools/build.php, tools/gen-modules-md.php and the tests use it).
 *
 * @package Blocksy_Child
 */

require_once __DIR__ . '/context.php';
require_once __DIR__ . '/runner.php';
require_once __DIR__ . '/gates-php.php';
require_once __DIR__ . '/gates-version.php';
require_once __DIR__ . '/gates-manifest.php';
require_once __DIR__ . '/gates-surfaces.php';
require_once __DIR__ . '/gates-denylist.php';
require_once __DIR__ . '/gates-style.php';
require_once __DIR__ . '/gates-symbols.php';
require_once __DIR__ . '/dist.php';
require_once __DIR__ . '/gates-dist.php';
require_once __DIR__ . '/modules-md.php';
require_once __DIR__ . '/cli.php';
