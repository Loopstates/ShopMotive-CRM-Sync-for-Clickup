<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AdminMenu
 * 
 * Registers WooCommerce sidebar admin menu and enqueues admin styling & scripts.
 */
class AdminMenu {

	/**
	 * Register admin menu and hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_quota_notice' ) );
	}

	/**
	 * Register ClickSync as a prominent top-level main sidebar menu item.
	 */
	public static function register_menu() {
		// Register top-level main sidebar menu
		add_menu_page(
			__( 'ClickSync: Wordpress to ClickUp CRM Sync', 'clicksync-wordpress' ),
			__( 'ClickSync', 'clicksync-wordpress' ),
			'manage_options',
			'clicksync',
			array( __CLASS__, 'render_settings_page' ),
			'dashicons-update',
			56 // Position right below WooCommerce / Products
		);
	}

	/**
	 * Enqueue Polaris CSS and admin JavaScript assets on ClickSync page.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'clicksync' ) ) {
			return;
		}

		wp_enqueue_style(
			'clicksync-admin-css',
			CLICKSYNC_URL . 'assets/css/admin.css',
			array(),
			CLICKSYNC_VERSION
		);

		wp_enqueue_script(
			'clicksync-admin-js',
			CLICKSYNC_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			CLICKSYNC_VERSION,
			true
		);

		wp_localize_script(
			'clicksync-admin-js',
			'clicksync_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'clicksync_admin_nonce' ),
			)
		);
	}

	/**
	 * Render the main settings page.
	 */
	public static function render_settings_page() {
		SettingsPage::render();
	}

	/**
	 * Render contextual, dismissible admin notice when quota limit (100/100) is reached.
	 */
	public static function render_quota_notice() {
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, 'clicksync' ) ) {
			return;
		}

		$account = Options::get_account();
		$count   = (int) ( $account['monthly_sync_count'] ?? 0 );
		$quota   = (int) ( $account['monthly_quota'] ?? 100 );
		$plan    = $account['plan_name'] ?? 'Free Plan';

		if ( 'Free Plan' === $plan && $count >= $quota ) {
			?>
			<div class="notice notice-warning is-dismissible" style="border-left-color: #7c3aed; padding: 12px 16px;">
				<p style="font-size: 14px; margin: 0; line-height: 1.5;">
					<strong><?php esc_html_e( 'ClickSync Limit Notice:', 'clicksync-wordpress' ); ?></strong> 
					<?php esc_html_e( 'You have reached your free plan limit of 100 monthly sync runs.', 'clicksync-wordpress' ); ?>
					<a href="<?php echo esc_url( CLICKSYNC_CLOUD_URL . '/account/billing?shop=' . parse_url( site_url(), PHP_URL_HOST ) ); ?>" target="_blank" style="font-weight: bold; color: #7c3aed; text-decoration: underline; margin-left: 8px;">
						<?php esc_html_e( 'Upgrade to Growth or Pro Plan →', 'clicksync-wordpress' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}
}
