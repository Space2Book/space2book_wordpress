<?php
/**
 * Uninstall routine: removes all data stored by the plugin.
 *
 * @package Space2Book
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'space2book_settings' );
delete_option( 'space2book_version' );
