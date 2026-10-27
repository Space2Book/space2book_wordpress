<?php
/**
 * Renders the booking button, inline embed and direct link markup.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Shared renderer used by the shortcode and the block.
 */
class Renderer {

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * SDK adapter instance.
	 *
	 * @var Sdk_Adapter
	 */
	private $adapter;

	/**
	 * Constructor.
	 *
	 * @param Settings    $settings Settings instance.
	 * @param Sdk_Adapter $adapter  SDK adapter instance.
	 */
	public function __construct( Settings $settings, Sdk_Adapter $adapter ) {
		$this->settings = $settings;
		$this->adapter  = $adapter;
	}

	/**
	 * Merges the given options with the configured defaults and validates them.
	 *
	 * @param array<string, mixed> $args {
	 *     Optional. Rendering options. Empty values fall back to the settings.
	 *
	 *     @type string $venue    Public venue reference.
	 *     @type string $mode     Display mode: button, inline or link.
	 *     @type string $language Language code (nl, en, de, fr, es) or `auto`.
	 *     @type string $text     Button / link text.
	 *     @type int    $height   Inline embed height in pixels.
	 * }
	 * @return array<string, mixed>
	 */
	public function normalize( array $args ) {
		$settings = $this->settings->all();

		$venue = Settings::sanitize_venue( isset( $args['venue'] ) ? $args['venue'] : '' );
		$venue = '' !== $venue ? $venue : Settings::sanitize_venue( $settings['venue'] );

		$mode = isset( $args['mode'] ) ? sanitize_key( (string) $args['mode'] ) : '';
		$mode = in_array( $mode, Settings::MODES, true ) ? $mode : $settings['mode'];

		$language = isset( $args['language'] ) ? sanitize_key( (string) $args['language'] ) : '';
		$language = ( 'auto' === $language || in_array( $language, Settings::LANGUAGES, true ) ) ? $language : $settings['language'];

		$text = isset( $args['text'] ) ? trim( sanitize_text_field( (string) $args['text'] ) ) : '';
		$text = '' !== $text ? $text : trim( (string) $settings['button_text'] );
		$text = '' !== $text ? $text : __( 'Book now', 'space2book' );

		return array(
			'venue'    => $venue,
			'mode'     => $mode,
			'language' => $this->adapter->resolve_language( $language ),
			'text'     => $text,
			'height'   => Settings::sanitize_height( isset( $args['height'] ) ? $args['height'] : 0, (int) $settings['inline_height'] ),
			'new_tab'  => (bool) $settings['new_tab'],
			'bg_color' => Settings::sanitize_color( $settings['button_bg_color'], Settings::defaults()['button_bg_color'] ),
			'color'    => Settings::sanitize_color( $settings['button_color'], Settings::defaults()['button_color'] ),
		);
	}

	/**
	 * Renders a booking element.
	 *
	 * @param array<string, mixed> $args See {@see Renderer::normalize()}.
	 * @return string HTML.
	 */
	public function render( array $args = array() ) {
		$options = $this->normalize( $args );

		if ( '' === $options['venue'] ) {
			return $this->render_notice( __( 'Space2Book: no venue reference configured. Set one in Settings → Space2Book or pass it to the block or shortcode.', 'space2book' ) );
		}

		$booking_url = $this->adapter->get_booking_url( $options['venue'], $options['language'] );

		if ( '' === $booking_url ) {
			return $this->render_notice( __( 'Space2Book: the booking URL could not be generated.', 'space2book' ) );
		}

		switch ( $options['mode'] ) {
			case 'inline':
				$html = $this->render_inline( $options, $booking_url );
				break;
			case 'link':
				$html = $this->render_link( $options, $booking_url );
				break;
			default:
				$html = $this->render_button( $options, $booking_url );
		}

		/**
		 * Filters the rendered Space2Book booking element.
		 *
		 * @param string $html    Rendered HTML.
		 * @param array  $options Normalized rendering options.
		 */
		return (string) apply_filters( 'space2book_render_html', $html, $options );
	}

	/**
	 * Renders the modal booking button.
	 *
	 * The button is an ordinary link to the booking page so it keeps working
	 * without JavaScript; the frontend script progressively enhances it into
	 * a button that opens the form in an accessible modal dialog.
	 *
	 * @param array<string, mixed> $options     Normalized options.
	 * @param string               $booking_url Booking page URL.
	 * @return string
	 */
	private function render_button( array $options, $booking_url ) {
		$this->adapter->enqueue_assets( true );

		return sprintf(
			'<a class="space2book space2book-button" href="%1$s" data-space2book-mode="modal" data-space2book-venue="%2$s" data-space2book-language="%3$s" data-space2book-embed-url="%4$s" style="%5$s">%6$s</a>',
			esc_url( $booking_url ),
			esc_attr( $options['venue'] ),
			esc_attr( $options['language'] ),
			esc_url( $this->adapter->get_embed_url( $options['venue'], $options['language'] ) ),
			esc_attr( sprintf( '--space2book-button-bg:%s;--space2book-button-color:%s', $options['bg_color'], $options['color'] ) ),
			esc_html( $options['text'] )
		);
	}

	/**
	 * Renders the inline embed.
	 *
	 * @param array<string, mixed> $options     Normalized options.
	 * @param string               $booking_url Booking page URL.
	 * @return string
	 */
	private function render_inline( array $options, $booking_url ) {
		$this->adapter->enqueue_assets( true );

		$attributes = array(
			'src'            => $this->adapter->get_embed_url( $options['venue'], $options['language'] ),
			'title'          => __( 'Space2Book booking form', 'space2book' ),
			'loading'        => 'lazy',
			'referrerpolicy' => 'strict-origin-when-cross-origin',
			'allow'          => 'payment',
			'style'          => sprintf( 'height:%dpx', $options['height'] ),
		);

		/**
		 * Filters the attributes of the inline booking iframe.
		 *
		 * @param array $attributes Iframe attributes.
		 * @param array $options    Normalized rendering options.
		 */
		$attributes = (array) apply_filters( 'space2book_iframe_attributes', $attributes, $options );

		$attribute_html = '';
		foreach ( $attributes as $name => $value ) {
			$name = preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $name ) );
			if ( '' === $name ) {
				continue;
			}
			$value           = 'src' === $name ? esc_url( (string) $value ) : esc_attr( (string) $value );
			$attribute_html .= sprintf( ' %s="%s"', $name, $value );
		}

		return sprintf(
			'<div class="space2book space2book-inline" data-space2book-mode="inline" data-space2book-venue="%1$s" data-space2book-language="%2$s"><iframe%3$s></iframe><p class="space2book-fallback"><a href="%4$s" target="_blank" rel="noopener">%5$s%6$s</a></p></div>',
			esc_attr( $options['venue'] ),
			esc_attr( $options['language'] ),
			$attribute_html,
			esc_url( $booking_url ),
			esc_html__( 'Open the booking form', 'space2book' ),
			$this->new_tab_hint()
		);
	}

	/**
	 * Renders a direct link to the public booking page.
	 *
	 * @param array<string, mixed> $options     Normalized options.
	 * @param string               $booking_url Booking page URL.
	 * @return string
	 */
	private function render_link( array $options, $booking_url ) {
		$this->adapter->enqueue_assets( false );

		return sprintf(
			'<a class="space2book space2book-link" href="%1$s"%2$s>%3$s</a>',
			esc_url( $booking_url ),
			$options['new_tab'] ? ' target="_blank" rel="noopener"' : '',
			esc_html( $options['text'] ) . ( $options['new_tab'] ? $this->new_tab_hint() : '' )
		);
	}

	/**
	 * Screen reader hint for links that open in a new tab.
	 *
	 * @return string
	 */
	private function new_tab_hint() {
		return sprintf( '<span class="space2book-sr-only"> %s</span>', esc_html__( '(opens in a new tab)', 'space2book' ) );
	}

	/**
	 * Renders a configuration notice, visible to editors only.
	 *
	 * @param string $message Message.
	 * @return string
	 */
	private function render_notice( $message ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return sprintf( '<p class="space2book-notice">%s</p>', esc_html( $message ) );
	}
}
