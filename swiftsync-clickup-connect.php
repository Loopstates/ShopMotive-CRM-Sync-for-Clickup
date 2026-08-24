<?php
/**
 * Plugin Name:       SwiftSync: Connect ClickUp with WooCommerce
 * Plugin URI:        https://docs.loopstates.com/clicksync/wordpress/
 * Description:       Automate your WooCommerce workflow by converting orders, refunds, customer updates, and abandoned checkouts directly into ClickUp tasks.
 * Version:           1.2.1
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Loopstates
 * Author URI:        https://loopstates.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       swiftsync-connect-clickup-with-woocommerce
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Plugin Constants
define( 'CLICKSYNC_VERSION', '1.2.1' );
define( 'CLICKSYNC_FILE', __FILE__ );
define( 'CLICKSYNC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLICKSYNC_URL', plugin_dir_url( __FILE__ ) );
define( 'CLICKSYNC_CLOUD_URL', 'https://clicksync-connect.apps.loopstates.com' );

// Require Autoloader with case-insensitive file system fallback check
$clicksync_autoloader = untrailingslashit( CLICKSYNC_PATH ) . '/src/Core/Autoloader.php';
if ( ! file_exists( $clicksync_autoloader ) ) {
	$clicksync_autoloader = strtolower( $clicksync_autoloader );
}
require_once $clicksync_autoloader;

// Register Autoloader
\ClickSync\Core\Autoloader::register();

// Activation Hook
register_activation_hook( __FILE__, array( \ClickSync\Core\Plugin::class, 'activate' ) );

// Deactivation Hook
register_deactivation_hook( __FILE__, array( \ClickSync\Core\Plugin::class, 'deactivate' ) );

// Initialize Plugin Instance
function clicksync_init() {
	\ClickSync\Core\Plugin::instance();
}
add_action( 'plugins_loaded', 'clicksync_init' );
