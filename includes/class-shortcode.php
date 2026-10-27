<?php
/**
 * The [space2book] shortcode.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the `[space2book]` shortcode.
 *
 * Usage:
 *
 *     [space2book]
 *     [space2book venue="my-venue" mode="button" language="nl" text="Reserveer nu"]
 *     [space2book mode="inline" height="800"]
 *     [space2book mode="link" text="View availability"]
 */
class Shortcode {

	/**
	 * Shortcode tag.
	 */
	const TAG = 'space2book';

	/**
	 * Renderer instance.
	 *
	 * @var Renderer
	 */
	private $renderer;

	/**
	 * Constructor.
	 *
	 * @param Renderer $renderer Renderer instance.
	 */
	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Hooks the shortcode into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'venue'    => '',
				'mode'     => '',
				'language' => '',
				'lang'     => '',
				'text'     => '',
				'height'   => '',
			),
			is_array( $atts ) ? $atts : array(),
			self::TAG
		);

		return $this->renderer->render(
			array(
				'venue'    => $atts['venue'],
				'mode'     => $atts['mode'],
				'language' => '' !== $atts['language'] ? $atts['language'] : $atts['lang'],
				'text'     => $atts['text'],
				'height'   => $atts['height'],
			)
		);
	}
}
