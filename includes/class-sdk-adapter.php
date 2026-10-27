<?php
/**
 * Integration adapter between WordPress and the Space2Book booking frontend.
 *
 * The official Space2Book Embed SDK and the secure iframe architecture are
 * developed separately. This class is the single place where the plugin
 * decides which URLs and scripts are used, so the SDK can be connected
 * later without touching the renderer, shortcode or block code.
 *
 * Integration points (all optional):
 *
 * - Constant `SPACE2BOOK_BOOKING_URL_TEMPLATE` / filter
 *   `space2book_booking_url_template`: URL template of the public booking
 *   page. Supports the `{venue}` and `{language}` placeholders.
 * - Filters `space2book_booking_url` and `space2book_embed_url`: final URL
 *   used for direct links and for embedded (modal / inline) forms.
 * - Constant `SPACE2BOOK_SDK_URL` / filter `space2book_sdk_script_url`: URL
 *   of the official Embed SDK script. When set, it is loaded before the
 *   plugin's frontend script, which exposes `window.Space2BookWP.registerDriver()`
 *   so the SDK can take over opening modals and mounting inline forms.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Builds booking URLs and registers frontend assets.
 */
class Sdk_Adapter {

	/**
	 * Placeholder URL template for the public booking page.
	 *
	 * This is a configurable placeholder until the official public booking
	 * URL format is documented. Override it with the
	 * `SPACE2BOOK_BOOKING_URL_TEMPLATE` constant or the
	 * `space2book_booking_url_template` filter.
	 */
	const DEFAULT_BOOKING_URL_TEMPLATE = 'https://app.space2book.com/book/{venue}?lang={language}';

	/**
	 * Script / style handle of the frontend assets.
	 */
	const HANDLE = 'space2book';

	/**
	 * Script handle of the official Embed SDK.
	 */
	const SDK_HANDLE = 'space2book-sdk';

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Hooks the adapter into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_assets' ) );
	}

	/**
	 * Returns the URL template of the public booking page.
	 *
	 * @return string
	 */
	public function get_url_template() {
		$template = defined( 'SPACE2BOOK_BOOKING_URL_TEMPLATE' ) ? (string) SPACE2BOOK_BOOKING_URL_TEMPLATE : self::DEFAULT_BOOKING_URL_TEMPLATE;

		/**
		 * Filters the URL template of the public Space2Book booking page.
		 *
		 * @param string $template URL template with `{venue}` and `{language}` placeholders.
		 */
		return (string) apply_filters( 'space2book_booking_url_template', $template );
	}

	/**
	 * Resolves a language code to one supported by the booking form.
	 *
	 * `auto` (or an empty value) follows the WordPress locale, falling back
	 * to English for unsupported locales.
	 *
	 * @param string $language Language code, `auto` or empty.
	 * @return string
	 */
	public function resolve_language( $language ) {
		$language = strtolower( (string) $language );

		if ( in_array( $language, Settings::LANGUAGES, true ) ) {
			return $language;
		}

		$locale = strtolower( substr( (string) determine_locale(), 0, 2 ) );

		return in_array( $locale, Settings::LANGUAGES, true ) ? $locale : 'en';
	}

	/**
	 * Returns the URL of the public booking page (used for direct links and
	 * as no-JavaScript fallback).
	 *
	 * @param string $venue    Public venue reference.
	 * @param string $language Supported language code.
	 * @return string Empty string when no valid URL can be built.
	 */
	public function get_booking_url( $venue, $language ) {
		$url = $this->build_url( $venue, $language );

		/**
		 * Filters the URL of the public Space2Book booking page.
		 *
		 * @param string $url      Booking URL.
		 * @param string $venue    Public venue reference.
		 * @param string $language Language code.
		 */
		return esc_url_raw( (string) apply_filters( 'space2book_booking_url', $url, $venue, $language ), array( 'https', 'http' ) );
	}

	/**
	 * Returns the URL loaded inside the modal / inline iframe.
	 *
	 * @param string $venue    Public venue reference.
	 * @param string $language Supported language code.
	 * @return string Empty string when no valid URL can be built.
	 */
	public function get_embed_url( $venue, $language ) {
		$url = $this->get_booking_url( $venue, $language );

		/**
		 * Filters the URL loaded in embedded (modal and inline) booking forms.
		 *
		 * Use this when the official Embed SDK documents a dedicated embed URL.
		 *
		 * @param string $url      Embed URL.
		 * @param string $venue    Public venue reference.
		 * @param string $language Language code.
		 */
		return esc_url_raw( (string) apply_filters( 'space2book_embed_url', $url, $venue, $language ), array( 'https', 'http' ) );
	}

	/**
	 * Returns the URL of the official Embed SDK script, if configured.
	 *
	 * @return string
	 */
	public function get_sdk_script_url() {
		$url = defined( 'SPACE2BOOK_SDK_URL' ) ? (string) SPACE2BOOK_SDK_URL : '';

		/**
		 * Filters the URL of the official Space2Book Embed SDK script.
		 *
		 * Empty by default: the plugin then uses its built-in iframe driver.
		 *
		 * @param string $url SDK script URL.
		 */
		return esc_url_raw( (string) apply_filters( 'space2book_sdk_script_url', $url ), array( 'https' ) );
	}

	/**
	 * Registers (but does not enqueue) the frontend assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		$deps    = array();
		$sdk_url = $this->get_sdk_script_url();

		if ( '' !== $sdk_url ) {
			// The SDK is versioned by its own URL.
			wp_register_script( self::SDK_HANDLE, $sdk_url, array(), null, array( 'strategy' => 'defer' ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			$deps[] = self::SDK_HANDLE;
		}

		wp_register_style( self::HANDLE, SPACE2BOOK_PLUGIN_URL . 'assets/css/space2book.css', array(), SPACE2BOOK_VERSION );
		wp_register_script(
			self::HANDLE,
			SPACE2BOOK_PLUGIN_URL . 'assets/js/space2book.js',
			$deps,
			SPACE2BOOK_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_add_inline_script( self::HANDLE, 'window.space2bookConfig = ' . wp_json_encode( $this->get_script_config() ) . ';', 'before' );
	}

	/**
	 * Enqueues the frontend assets. Called by the renderer only when a
	 * booking element is actually present on the page.
	 *
	 * @param bool $with_script Whether the interactive script is needed.
	 * @return void
	 */
	public function enqueue_assets( $with_script ) {
		wp_enqueue_style( self::HANDLE );

		if ( $with_script ) {
			wp_enqueue_script( self::HANDLE );
		}
	}

	/**
	 * Configuration passed to the frontend script.
	 *
	 * @return array<string, mixed>
	 */
	public function get_script_config() {
		$config = array(
			'i18n' => array(
				'dialogLabel' => __( 'Booking form', 'space2book' ),
				'close'       => __( 'Close booking form', 'space2book' ),
				'iframeTitle' => __( 'Space2Book booking form', 'space2book' ),
			),
		);

		/**
		 * Filters the configuration passed to the frontend script
		 * (`window.space2bookConfig`).
		 *
		 * @param array $config Script configuration.
		 */
		return (array) apply_filters( 'space2book_script_config', $config );
	}

	/**
	 * Replaces the template placeholders.
	 *
	 * @param string $venue    Public venue reference.
	 * @param string $language Language code.
	 * @return string
	 */
	private function build_url( $venue, $language ) {
		$venue = Settings::sanitize_venue( $venue );

		if ( '' === $venue ) {
			return '';
		}

		return strtr(
			$this->get_url_template(),
			array(
				'{venue}'    => rawurlencode( $venue ),
				'{language}' => rawurlencode( (string) $language ),
			)
		);
	}
}
