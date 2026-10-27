<?php
/**
 * Plugin settings: defaults, retrieval and sanitization.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Access layer for the `space2book_settings` option.
 */
class Settings {

	/**
	 * Option name used to store all plugin settings.
	 */
	const OPTION = 'space2book_settings';

	/**
	 * Display modes supported by the renderer.
	 */
	const MODES = array( 'button', 'inline', 'link' );

	/**
	 * Booking form languages supported by Space2Book.
	 */
	const LANGUAGES = array( 'nl', 'en', 'de', 'fr', 'es' );

	/**
	 * Allowed inline height range in pixels.
	 */
	const MIN_HEIGHT = 300;
	const MAX_HEIGHT = 3000;

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'venue'           => '',
			'language'        => 'auto',
			'mode'            => 'button',
			'inline_height'   => 700,
			'new_tab'         => true,
			'button_text'     => '',
			'button_bg_color' => '#1d4ed8',
			'button_color'    => '#ffffff',
		);
	}

	/**
	 * Returns all settings merged with the defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function all() {
		$stored = get_option( self::OPTION, array() );

		return array_merge( self::defaults(), is_array( $stored ) ? $stored : array() );
	}

	/**
	 * Returns a single setting.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public function get( $key ) {
		$all = $this->all();

		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/**
	 * Sanitizes the full settings array submitted through the Settings API.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();

		$language = isset( $input['language'] ) ? sanitize_key( $input['language'] ) : '';
		$mode     = isset( $input['mode'] ) ? sanitize_key( $input['mode'] ) : '';

		return array(
			'venue'           => self::sanitize_venue( isset( $input['venue'] ) ? $input['venue'] : '' ),
			'language'        => ( 'auto' === $language || in_array( $language, self::LANGUAGES, true ) ) ? $language : $defaults['language'],
			'mode'            => in_array( $mode, self::MODES, true ) ? $mode : $defaults['mode'],
			'inline_height'   => self::sanitize_height( isset( $input['inline_height'] ) ? $input['inline_height'] : 0, $defaults['inline_height'] ),
			'new_tab'         => ! empty( $input['new_tab'] ),
			'button_text'     => isset( $input['button_text'] ) ? sanitize_text_field( (string) $input['button_text'] ) : '',
			'button_bg_color' => self::sanitize_color( isset( $input['button_bg_color'] ) ? $input['button_bg_color'] : '', $defaults['button_bg_color'] ),
			'button_color'    => self::sanitize_color( isset( $input['button_color'] ) ? $input['button_color'] : '', $defaults['button_color'] ),
		);
	}

	/**
	 * Sanitizes a public venue reference.
	 *
	 * Only letters, digits, hyphens and underscores are allowed so the value
	 * can safely be used inside a URL path or query string.
	 *
	 * @param mixed $venue Raw venue reference.
	 * @return string
	 */
	public static function sanitize_venue( $venue ) {
		if ( ! is_scalar( $venue ) ) {
			return '';
		}

		return substr( (string) preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $venue ), 0, 100 );
	}

	/**
	 * Sanitizes an inline height value.
	 *
	 * @param mixed $height   Raw height.
	 * @param int   $fallback Value returned when the input is empty or invalid.
	 * @return int
	 */
	public static function sanitize_height( $height, $fallback ) {
		$height = is_numeric( $height ) ? (int) $height : 0;

		if ( $height <= 0 ) {
			return (int) $fallback;
		}

		return max( self::MIN_HEIGHT, min( self::MAX_HEIGHT, $height ) );
	}

	/**
	 * Sanitizes a hex color.
	 *
	 * @param mixed  $color    Raw color.
	 * @param string $fallback Value returned when the input is invalid.
	 * @return string
	 */
	public static function sanitize_color( $color, $fallback ) {
		if ( is_string( $color ) && preg_match( '/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color ) ) {
			return strtolower( $color );
		}

		return $fallback;
	}
}
