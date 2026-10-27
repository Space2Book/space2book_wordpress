<?php
/**
 * Plugin Name:       Space2Book
 * Plugin URI:        https://github.com/Space2Book/space2book_wordpress
 * Description:       Embed the public Space2Book booking form on your WordPress site as a modal booking button, an inline form or a direct link.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Space2Book
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       space2book
 * Domain Path:       /languages
 *
 * @package Space2Book
 */

defined( 'ABSPATH' ) || exit;

define( 'SPACE2BOOK_VERSION', '0.1.0' );
define( 'SPACE2BOOK_PLUGIN_FILE', __FILE__ );
define( 'SPACE2BOOK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPACE2BOOK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SPACE2BOOK_PLUGIN_DIR . 'includes/autoload.php';

register_activation_hook( __FILE__, array( \Space2Book\Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \Space2Book\Lifecycle::class, 'deactivate' ) );

add_action( 'plugins_loaded', array( \Space2Book\Plugin::class, 'boot' ) );
