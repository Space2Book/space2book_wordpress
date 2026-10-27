<?php
/**
 * Lightweight autoloader for the Space2Book plugin classes.
 *
 * Maps `Space2Book\Foo_Bar` to `includes/class-foo-bar.php` following the
 * WordPress file naming conventions.
 *
 * @package Space2Book
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'Space2Book\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );

		if ( false !== strpos( $relative, '\\' ) ) {
			return;
		}

		$file = __DIR__ . '/class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);
