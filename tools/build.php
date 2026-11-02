<?php
/**
 * Builds the distribution zip: <out>/blocksy-child.zip with root folder blocksy-child/, .distignore
 * applied and .blaze-manifest.json (sorted JSON list of shipped paths) inside.
 *
 * Usage: php tools/build.php --out=<dir> [--root=<dir>]
 *
 * @package Blocksy_Child
 */

require_once __DIR__ . '/lib/load.php';

exit( \Blaze\Child\Tools\Cli::build( array_slice( $argv, 1 ) ) );
