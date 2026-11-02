<?php
/**
 * Command-line entry points of tools/check.php, tools/gen-modules-md.php and tools/build.php.
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * Each method parses its options, does the work and returns the process exit code.
 */
final class Cli {

	/** Gate files listed per gate in text output before "... and N more". */
	const TEXT_FILES_LIMIT = 25;

	/**
	 * Php tools/check.php [--json] [--root=<dir>] [--module=<id>]: exit 0 when no fail gate failed, 1 when one
	 * did, 2 on bad arguments.
	 *
	 * @param string[] $argv Arguments without the script name.
	 * @return int
	 */
	public static function check( array $argv ): int {
		$opts = self::options( $argv, [ 'json', 'root', 'module', 'help' ], [ 'root', 'module' ] );
		if ( null === $opts || isset( $opts['help'] ) ) {
			fwrite( STDERR, "Usage: php tools/check.php [--json] [--root=<dir>] [--module=<id>]\n" );
			return null === $opts ? 2 : 0;
		}
		$root = $opts['root'] ?? dirname( __DIR__, 2 );
		if ( ! is_dir( $root ) ) {
			fwrite( STDERR, "No such directory: {$root}\n" );
			return 2;
		}
		$module = isset( $opts['module'] ) && '' !== $opts['module'] ? $opts['module'] : null;
		$report = Runner::run( $root, $module );

		if ( isset( $opts['json'] ) ) {
			$json = self::encode_report( $report );
			echo $json, "\n";
			return $report['passed'] && false !== strpos( $json, '"passed": true' ) ? 0 : 1;
		}

		$counts = [
			'PASS' => 0,
			'FAIL' => 0,
			'WARN' => 0,
		];
		printf( "blocksy-child %s  root %s%s\n\n", $report['version'], $report['root'], null === $module ? '' : "  module {$module}" );
		foreach ( $report['gates'] as $gate ) {
			$label = $gate['passed'] ? 'PASS' : ( 'warn' === $gate['severity'] ? 'WARN' : 'FAIL' );
			++$counts[ $label ];
			printf( "%-4s  %-20s %s\n", $label, $gate['code'], $gate['detail'] );
			foreach ( array_slice( $gate['files'], 0, self::TEXT_FILES_LIMIT ) as $file ) {
				echo "        {$file}\n";
			}
			if ( count( $gate['files'] ) > self::TEXT_FILES_LIMIT ) {
				printf( "        ... and %d more\n", count( $gate['files'] ) - self::TEXT_FILES_LIMIT );
			}
		}
		printf( "\n%d gates: %d passed, %d failed, %d warnings\n", count( $report['gates'] ), $counts['PASS'], $counts['FAIL'], $counts['WARN'] );

		return $report['passed'] ? 0 : 1;
	}

	/**
	 * The report as JSON. Invalid UTF-8 (e.g. a file name in a detail) is substituted; if encoding still
	 * fails, a minimal valid report with passed false is returned, so stdout is always the contract.
	 *
	 * @param array $report Runner::run() report.
	 * @return string
	 */
	public static function encode_report( array $report ): string {
		$flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
		try {
			return json_encode( $report, $flags | JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			$fallback = [
				'version' => is_string( $report['version'] ?? null ) ? $report['version'] : '',
				'root'    => is_string( $report['root'] ?? null ) ? $report['root'] : '',
				'module'  => is_string( $report['module'] ?? null ) ? $report['module'] : null,
				'gates'   => [ Context::gate( 'report_encoding', 'fail', false, 'report could not be JSON-encoded: ' . $e->getMessage() ) ],
				'passed'  => false,
			];
			return (string) json_encode( $fallback, $flags );
		}
	}

	/**
	 * Php tools/gen-modules-md.php [--root=<dir>] [--check]: writes docs/MODULES.md; with --check writes
	 * nothing and returns 1 when the file is stale or missing.
	 *
	 * @param string[] $argv Arguments without the script name.
	 * @return int
	 */
	public static function gen_modules_md( array $argv ): int {
		$opts = self::options( $argv, [ 'root', 'check' ], [ 'root' ] );
		if ( null === $opts ) {
			fwrite( STDERR, "Usage: php tools/gen-modules-md.php [--root=<dir>] [--check]\n" );
			return 2;
		}
		$ctx  = new Context( $opts['root'] ?? dirname( __DIR__, 2 ) );
		$text = ModulesMd::render( $ctx );

		if ( isset( $opts['check'] ) ) {
			if ( $ctx->read( ModulesMd::FILE ) !== $text ) {
				fwrite( STDERR, ModulesMd::FILE . " is stale or missing; run php tools/gen-modules-md.php\n" );
				return 1;
			}
			echo ModulesMd::FILE, " is up to date\n";
			return 0;
		}

		$file = $ctx->path( ModulesMd::FILE );
		if ( ! is_dir( dirname( $file ) ) ) {
			mkdir( dirname( $file ), 0777, true );
		}
		file_put_contents( $file, $text );
		echo 'Wrote ', ModulesMd::FILE, "\n";
		return 0;
	}

	/**
	 * Php tools/build.php --out=<dir> [--root=<dir>]: writes <dir>/blocksy-child.zip.
	 *
	 * @param string[] $argv Arguments without the script name.
	 * @return int
	 */
	public static function build( array $argv ): int {
		$opts = self::options( $argv, [ 'out', 'root' ], [ 'out', 'root' ] );
		if ( null === $opts || empty( $opts['out'] ) ) {
			fwrite( STDERR, "Usage: php tools/build.php --out=<dir> [--root=<dir>]\n" );
			return 2;
		}
		try {
			$built = Dist::build( $opts['root'] ?? dirname( __DIR__, 2 ), $opts['out'] );
		} catch ( \RuntimeException $e ) {
			fwrite( STDERR, 'Build failed: ' . $e->getMessage() . "\n" );
			return 1;
		}
		printf( "Wrote %s (%d files)\n", $built['zip'], count( $built['files'] ) );
		return 0;
	}

	/**
	 * Parses --flag and --key=value options; null on an unknown option, a stray argument or a missing value.
	 *
	 * @param string[] $argv    Arguments without the script name.
	 * @param string[] $allowed Option names.
	 * @param string[] $valued  Options that need "=value".
	 * @return array<string,string|true>|null
	 */
	public static function options( array $argv, array $allowed, array $valued ): ?array {
		$out = [];
		foreach ( $argv as $arg ) {
			if ( ! preg_match( '/^--([a-z-]+)(?:=(.*))?$/s', $arg, $m ) || ! in_array( $m[1], $allowed, true ) ) {
				return null;
			}
			$has_value = isset( $m[2] );
			if ( in_array( $m[1], $valued, true ) !== $has_value ) {
				return null;
			}
			$out[ $m[1] ] = $has_value ? $m[2] : true;
		}
		return $out;
	}
}
