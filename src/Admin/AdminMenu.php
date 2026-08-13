<?php

namespace ClickSync\Admin;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AdminMenu
 * 
 * Registers the 1:1 multi-page WP Admin navigation structure matching Shopify ClickSync.
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
	 * Register top-level ClickSync menu and 4 dedicated sub-pages matching Shopify menus.
	 */
	public static function register_menu() {
		// 1. Top-Level Parent Menu (Settings)
		add_menu_page(
			__( 'ClickSync: Wordpress to ClickUp CRM Sync', 'clicksync-wordpress' ),
			__( 'ClickSync', 'clicksync-wordpress' ),
			'manage_options',
			'clicksync',
			array( SettingsPage::class, 'render' ),
			'dashicons-update',
			56
		);

		// Submenu 1: Settings (Default tab)
		add_submenu_page(
			'clicksync',
			__( 'ClickSync Settings', 'clicksync-wordpress' ),
			__( 'Settings', 'clicksync-wordpress' ),
			'manage_options',
			'clicksync',
			array( SettingsPage::class, 'render' )
		);

		// Submenu 2: Sync Audit Logs
		add_submenu_page(
			'clicksync',
			__( 'Sync Audit Logs', 'clicksync-wordpress' ),
			__( 'Sync Audit Logs', 'clicksync-wordpress' ),
			'manage_options',
			'clicksync-logs',
			array( LogsPage::class, 'render' )
		);

		// Submenu 3: Sync Error Center
		add_submenu_page(
			'clicksync',
			__( 'Sync Error Center', 'clicksync-wordpress' ),
			__( 'Sync Error Center', 'clicksync-wordpress' ),
			'manage_options',
			'clicksync-errors',
			array( ErrorsPage::class, 'render' )
		);

		// Submenu 4: User Guide
		add_submenu_page(
			'clicksync',
			__( 'User Guide & Documentation', 'clicksync-wordpress' ),
			__( 'User Guide', 'clicksync-wordpress' ),
			'manage_options',
			'clicksync-guide',
			array( GuidePage::class, 'render' )
		);
	}

	/**
	 * Enqueue assets on ClickSync admin pages only.
	 */
	public static function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'clicksync' ) === false ) {
			return;
		}

		wp_enqueue_style( 'clicksync-admin-css', CLICKSYNC_URL . 'assets/css/admin.css', array(), CLICKSYNC_VERSION );
		wp_enqueue_script( 'clicksync-admin-js', CLICKSYNC_URL . 'assets/js/admin.js', array( 'jquery' ), CLICKSYNC_VERSION, true );

		wp_localize_script( 'clicksync-admin-js', 'clicksyncData', array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'cloudUrl' => CLICKSYNC_CLOUD_URL,
			'host'     => parse_url( site_url(), PHP_URL_HOST ),
			'nonce'    => wp_create_nonce( 'clicksync_admin_nonce' ),
		) );
	}

	/**
	 * Render quota warning notice if free quota is exhausted.
	 */
	public static function render_quota_notice() {
		$account    = Options::get_account();
		$sync_count = (int) ( $account['monthly_sync_count'] ?? 0 );
		$quota      = (int) ( $account['monthly_quota'] ?? 100 );

		if ( $quota > 0 && $sync_count >= $quota ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'ClickSync Limit Reached:', 'clicksync-wordpress' ); ?></strong>
					<?php printf( esc_html__( 'You have used %1$d of your %2$d monthly sync tasks. Upgrade your plan to keep syncing WooCommerce events without interruption.', 'clicksync-wordpress' ), $sync_count, $quota ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=clicksync#billing-section' ) ); ?>" class="button button-small button-primary" style="margin-left: 10px;">
						<?php esc_html_e( 'Upgrade Plan →', 'clicksync-wordpress' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}
}
