<?php
/**
 * Main plugin class: wires all components together.
 *
 * @package Space2Book
 */

namespace Space2Book;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin bootstrapper.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

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
	 * Renderer instance.
	 *
	 * @var Renderer
	 */
	private $renderer;

	/**
	 * Boots the plugin once. Hooked to `plugins_loaded`.
	 *
	 * @return Plugin
	 */
	public static function boot() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->register();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->settings = new Settings();
		$this->adapter  = new Sdk_Adapter( $this->settings );
		$this->renderer = new Renderer( $this->settings, $this->adapter );
	}

	/**
	 * Registers all hooks.
	 *
	 * @return void
	 */
	private function register() {
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		$this->adapter->register();
		( new Shortcode( $this->renderer ) )->register();
		( new Block( $this->renderer ) )->register();

		if ( is_admin() ) {
			( new Admin( $this->settings ) )->register();
		}

		/**
		 * Fires after the Space2Book plugin has been loaded.
		 *
		 * @param Plugin $plugin Plugin instance.
		 */
		do_action( 'space2book_loaded', $this );
	}

	/**
	 * Loads the bundled translations. Translations from translate.wordpress.org
	 * take precedence when available.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'space2book', false, dirname( plugin_basename( SPACE2BOOK_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Returns the renderer, e.g. for use in themes:
	 * `echo \Space2Book\Plugin::boot()->renderer()->render( array( 'mode' => 'link' ) );`
	 *
	 * @return Renderer
	 */
	public function renderer() {
		return $this->renderer;
	}

	/**
	 * Returns the SDK adapter.
	 *
	 * @return Sdk_Adapter
	 */
	public function adapter() {
		return $this->adapter;
	}

	/**
	 * Returns the settings.
	 *
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}
}
