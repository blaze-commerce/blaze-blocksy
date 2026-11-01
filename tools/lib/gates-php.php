<?php
/**
 * Gates that run PHP tooling: php -l, PHPCS, PHPStan.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * php_lint, phpcs, phpstan.
 */
final class PhpGates {

	/** Parallel php -l processes. */
	const LINT_WORKERS = 8;

	/**
	 * `php -l` on every PHP file (fixtures excluded: some are broken on purpose).
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function php_lint( Context $ctx ): array {
		$queue = [];
		foreach ( $ctx->files() as $rel ) {
			if ( 'php' === strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) ) && ! preg_match( '#(^|/)fixtures/#', $rel ) ) {
				$queue[] = $rel;
			}
		}
		$total   = count( $queue );
		$bad     = [];
		$notes   = [];
		$running = [];

		while ( $queue || $running ) {
			for ( $free = self::LINT_WORKERS - count( $running ); $free > 0 && $queue; $free-- ) {
				$rel  = array_shift( $queue );
				$proc = proc_open(
					// Parse errors go to stdout whatever php.ini says; stderr is not read.
					[ PHP_BINARY, '-d', 'display_errors=stdout', '-d', 'log_errors=0', '-l', $ctx->path( $rel ) ],
					[
						1 => [ 'pipe', 'w' ],
						2 => [ 'file', '\\' === DIRECTORY_SEPARATOR ? 'NUL' : '/dev/null', 'w' ],
					],
					$pipes
				);
				if ( ! is_resource( $proc ) ) {
					$bad[]   = $rel;
					$notes[] = "{$rel}: could not start php -l";
					continue;
				}
				stream_set_blocking( $pipes[1], false );
				$running[] = [ $proc, $pipes[1], $rel, '' ];
			}
			foreach ( $running as $i => $job ) {
				$running[ $i ][3] .= (string) stream_get_contents( $job[1] );
				$status            = proc_get_status( $job[0] );
				if ( $status['running'] ) {
					continue;
				}
				stream_set_blocking( $job[1], true );
				$output = $running[ $i ][3] . (string) stream_get_contents( $job[1] );
				fclose( $job[1] );
				proc_close( $job[0] );
				unset( $running[ $i ] );
				if ( 0 !== $status['exitcode'] || false === strpos( $output, 'No syntax errors' ) ) {
					$line    = preg_match( '/on line (\d+)/', $output, $m ) ? (int) $m[1] : 1;
					$bad[]   = "{$job[2]}:{$line}";
					$notes[] = self::first_error_line( $output );
				}
			}
			if ( $running ) {
				usleep( 2000 );
			}
		}

		sort( $bad, SORT_STRING );
		return Context::findings( 'php_lint', 'fail', $bad, "{$total} PHP files lint clean on PHP " . PHP_VERSION, 'PHP syntax errors on PHP ' . PHP_VERSION, $notes );
	}

	/**
	 * First meaningful line of php -l output.
	 *
	 * @param string $output Output.
	 * @return string
	 */
	private static function first_error_line( string $output ): string {
		foreach ( preg_split( '/\R/', $output ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line && 0 !== strpos( $line, 'Errors parsing' ) ) {
				return $line;
			}
		}
		return 'php -l failed';
	}

	/**
	 * PHPCS with tools/phpcs.xml: warn when not installed, fail on errors when present.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function phpcs( Context $ctx ): array {
		return self::external(
			$ctx,
			'phpcs',
			'tools/phpcs.xml',
			function ( string $bin, string $config, array $paths ): array {
				return array_merge( [ $bin, '--standard=' . $config, '--report=json', '-q', '--no-colors' ], $paths );
			}
		);
	}

	/**
	 * PHPStan with tools/phpstan.neon: warn when not installed, fail on errors when present.
	 *
	 * @param Context $ctx Context.
	 * @return array
	 */
	public static function phpstan( Context $ctx ): array {
		return self::external(
			$ctx,
			'phpstan',
			'tools/phpstan.neon',
			function ( string $bin, string $config, array $paths ): array {
				return array_merge( [ $bin, 'analyse', '-c', $config, '--error-format=json', '--no-progress', '--no-interaction', '--memory-limit=1G' ], $paths );
			}
		);
	}

	/**
	 * Shared runner for phpcs/phpstan: both print a JSON report with totals and per-file messages.
	 *
	 * @param Context  $ctx     Context.
	 * @param string   $tool    Binary name and gate code.
	 * @param string   $config  Relative config path.
	 * @param callable $command fn( bin, abs config, paths ) => argv.
	 * @return array
	 */
	private static function external( Context $ctx, string $tool, string $config, callable $command ): array {
		$bin = $ctx->find_tool( $tool );
		if ( null === $bin ) {
			return Context::gate( $tool, 'warn', false, "{$tool} not installed" );
		}
		if ( ! is_file( $ctx->path( $config ) ) ) {
			return Context::gate( $tool, 'warn', false, "{$config} not found; {$tool} skipped" );
		}

		$paths = [];
		if ( null !== $ctx->module ) {
			$php = $ctx->files_under( [ "modules/{$ctx->module}/" ], [ 'php' ] );
			if ( ! $php ) {
				return Context::gate( $tool, 'fail', true, "modules/{$ctx->module} has no PHP files" );
			}
			$paths = [ $ctx->path( "modules/{$ctx->module}" ) ];
		}

		$run    = Context::exec( $command( $bin, $ctx->path( $config ), $paths ), $ctx->root );
		$report = json_decode( $run['out'], true );
		if ( ! is_array( $report ) || ! isset( $report['totals'] ) ) {
			$first = trim( (string) strtok( $run['out'], "\n" ) );
			return Context::gate( $tool, 'fail', false, "{$tool} did not produce a JSON report (exit {$run['code']})" . ( '' !== $first ? ": {$first}" : '' ) );
		}

		$files = [];
		foreach ( (array) ( $report['files'] ?? [] ) as $abs => $file ) {
			$rel = 0 === strpos( (string) $abs, $ctx->root . '/' ) ? substr( (string) $abs, strlen( $ctx->root ) + 1 ) : (string) $abs;
			foreach ( (array) ( $file['messages'] ?? [] ) as $msg ) {
				// phpcs marks each message ERROR/WARNING; every phpstan message is an error.
				if ( 'WARNING' !== ( $msg['type'] ?? 'ERROR' ) ) {
					$files[] = $rel . ':' . (int) ( $msg['line'] ?? 0 );
				}
			}
		}

		$totals   = $report['totals'];
		$errors   = 'phpcs' === $tool ? (int) ( $totals['errors'] ?? 0 ) : (int) ( $totals['file_errors'] ?? 0 ) + (int) ( $totals['errors'] ?? 0 );
		$warnings = (int) ( $totals['warnings'] ?? 0 );
		$general  = array_map( 'strval', (array) ( $report['errors'] ?? [] ) );
		$summary  = "{$errors} errors" . ( 'phpcs' === $tool ? ", {$warnings} warnings" : '' );

		if ( 0 === $errors ) {
			return Context::gate( $tool, 'fail', true, $summary );
		}
		return Context::gate( $tool, 'fail', false, $summary . ( $general ? ': ' . implode( '; ', array_slice( $general, 0, 3 ) ) : '' ), $files );
	}
}
