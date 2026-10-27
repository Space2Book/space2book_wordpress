<?php
/**
 * @package Space2Book
 */

namespace Space2Book\Tests;

require_once __DIR__ . '/TestCase.php';

class RendererTest extends TestCase {

	public function test_missing_venue_is_hidden_from_visitors() {
		$this->assertSame( '', $this->renderer()->render() );
	}

	public function test_missing_venue_shows_notice_to_editors() {
		\WP_Stubs::$can_edit = true;
		$this->assertStringContainsString( 'space2book-notice', $this->renderer()->render() );
	}

	public function test_renders_modal_button_with_defaults() {
		$this->set_settings( array( 'venue' => 'my-venue', 'language' => 'de' ) );

		$html = $this->renderer()->render();

		$this->assertStringContainsString( 'class="space2book space2book-button"', $html );
		$this->assertStringContainsString( 'href="https://app.space2book.com/book/my-venue?lang=de"', $html );
		$this->assertStringContainsString( 'data-space2book-mode="modal"', $html );
		$this->assertStringContainsString( 'data-space2book-language="de"', $html );
		$this->assertStringContainsString( '--space2book-button-bg:#1d4ed8', $html );
		$this->assertStringContainsString( '>Book now</a>', $html );
		$this->assertContains( 'space2book', \WP_Stubs::$scripts );
		$this->assertContains( 'space2book', \WP_Stubs::$styles );
	}

	public function test_arguments_override_settings_and_are_escaped() {
		$this->set_settings( array( 'venue' => 'default-venue', 'button_text' => 'Settings text' ) );

		$html = $this->renderer()->render(
			array(
				'venue'    => 'other',
				'language' => 'es',
				'text'     => '<script>alert(1)</script>Reservar "ya"',
			)
		);

		$this->assertStringContainsString( 'book/other?lang=es', $html );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( 'Reservar &quot;ya&quot;', $html );
	}

	public function test_button_text_falls_back_to_setting() {
		$this->set_settings( array( 'venue' => 'v', 'button_text' => 'Reserveer' ) );
		$this->assertStringContainsString( '>Reserveer</a>', $this->renderer()->render() );
	}

	public function test_renders_inline_iframe() {
		$this->set_settings( array( 'venue' => 'v' ) );

		$html = $this->renderer()->render( array( 'mode' => 'inline', 'height' => 850, 'language' => 'en' ) );

		$this->assertStringContainsString( 'space2book-inline', $html );
		$this->assertStringContainsString( '<iframe src="https://app.space2book.com/book/v?lang=en"', $html );
		$this->assertStringContainsString( 'style="height:850px"', $html );
		$this->assertStringContainsString( 'title="Space2Book booking form"', $html );
		$this->assertStringContainsString( 'referrerpolicy="strict-origin-when-cross-origin"', $html );
	}

	public function test_renders_direct_link() {
		$this->set_settings( array( 'venue' => 'v', 'new_tab' => true ) );

		$html = $this->renderer()->render( array( 'mode' => 'link', 'language' => 'en' ) );

		$this->assertStringContainsString( '<a class="space2book space2book-link" href="https://app.space2book.com/book/v?lang=en" target="_blank" rel="noopener">', $html );
		$this->assertStringContainsString( 'space2book-sr-only', $html );
		$this->assertSame( array(), \WP_Stubs::$scripts, 'Direct links do not need JavaScript.' );

		$this->set_settings( array( 'venue' => 'v', 'new_tab' => false ) );
		$this->assertStringNotContainsString( 'target="_blank"', $this->renderer()->render( array( 'mode' => 'link' ) ) );
	}

	public function test_invalid_mode_falls_back_to_setting() {
		$this->set_settings( array( 'venue' => 'v', 'mode' => 'link' ) );
		$this->assertStringContainsString( 'space2book-link', $this->renderer()->render( array( 'mode' => 'popup' ) ) );
	}

	public function test_multiple_instances_are_independent() {
		$this->set_settings( array( 'venue' => 'v' ) );
		$renderer = $this->renderer();

		$first  = $renderer->render( array( 'venue' => 'room-a', 'language' => 'nl' ) );
		$second = $renderer->render( array( 'venue' => 'room-b', 'language' => 'fr' ) );

		$this->assertStringContainsString( 'data-space2book-venue="room-a"', $first );
		$this->assertStringContainsString( 'data-space2book-venue="room-b"', $second );
		$this->assertStringContainsString( 'lang=fr', $second );
	}
}
