<?php
/**
 * Writes docs/MODULES.md from every modules/<id>/module.json (sorted, no timestamps).
 *
 * Usage: php tools/gen-modules-md.php [--root=<dir>] [--check]
 *
 * --check writes nothing and exits 1 when docs/MODULES.md is stale or missing (CI runs this).
 *
 * @package Blocksy_Child
 */

require_once __DIR__ . '/lib/load.php';

exit( \Blaze\Child\Tools\Cli::gen_modules_md( array_slice( $argv, 1 ) ) );
