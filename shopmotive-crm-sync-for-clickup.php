<?php
/**
 * Plugin Name:       ShopMotive: CRM Sync for ClickUp
 * Plugin URI:        https://docs.loopstates.com/shopmotive-for-clickup-and-woocommerce/
 * Description:       Connect and synchronize WooCommerce order events, customer profiles, notes, and refunds directly into ClickUp tasks.
 * Version:           1.2.1
 * Tested up to:      7.1
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Loopstates
 * Author URI:        https://loopstates.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shopmotive-crm-sync-for-clickup
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Plugin Constants
define( 'SHOPMOTIVE_VERSION', '1.2.1' );
define( 'SHOPMOTIVE_FILE', __FILE__ );
define( 'SHOPMOTIVE_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHOPMOTIVE_URL', plugin_dir_url( __FILE__ ) );
define( 'SHOPMOTIVE_CLOUD_URL', 'https://shopmotive.apps.loopstates.com' );

if ( ! defined( 'SHOPMOTIVE_VERSION' ) ) {
	define( 'SHOPMOTIVE_VERSION', SHOPMOTIVE_VERSION );
	define( 'SHOPMOTIVE_FILE', SHOPMOTIVE_FILE );
	define( 'SHOPMOTIVE_PATH', SHOPMOTIVE_PATH );
	define( 'SHOPMOTIVE_URL', SHOPMOTIVE_URL );
	define( 'SHOPMOTIVE_CLOUD_URL', SHOPMOTIVE_CLOUD_URL );
}

// Require Autoloader with case-insensitive file system fallback check
$shopmotive_autoloader = untrailingslashit( SHOPMOTIVE_PATH ) . '/src/Core/Autoloader.php';
if ( ! file_exists( $shopmotive_autoloader ) ) {
	$shopmotive_autoloader = strtolower( $shopmotive_autoloader );
}
require_once $shopmotive_autoloader;

// Register Autoloader
\ShopMotive\Core\Autoloader::register();

// Activation Hook
register_activation_hook( __FILE__, array( \ShopMotive\Core\Plugin::class, 'activate' ) );

// Deactivation Hook
register_deactivation_hook( __FILE__, array( \ShopMotive\Core\Plugin::class, 'deactivate' ) );

// Initialize Plugin Instance
function shopmotive_init() {
	\ShopMotive\Core\Plugin::instance();
}
add_action( 'plugins_loaded', 'shopmotive_init' );
