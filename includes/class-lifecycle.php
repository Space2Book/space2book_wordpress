<?php
/**
 * Plugin lifecycle (activation / deactivation) handling.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Activation and deactivation callbacks.
 */
final class Lifecycle {

	/**
	 * Option that stores the installed plugin version.
	 */
	const VERSION_OPTION = 'space2book_version';

	/**
	 * Runs on plugin activation.
	 *
	 * Stores the default settings (without overwriting existing ones) and the
	 * installed version so future releases can run upgrade routines.
	 *
	 * @return void
	 */
	public static function activate() {
		add_option( Settings::OPTION, Settings::defaults() );
		update_option( self::VERSION_OPTION, SPACE2BOOK_VERSION, false );
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * Settings are intentionally kept so they survive a deactivate/activate
	 * cycle. They are removed in uninstall.php.
	 *
	 * @return void
	 */
	public static function deactivate() {
		/**
		 * Fires when the Space2Book plugin is deactivated.
		 */
		do_action( 'space2book_deactivated' );
	}
}
