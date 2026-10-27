<?php
/**
 * @package Space2Book
 */

namespace Space2Book\Tests;

use Space2Book\Sdk_Adapter;
use Space2Book\Settings;

require_once __DIR__ . '/TestCase.php';

class SdkAdapterTest extends TestCase {

	private function adapter() {
		return new Sdk_Adapter( new Settings() );
	}

	public function test_builds_booking_url_from_template() {
		$this->assertSame(
			'https://app.space2book.com/book/my-venue?lang=nl',
			$this->adapter()->get_booking_url( 'my-venue', 'nl' )
		);
	}

	public function test_empty_or_invalid_venue_gives_empty_url() {
		$this->assertSame( '', $this->adapter()->get_booking_url( '', 'en' ) );
		$this->assertSame( '', $this->adapter()->get_booking_url( '../', 'en' ) );
	}

	public function test_template_and_urls_are_filterable() {
		add_filter(
			'space2book_booking_url_template',
			function () {
				return 'https://booking.example.com/{language}/{venue}';
			}
		);
		$this->assertSame( 'https://booking.example.com/de/abc', $this->adapter()->get_booking_url( 'abc', 'de' ) );

		add_filter(
			'space2book_embed_url',
			function ( $url ) {
				return $url . '?embed=1';
			}
		);
		$this->assertSame( 'https://booking.example.com/de/abc?embed=1', $this->adapter()->get_embed_url( 'abc', 'de' ) );
	}

	public function test_unsafe_url_schemes_are_rejected() {
		add_filter(
			'space2book_booking_url',
			function () {
				return 'javascript:alert(1)';
			}
		);
		$this->assertSame( '', $this->adapter()->get_booking_url( 'abc', 'en' ) );
	}

	public function test_language_resolution() {
		$adapter = $this->adapter();
		$this->assertSame( 'fr', $adapter->resolve_language( 'fr' ) );

		\WP_Stubs::$locale = 'nl_NL';
		$this->assertSame( 'nl', $adapter->resolve_language( 'auto' ) );
		$this->assertSame( 'nl', $adapter->resolve_language( '' ) );

		\WP_Stubs::$locale = 'it_IT';
		$this->assertSame( 'en', $adapter->resolve_language( 'auto' ) );
	}

	public function test_sdk_script_is_optional_and_https_only() {
		$this->assertSame( '', $this->adapter()->get_sdk_script_url() );

		add_filter(
			'space2book_sdk_script_url',
			function () {
				return 'http://insecure.example.com/sdk.js';
			}
		);
		$this->assertSame( '', $this->adapter()->get_sdk_script_url() );
	}

	public function test_script_config_contains_translatable_strings() {
		$config = $this->adapter()->get_script_config();
		$this->assertArrayHasKey( 'close', $config['i18n'] );
		$this->assertArrayHasKey( 'dialogLabel', $config['i18n'] );
	}
}
