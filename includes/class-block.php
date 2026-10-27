<?php
/**
 * The Space2Book booking block.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the dynamic `space2book/booking` block.
 */
class Block {

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
	 * Hooks the block into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Registers the block type from its block.json metadata.
	 *
	 * @return void
	 */
	public function register_block() {
		$block = register_block_type(
			SPACE2BOOK_PLUGIN_DIR . 'blocks/booking',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);

		if ( $block && ! empty( $block->editor_script_handles ) ) {
			foreach ( $block->editor_script_handles as $handle ) {
				wp_set_script_translations( $handle, 'space2book', SPACE2BOOK_PLUGIN_DIR . 'languages' );
			}
		}
	}

	/**
	 * Server-side render callback.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public function render( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();

		$html = $this->renderer->render(
			array(
				'venue'    => isset( $attributes['venue'] ) ? $attributes['venue'] : '',
				'mode'     => isset( $attributes['mode'] ) ? $attributes['mode'] : '',
				'language' => isset( $attributes['language'] ) ? $attributes['language'] : '',
				'text'     => isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : '',
				'height'   => isset( $attributes['height'] ) ? $attributes['height'] : 0,
			)
		);

		if ( '' === $html ) {
			return '';
		}

		return sprintf( '<div %s>%s</div>', get_block_wrapper_attributes(), $html );
	}
}
