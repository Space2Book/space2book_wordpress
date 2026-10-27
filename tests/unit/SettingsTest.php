<?php
/**
 * @package Space2Book
 */

namespace Space2Book\Tests;

use Space2Book\Lifecycle;
use Space2Book\Settings;

require_once __DIR__ . '/TestCase.php';

class SettingsTest extends TestCase {

	public function test_all_falls_back_to_defaults() {
		$this->assertSame( Settings::defaults(), ( new Settings() )->all() );

		$this->set_settings( array( 'venue' => 'abc' ) );
		$this->assertSame( 'abc', ( new Settings() )->get( 'venue' ) );
		$this->assertSame( 'button', ( new Settings() )->get( 'mode' ) );
	}

	public function test_sanitize_accepts_valid_input() {
		$clean = Settings::sanitize(
			array(
				'venue'           => 'my-venue_01',
				'language'        => 'nl',
				'mode'            => 'inline',
				'inline_height'   => '900',
				'new_tab'         => '1',
				'button_text'     => ' Reserveer <b>nu</b> ',
				'button_bg_color' => '#AABBCC',
				'button_color'    => '#fff',
			)
		);

		$this->assertSame( 'my-venue_01', $clean['venue'] );
		$this->assertSame( 'nl', $clean['language'] );
		$this->assertSame( 'inline', $clean['mode'] );
		$this->assertSame( 900, $clean['inline_height'] );
		$this->assertTrue( $clean['new_tab'] );
		$this->assertSame( 'Reserveer nu', $clean['button_text'] );
		$this->assertSame( '#aabbcc', $clean['button_bg_color'] );
		$this->assertSame( '#fff', $clean['button_color'] );
	}

	public function test_sanitize_rejects_invalid_input() {
		$defaults = Settings::defaults();
		$clean    = Settings::sanitize(
			array(
				'venue'           => '../evil"><script>',
				'language'        => 'xx',
				'mode'            => 'popup',
				'inline_height'   => 'abc',
				'button_bg_color' => 'red;background:url(x)',
				'button_color'    => array( 'x' ),
			)
		);

		$this->assertSame( 'evilscript', $clean['venue'] );
		$this->assertSame( $defaults['language'], $clean['language'] );
		$this->assertSame( $defaults['mode'], $clean['mode'] );
		$this->assertSame( $defaults['inline_height'], $clean['inline_height'] );
		$this->assertFalse( $clean['new_tab'] );
		$this->assertSame( $defaults['button_bg_color'], $clean['button_bg_color'] );
		$this->assertSame( $defaults['button_color'], $clean['button_color'] );
		$this->assertSame( Settings::sanitize( 'not-an-array' )['mode'], $defaults['mode'] );
	}

	public function test_height_is_clamped() {
		$this->assertSame( Settings::MIN_HEIGHT, Settings::sanitize_height( 10, 700 ) );
		$this->assertSame( Settings::MAX_HEIGHT, Settings::sanitize_height( 99999, 700 ) );
		$this->assertSame( 700, Settings::sanitize_height( 0, 700 ) );
	}

	public function test_venue_is_limited_in_length() {
		$this->assertSame( 100, strlen( Settings::sanitize_venue( str_repeat( 'a', 150 ) ) ) );
		$this->assertSame( '', Settings::sanitize_venue( array( 'a' ) ) );
	}

	public function test_activation_stores_defaults_without_overwriting() {
		Lifecycle::activate();
		$this->assertSame( Settings::defaults(), get_option( Settings::OPTION ) );
		$this->assertSame( SPACE2BOOK_VERSION, get_option( Lifecycle::VERSION_OPTION ) );

		$this->set_settings( array( 'venue' => 'kept' ) );
		Lifecycle::activate();
		$this->assertSame( 'kept', get_option( Settings::OPTION )['venue'] );
	}
}
