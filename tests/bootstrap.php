<?php
/**
 * PHPUnit bootstrap: minimal WordPress function stubs so the plugin classes
 * can be unit tested without a full WordPress installation.
 *
 * @package Space2Book
 */

// phpcs:disable

define( 'ABSPATH', __DIR__ . '/' );
define( 'SPACE2BOOK_VERSION', '0.1.0' );
define( 'SPACE2BOOK_PLUGIN_FILE', dirname( __DIR__ ) . '/space2book.php' );
define( 'SPACE2BOOK_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'SPACE2BOOK_PLUGIN_URL', 'https://example.test/wp-content/plugins/space2book/' );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/includes/autoload.php';

/**
 * Shared, resettable state of the WordPress stubs.
 */
final class WP_Stubs {
	public static $options      = array();
	public static $filters      = array();
	public static $actions      = array();
	public static $shortcodes   = array();
	public static $styles       = array();
	public static $scripts      = array();
	public static $locale       = 'en_US';
	public static $can_edit     = false;

	public static function reset() {
		self::$options    = array();
		self::$filters    = array();
		self::$actions    = array();
		self::$shortcodes = array();
		self::$styles     = array();
		self::$scripts    = array();
		self::$locale     = 'en_US';
		self::$can_edit   = false;
	}
}

function get_option( $name, $default = false ) {
	return array_key_exists( $name, WP_Stubs::$options ) ? WP_Stubs::$options[ $name ] : $default;
}

function add_option( $name, $value ) {
	if ( ! array_key_exists( $name, WP_Stubs::$options ) ) {
		WP_Stubs::$options[ $name ] = $value;
	}
	return true;
}

function update_option( $name, $value ) {
	WP_Stubs::$options[ $name ] = $value;
	return true;
}

function add_filter( $hook, $callback, $priority = 10, $args = 1 ) {
	WP_Stubs::$filters[ $hook ][] = $callback;
	return true;
}

function apply_filters( $hook, $value, ...$args ) {
	if ( ! empty( WP_Stubs::$filters[ $hook ] ) ) {
		foreach ( WP_Stubs::$filters[ $hook ] as $callback ) {
			$value = $callback( $value, ...$args );
		}
	}
	return $value;
}

function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
	WP_Stubs::$actions[ $hook ][] = $callback;
	return true;
}

function do_action( $hook, ...$args ) {
	WP_Stubs::$actions[ '__fired_' . $hook ][] = $args;
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function esc_html__( $text, $domain = 'default' ) {
	return esc_html( $text );
}

function esc_attr__( $text, $domain = 'default' ) {
	return esc_attr( $text );
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_url_raw( $url, $protocols = null ) {
	$url    = trim( (string) $url );
	$scheme = strtolower( (string) parse_url( $url, PHP_URL_SCHEME ) );
	$allow  = $protocols ? $protocols : array( 'http', 'https' );
	if ( '' === $url || ! in_array( $scheme, $allow, true ) ) {
		return '';
	}
	return $url;
}

function esc_url( $url, $protocols = null ) {
	return esc_attr( esc_url_raw( $url, $protocols ) );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

function sanitize_text_field( $text ) {
	return trim( preg_replace( '/[\r\n\t ]+/', ' ', strip_tags( (string) $text ) ) );
}

function determine_locale() {
	return WP_Stubs::$locale;
}

function current_user_can( $capability ) {
	return WP_Stubs::$can_edit;
}

function wp_enqueue_style( $handle ) {
	WP_Stubs::$styles[] = $handle;
}

function wp_enqueue_script( $handle ) {
	WP_Stubs::$scripts[] = $handle;
}

function wp_json_encode( $data ) {
	return json_encode( $data );
}

function add_shortcode( $tag, $callback ) {
	WP_Stubs::$shortcodes[ $tag ] = $callback;
}

function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
	$out = array();
	foreach ( $pairs as $name => $default ) {
		$out[ $name ] = array_key_exists( $name, $atts ) ? $atts[ $name ] : $default;
	}
	return $out;
}

function get_block_wrapper_attributes() {
	return 'class="wp-block-space2book-booking"';
}
