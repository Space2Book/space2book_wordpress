<?php
/**
 * Tests for the shortcode and block entry points.
 *
 * @package Space2Book
 */

namespace Space2Book\Tests;

use Space2Book\Block;
use Space2Book\Shortcode;

require_once __DIR__ . '/TestCase.php';

class IntegrationTest extends TestCase {

	public function test_shortcode_is_registered_and_passes_attributes() {
		$this->set_settings( array( 'venue' => 'v' ) );
		( new Shortcode( $this->renderer() ) )->register();

		$this->assertArrayHasKey( 'space2book', \WP_Stubs::$shortcodes );

		$html = call_user_func(
			\WP_Stubs::$shortcodes['space2book'],
			array(
				'venue' => 'hall',
				'mode'  => 'link',
				'lang'  => 'nl',
				'text'  => 'Reserveer',
			)
		);

		$this->assertStringContainsString( 'space2book-link', $html );
		$this->assertStringContainsString( 'book/hall?lang=nl', $html );
		$this->assertStringContainsString( '>Reserveer', $html );
	}

	public function test_shortcode_without_attributes_uses_settings() {
		$this->set_settings( array( 'venue' => 'v', 'language' => 'en' ) );
		$html = ( new Shortcode( $this->renderer() ) )->render( '' );

		$this->assertStringContainsString( 'space2book-button', $html );
		$this->assertStringContainsString( 'book/v?lang=en', $html );
	}

	public function test_block_render_wraps_output() {
		$this->set_settings( array( 'venue' => 'v', 'language' => 'en' ) );
		$html = ( new Block( $this->renderer() ) )->render(
			array(
				'mode'       => 'button',
				'buttonText' => 'Book a room',
			)
		);

		$this->assertStringStartsWith( '<div class="wp-block-space2book-booking">', $html );
		$this->assertStringContainsString( '>Book a room</a>', $html );
	}

	public function test_block_render_is_empty_without_venue_for_visitors() {
		$this->assertSame( '', ( new Block( $this->renderer() ) )->render( array() ) );
	}
}
