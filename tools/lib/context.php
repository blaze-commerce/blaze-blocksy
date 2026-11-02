<?php
/**
 * What every gate reads: the theme root, its files, its manifests and the optional --module scope.
 *
 * CLI only (tools/ is never shipped and never loaded by WordPress).
 *
 * @package Blocksy_Child
 */

namespace Blaze\Child\Tools;

/**
 * Read-only view of one theme checkout.
 */
final class Context {

	/** Module statuses whose code the style gates check (spec 4.11: beta/stable; legacy and planned are stubs). */
	const STYLE_STATUSES = [ 'beta', 'stable' ];

	/** Folders never walked: VCS data and installed dependencies. */
	const SKIP_DIRS = [ '.git', 'vendor', 'node_modules' ];

	/**
	 * @var string Absolute root, no trailing slash.
	 */
	public $root;

	/**
	 * @var string|null Module id from --module, or null for the whole theme.
	 */
	public $module;

	/**
	 * @var string PATH used to look up phpcs/phpstan (tests pass '' to hide them).
	 */
	public $path_env;

	/**
	 * @var string[]|null
	 */
	private $files = null;

	/**
	 * @var array<string,array>|null
	 */
	private $modules = null;

	/**
	 * @param string      $root   Theme root.
	 * @param string|null $module Module id or null.
	 * @param array       $opts   `path` => PATH override for tool lookup.
	 */
	public function __construct( string $root, ?string $module = null, array $opts = [] ) {
		$real           = realpath( $root );
		$this->root     = rtrim( false !== $real ? $real : $root, '/\\' );
		$this->module   = $module;
		$this->path_env = array_key_exists( 'path', $opts ) ? (string) $opts['path'] : (string) getenv( 'PATH' );
	}

	/**
	 * Absolute path of a root-relative path.
	 *
	 * @param string $rel Relative path.
	 * @return string
	 */
	public function path( string $rel ): string {
		return $this->root . '/' . ltrim( $rel, '/' );
	}

	/**
	 * Contents of a root-relative file, '' when unreadable.
	 *
	 * @param string $rel Relative path.
	 * @return string
	 */
	public function read( string $rel ): string {
		$path = $this->path( $rel );
		return is_file( $path ) ? (string) file_get_contents( $path ) : '';
	}

	/**
	 * Every file under the root, relative, sorted ('/' separators).
	 *
	 * @return string[]
	 */
	public function files(): array {
		if ( null === $this->files ) {
			$this->files = self::walk( $this->root );
		}
		return $this->files;
	}

	/**
	 * Files under a directory (relative), sorted, skipping SKIP_DIRS.
	 *
	 * @param string $root Absolute directory.
	 * @return string[]
	 */
	public static function walk( string $root ): array {
		$out   = [];
		$stack = [ '' ];
		while ( $stack ) {
			$rel     = array_pop( $stack );
			$entries = scandir( '' === $rel ? $root : $root . '/' . $rel );
			foreach ( false !== $entries ? $entries : [] as $name ) {
				if ( '.' === $name || '..' === $name || in_array( $name, self::SKIP_DIRS, true ) ) {
					continue;
				}
				$child = '' === $rel ? $name : $rel . '/' . $name;
				if ( is_dir( $root . '/' . $child ) ) {
					$stack[] = $child;
				} else {
					$out[] = $child;
				}
			}
		}
		sort( $out, SORT_STRING );
		return $out;
	}

	/**
	 * True for paths inside a tests/ folder at any depth, fixtures included (never checked by code gates).
	 * A fixtures/ folder outside tests/ is ordinary code and is checked.
	 *
	 * @param string $rel Relative path.
	 * @return bool
	 */
	public static function is_test_path( string $rel ): bool {
		return 1 === preg_match( '#(^|/)tests/#', $rel );
	}

	/**
	 * Every folder under modules/, keyed by folder name: manifest file, raw text and decoded data.
	 *
	 * @return array<string,array{dir:string,file:string,exists:bool,raw:string,data:?array,obj:mixed}>
	 */
	public function modules(): array {
		if ( null !== $this->modules ) {
			return $this->modules;
		}
		$out  = [];
		$dirs = glob( $this->root . '/modules/*', GLOB_ONLYDIR );
		foreach ( false !== $dirs ? $dirs : [] as $dir ) {
			$name         = basename( $dir );
			$file         = "modules/{$name}/module.json";
			$raw          = $this->read( $file );
			$data         = json_decode( $raw, true );
			$out[ $name ] = [
				'dir'    => "modules/{$name}",
				'file'   => $file,
				'exists' => is_file( $this->path( $file ) ),
				'raw'    => $raw,
				'data'   => is_array( $data ) ? $data : null,
				'obj'    => json_decode( $raw ),
			];
		}
		ksort( $out, SORT_STRING );
		$this->modules = $out;
		return $out;
	}

	/**
	 * Modules a module-scoped gate looks at: all of them, or only --module.
	 *
	 * @return array<string,array>
	 */
	public function scoped_modules(): array {
		$all = $this->modules();
		if ( null === $this->module ) {
			return $all;
		}
		return isset( $all[ $this->module ] ) ? [ $this->module => $all[ $this->module ] ] : [];
	}

	/**
	 * True when a module folder is inside the current scope.
	 *
	 * @param string $name Module folder name.
	 * @return bool
	 */
	public function in_scope( string $name ): bool {
		return null === $this->module || $this->module === $name;
	}

	/**
	 * Files the style gates check: src/, tools/ and beta/stable module folders (or only --module's
	 * folder), never tests/ (fixtures included). inc/, v1 assets/, woocommerce/ and partials/ are never in scope.
	 *
	 * @param string[] $extensions Lowercase extensions to keep; empty keeps every file.
	 * @return string[]
	 */
	public function style_files( array $extensions = [] ): array {
		$prefixes = null === $this->module ? [ 'src/', 'tools/' ] : [];
		foreach ( $this->scoped_modules() as $name => $mod ) {
			if ( in_array( $mod['data']['status'] ?? '', self::STYLE_STATUSES, true ) ) {
				$prefixes[] = "modules/{$name}/";
			}
		}
		return $this->files_under( $prefixes, $extensions );
	}

	/**
	 * Non-test files under any of the prefixes, filtered by extension.
	 *
	 * @param string[] $prefixes   Relative folder prefixes ending in '/'.
	 * @param string[] $extensions Lowercase extensions; empty keeps every file.
	 * @return string[]
	 */
	public function files_under( array $prefixes, array $extensions = [] ): array {
		$out = [];
		foreach ( $this->files() as $rel ) {
			if ( self::is_test_path( $rel ) ) {
				continue;
			}
			if ( $extensions && ! in_array( strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) ), $extensions, true ) ) {
				continue;
			}
			foreach ( $prefixes as $prefix ) {
				if ( 0 === strpos( $rel, $prefix ) ) {
					$out[] = $rel;
					break;
				}
			}
		}
		return $out;
	}

	/**
	 * style.css `Version:` header, '' when absent.
	 *
	 * @return string
	 */
	public function version(): string {
		return preg_match( '/^[ \t\/*#@]*Version:\s*(\S+)/mi', $this->read( 'style.css' ), $m ) ? $m[1] : '';
	}

	/**
	 * 1-based line number of a byte offset.
	 *
	 * @param string $text   Text.
	 * @param int    $offset Byte offset.
	 * @return int
	 */
	public static function line_at( string $text, int $offset ): int {
		return substr_count( substr( $text, 0, $offset ), "\n" ) + 1;
	}

	/**
	 * Line of the first `"key":` in a JSON text, 1 when absent.
	 *
	 * @param string $raw JSON text.
	 * @param string $key Key.
	 * @return int
	 */
	public static function key_line( string $raw, string $key ): int {
		$pos = strpos( $raw, '"' . $key . '"' );
		return false === $pos ? 1 : self::line_at( $raw, $pos );
	}

	/**
	 * Absolute path of an executable: <root>/vendor/bin/<name>, else the first match on PATH.
	 *
	 * @param string $name Binary name.
	 * @return string|null
	 */
	public function find_tool( string $name ): ?string {
		$local = $this->path( "vendor/bin/{$name}" );
		if ( is_file( $local ) && is_executable( $local ) ) {
			return $local;
		}
		foreach ( explode( PATH_SEPARATOR, $this->path_env ) as $dir ) {
			if ( '' !== $dir && is_file( "{$dir}/{$name}" ) && is_executable( "{$dir}/{$name}" ) ) {
				return "{$dir}/{$name}";
			}
		}
		return null;
	}

	/**
	 * Runs a command without a shell; stdout is returned, stderr discarded.
	 *
	 * @param string[]    $cmd Command and arguments.
	 * @param string|null $cwd Working directory.
	 * @return array{code:int,out:string}
	 */
	public static function exec( array $cmd, ?string $cwd = null ): array {
		$null = '\\' === DIRECTORY_SEPARATOR ? 'NUL' : '/dev/null';
		$proc = proc_open(
			$cmd,
			[
				1 => [ 'pipe', 'w' ],
				2 => [ 'file', $null, 'w' ],
			],
			$pipes,
			$cwd
		);
		if ( ! is_resource( $proc ) ) {
			return [
				'code' => 127,
				'out'  => '',
			];
		}
		$out = (string) stream_get_contents( $pipes[1] );
		fclose( $pipes[1] );
		return [
			'code' => proc_close( $proc ),
			'out'  => $out,
		];
	}

	/**
	 * One gate result in the JSON contract shape.
	 *
	 * @param string   $code     Gate code.
	 * @param string   $severity 'fail' or 'warn'.
	 * @param bool     $passed   Result.
	 * @param string   $detail   One line for people.
	 * @param string[] $files     Offending "path:line" entries.
	 * @return array
	 */
	public static function gate( string $code, string $severity, bool $passed, string $detail, array $files = [] ): array {
		$files = array_values( array_unique( $files ) );
		return [
			'code'     => $code,
			'severity' => $severity,
			'passed'   => $passed,
			'detail'   => $detail,
			'files'    => $files,
		];
	}

	/**
	 * Gate from a list of findings: passes when empty.
	 *
	 * @param string   $code     Gate code.
	 * @param string   $severity 'fail' or 'warn'.
	 * @param string[] $files    Offending "path:line" entries.
	 * @param string   $ok       Detail when passed.
	 * @param string   $bad      Detail prefix when failed (the count is appended).
	 * @param string[] $notes    Extra detail fragments when failed.
	 * @return array
	 */
	public static function findings( string $code, string $severity, array $files, string $ok, string $bad, array $notes = [] ): array {
		$files = array_values( array_unique( $files ) );
		if ( ! $files && ! $notes ) {
			return self::gate( $code, $severity, true, $ok );
		}
		$detail = $bad . ' (' . max( count( $files ), count( $notes ) ) . ')';
		if ( $notes ) {
			$detail .= ': ' . implode( '; ', array_unique( $notes ) );
		}
		return self::gate( $code, $severity, false, $detail, $files );
	}
}
