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
		add_action( 'admin_init', array( __CLASS__, 'check_secret_key_redirect' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_quota_notice' ) );
		add_action( 'wp_ajax_clicksync_get_wc_fields', array( __CLASS__, 'ajax_get_wc_fields' ) );
	}

	/**
	 * Save secret key and clean URL parameters.
	 */
	public static function check_secret_key_redirect() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'clicksync' && isset( $_GET['clicksync_secret_key'] ) ) {
			$secret_key = sanitize_text_field( $_GET['clicksync_secret_key'] );
			Options::update_secret_key( $secret_key );
			
			// Clean redirect URL query parameter
			wp_safe_redirect( admin_url( 'admin.php?page=clicksync' ) );
			exit;
		}
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

		wp_enqueue_style( 'clicksync-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap' );
		wp_enqueue_style( 'clicksync-admin-css', CLICKSYNC_URL . 'assets/css/admin.css', array(), time() );
		wp_enqueue_script( 'clicksync-admin-js', CLICKSYNC_URL . 'assets/js/admin.js', array( 'jquery' ), time(), true );

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
						<?php esc_html_e( 'Upgrade Plan ->', 'clicksync-wordpress' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * AJAX endpoint to retrieve dynamic WooCommerce fields, meta keys, and order statuses.
	 */
	public static function ajax_get_wc_fields() {
		check_ajax_referer( 'clicksync_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		global $wpdb;

		// 1. Get WooCommerce Order Statuses dynamically
		$statuses = array();
		if ( function_exists( 'wc_get_order_statuses' ) ) {
			foreach ( wc_get_order_statuses() as $slug => $label ) {
				$statuses[] = array(
					'slug'  => str_replace( 'wc-', '', $slug ),
					'label' => $label,
				);
			}
		}

		// 2. Get distinct non-underscore Order meta keys from recent orders
		$order_meta = array();
		$order_meta_results = $wpdb->get_col( "
			SELECT DISTINCT meta_key 
			FROM {$wpdb->postmeta} 
			WHERE post_id IN (
				SELECT ID FROM {$wpdb->posts} 
				WHERE post_type = 'shop_order' 
				ORDER BY ID DESC LIMIT 100
			) 
			AND meta_key NOT LIKE '\_%'
		" );
		if ( ! empty( $order_meta_results ) ) {
			foreach ( $order_meta_results as $key ) {
				$order_meta[] = array(
					'key'   => 'meta.' . $key,
					'label' => 'Meta: ' . $key . ' (meta.' . $key . ')',
				);
			}
		}

		// 3. Get distinct non-underscore Customer user meta keys from recent users
		$customer_meta = array();
		$customer_meta_results = $wpdb->get_col( "
			SELECT DISTINCT meta_key 
			FROM {$wpdb->usermeta} 
			WHERE user_id IN (
				SELECT ID FROM {$wpdb->users} 
				ORDER BY ID DESC LIMIT 100
			) 
			AND meta_key NOT LIKE '\_%'
		" );
		if ( ! empty( $customer_meta_results ) ) {
			foreach ( $customer_meta_results as $key ) {
				$customer_meta[] = array(
					'key'   => 'meta.' . $key,
					'label' => 'Meta: ' . $key . ' (meta.' . $key . ')',
				);
			}
		}

		$data = array(
			'orders' => array(
				'default_fields' => array(
					array( 'key' => 'id', 'label' => 'WooCommerce Order ID (id)' ),
					array( 'key' => 'name', 'label' => 'Order Name (name)' ),
					array( 'key' => 'order_number', 'label' => 'Order Number (order_number)' ),
					array( 'key' => 'email', 'label' => 'Customer Email (email)' ),
					array( 'key' => 'total_price', 'label' => 'Total Price (total_price)' ),
					array( 'key' => 'subtotal_price', 'label' => 'Subtotal Price (subtotal_price)' ),
					array( 'key' => 'total_tax', 'label' => 'Total Tax (total_tax)' ),
					array( 'key' => 'currency', 'label' => 'Currency (currency)' ),
					array( 'key' => 'financial_status', 'label' => 'Financial Status (financial_status)' ),
					array( 'key' => 'fulfillment_status', 'label' => 'Fulfillment Status (fulfillment_status)' ),
					array( 'key' => 'note', 'label' => 'Customer Note (note)' ),
					array( 'key' => 'created_at', 'label' => 'Created Date (created_at)' ),
					array( 'key' => 'updated_at', 'label' => 'Updated Date (updated_at)' ),
					array( 'key' => 'billing_address.first_name', 'label' => 'Billing First Name (billing_address.first_name)' ),
					array( 'key' => 'billing_address.last_name', 'label' => 'Billing Last Name (billing_address.last_name)' ),
					array( 'key' => 'billing_address.company', 'label' => 'Billing Company (billing_address.company)' ),
					array( 'key' => 'billing_address.address1', 'label' => 'Billing Address 1 (billing_address.address1)' ),
					array( 'key' => 'billing_address.address2', 'label' => 'Billing Address 2 (billing_address.address2)' ),
					array( 'key' => 'billing_address.city', 'label' => 'Billing City (billing_address.city)' ),
					array( 'key' => 'billing_address.province', 'label' => 'Billing State (billing_address.province)' ),
					array( 'key' => 'billing_address.country', 'label' => 'Billing Country (billing_address.country)' ),
					array( 'key' => 'billing_address.zip', 'label' => 'Billing Zip (billing_address.zip)' ),
					array( 'key' => 'billing_address.phone', 'label' => 'Billing Phone (billing_address.phone)' ),
					array( 'key' => 'shipping_address.first_name', 'label' => 'Shipping First Name (shipping_address.first_name)' ),
					array( 'key' => 'shipping_address.last_name', 'label' => 'Shipping Last Name (shipping_address.last_name)' ),
					array( 'key' => 'shipping_address.company', 'label' => 'Shipping Company (shipping_address.company)' ),
					array( 'key' => 'shipping_address.address1', 'label' => 'Shipping Address 1 (shipping_address.address1)' ),
					array( 'key' => 'shipping_address.address2', 'label' => 'Shipping Address 2 (shipping_address.address2)' ),
					array( 'key' => 'shipping_address.city', 'label' => 'Shipping City (shipping_address.city)' ),
					array( 'key' => 'shipping_address.province', 'label' => 'Shipping State (shipping_address.province)' ),
					array( 'key' => 'shipping_address.country', 'label' => 'Shipping Country (shipping_address.country)' ),
					array( 'key' => 'shipping_address.zip', 'label' => 'Shipping Zip (shipping_address.zip)' ),
				),
				'meta_fields'    => $order_meta,
				'order_statuses' => $statuses,
			),
			'customers' => array(
				'default_fields' => array(
					array( 'key' => 'id', 'label' => 'Customer User ID (id)' ),
					array( 'key' => 'email', 'label' => 'Customer Email (email)' ),
					array( 'key' => 'first_name', 'label' => 'First Name (first_name)' ),
					array( 'key' => 'last_name', 'label' => 'Last Name (last_name)' ),
					array( 'key' => 'phone', 'label' => 'Customer Phone (phone)' ),
					array( 'key' => 'orders_count', 'label' => 'Total Orders Count (orders_count)' ),
					array( 'key' => 'total_spent', 'label' => 'Total Amount Spent (total_spent)' ),
					array( 'key' => 'created_at', 'label' => 'Registered Date (created_at)' ),
				),
				'meta_fields'    => $customer_meta,
			),
		);

		wp_send_json_success( $data );
	}
}
