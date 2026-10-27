<?php
/**
 * Base test case.
 *
 * @package Space2Book
 */

namespace Space2Book\Tests;

use Space2Book\Renderer;
use Space2Book\Sdk_Adapter;
use Space2Book\Settings;

abstract class TestCase extends \PHPUnit\Framework\TestCase {

	protected function setUp(): void {
		parent::setUp();
		\WP_Stubs::reset();
	}

	/**
	 * @param array<string, mixed> $settings Stored settings.
	 */
	protected function set_settings( array $settings ) {
		\WP_Stubs::$options[ Settings::OPTION ] = array_merge( Settings::defaults(), $settings );
	}

	protected function renderer() {
		$settings = new Settings();
		return new Renderer( $settings, new Sdk_Adapter( $settings ) );
	}
}
