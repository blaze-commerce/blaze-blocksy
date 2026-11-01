<?php
/**
 * The quality gates (spec 4.11): one implementation for the theme's CI and for swamp.
 *
 * Usage: php tools/check.php [--json] [--root=<dir>] [--module=<id>]
 *
 * Exit 0 when no `fail` gate failed, 1 otherwise, 2 on bad arguments. With --json, stdout is only the
 * report: { version, root, module, gates: [ { code, severity, passed, detail, files } ], passed }.
 * --module limits the module-scoped gates to that module; php_lint, version_agreement and
 * surface_vocabulary stay whole-theme; dist_contents and modules_md_fresh do not run.
 *
 * @package Blocksy_Child
 */

require_once __DIR__ . '/lib/load.php';

exit( \Blaze\Child\Tools\Cli::check( array_slice( $argv, 1 ) ) );
