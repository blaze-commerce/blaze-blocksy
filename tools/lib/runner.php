<?php
/**
 * Runs the gates in contract order and builds the report tools/check.php prints.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * Gate list, --module scoping and the report shape (the JSON contract swamp reads).
 */
final class Runner {

	/** Every gate code, in report order. */
	const CODES = [
		'php_lint',
		'version_agreement',
		'manifest_schema',
		'manifest_ids',
		'class_name',
		'surface_unique',
		'surface_vocabulary',
		'component_lookalike',
		'client_denylist',
		'no_jquery',
		'no_innerhtml_vars',
		'no_hook_closures',
		'no_strict_types',
		'abspath_guard',
		'duplicate_symbols',
		'dist_contents',
		'modules_md_fresh',
		'phpcs',
		'phpstan',
	];

	/** Gates that stay whole-theme under --module. */
	const GLOBAL_WITH_MODULE = [ 'php_lint', 'version_agreement', 'surface_vocabulary' ];

	/** Whole-theme gates that do not run under --module. */
	const THEME_ONLY = [ 'dist_contents', 'modules_md_fresh' ];

	/** Gate code => implementing static method. */
	const IMPL = [
		'php_lint'            => [ PhpGates::class, 'php_lint' ],
		'version_agreement'   => [ VersionGates::class, 'version_agreement' ],
		'manifest_schema'     => [ ManifestGates::class, 'manifest_schema' ],
		'manifest_ids'        => [ ManifestGates::class, 'manifest_ids' ],
		'class_name'          => [ ManifestGates::class, 'class_name' ],
		'surface_unique'      => [ SurfaceGates::class, 'surface_unique' ],
		'surface_vocabulary'  => [ SurfaceGates::class, 'surface_vocabulary' ],
		'component_lookalike' => [ SurfaceGates::class, 'component_lookalike' ],
		'client_denylist'     => [ DenylistGates::class, 'client_denylist' ],
		'no_jquery'           => [ StyleGates::class, 'no_jquery' ],
		'no_innerhtml_vars'   => [ StyleGates::class, 'no_innerhtml_vars' ],
		'no_hook_closures'    => [ StyleGates::class, 'no_hook_closures' ],
		'no_strict_types'     => [ StyleGates::class, 'no_strict_types' ],
		'abspath_guard'       => [ StyleGates::class, 'abspath_guard' ],
		'duplicate_symbols'   => [ SymbolGates::class, 'duplicate_symbols' ],
		'dist_contents'       => [ DistGates::class, 'dist_contents' ],
		'modules_md_fresh'    => [ ModulesMd::class, 'modules_md_fresh' ],
		'phpcs'               => [ PhpGates::class, 'phpcs' ],
		'phpstan'             => [ PhpGates::class, 'phpstan' ],
	];

	/**
	 * Runs the gates.
	 *
	 * @param string        $root   Theme root.
	 * @param string|null   $module Limit module-scoped gates to this module id.
	 * @param string[]|null $only   Run only these codes (tests); null runs all.
	 * @param array         $opts   Context options (`path`).
	 * @return array{version:string,root:string,module:?string,gates:array,passed:bool}
	 */
	public static function run( string $root, ?string $module = null, ?array $only = null, array $opts = [] ): array {
		$ctx   = new Context( $root, $module, $opts );
		$gates = [];

		if ( null !== $module ) {
			$known   = isset( $ctx->modules()[ $module ] );
			$gates[] = Context::gate(
				'module_exists',
				'fail',
				$known,
				$known ? "module '{$module}' found" : "no module folder modules/{$module}",
				$known ? [] : [ "modules/{$module}" ]
			);
		}

		if ( null === $module || isset( $ctx->modules()[ $module ] ) ) {
			foreach ( self::CODES as $code ) {
				if ( null !== $only && ! in_array( $code, $only, true ) ) {
					continue;
				}
				if ( null !== $module && in_array( $code, self::THEME_ONLY, true ) ) {
					continue;
				}
				// Whole-theme gates ignore --module.
				$scope   = in_array( $code, self::GLOBAL_WITH_MODULE, true ) ? new Context( $root, null, $opts ) : $ctx;
				$gates[] = self::run_gate( $code, $scope );
			}
		}

		$passed = true;
		foreach ( $gates as $gate ) {
			if ( 'fail' === $gate['severity'] && ! $gate['passed'] ) {
				$passed = false;
			}
		}

		return [
			'version' => $ctx->version(),
			'root'    => $ctx->root,
			'module'  => $module,
			'gates'   => $gates,
			'passed'  => $passed,
		];
	}

	/**
	 * Runs one gate; a crash is reported as that gate failing, never as a broken report.
	 *
	 * @param string  $code Gate code.
	 * @param Context $ctx  Context.
	 * @return array
	 */
	private static function run_gate( string $code, Context $ctx ): array {
		try {
			return call_user_func( self::IMPL[ $code ], $ctx );
		} catch ( \Throwable $e ) {
			return Context::gate( $code, 'fail', false, 'gate crashed: ' . $e->getMessage() );
		}
	}
}
