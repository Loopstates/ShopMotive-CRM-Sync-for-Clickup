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
		add_action( 'wp_ajax_clicksync_save_local_settings', array( __CLASS__, 'ajax_save_local_settings' ) );
		add_action( 'wp_ajax_clicksync_save_user_mappings', array( __CLASS__, 'ajax_save_user_mappings' ) );
		add_action( 'wp_ajax_clicksync_widget_get_task_details', array( __CLASS__, 'ajax_widget_get_task_details' ) );
		add_action( 'wp_ajax_clicksync_widget_update_task', array( __CLASS__, 'ajax_widget_update_task' ) );
		add_action( 'wp_ajax_clicksync_widget_force_sync', array( __CLASS__, 'ajax_widget_force_sync' ) );
		add_action( 'wp_ajax_clicksync_contact_submit', array( __CLASS__, 'ajax_contact_submit' ) );

		// Register Sidebar Widgets (Meta Boxes)
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
		add_action( 'show_user_profile', array( __CLASS__, 'register_customer_metabox' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'register_customer_metabox' ) );
	}

	/**
	 * Save secret key and clean URL parameters.
	 */
	public static function check_secret_key_redirect() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'clicksync' && isset( $_GET['clicksync_secret_key'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$secret_key = sanitize_text_field( $_GET['clicksync_secret_key'] );
			Options::update_secret_key( $secret_key );
			
			// Clean redirect URL query parameter but preserve clicksync_action if set
			$redirect_url = admin_url( 'admin.php?page=clicksync' );
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['clicksync_action'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$redirect_url = add_query_arg( 'clicksync_action', sanitize_text_field( $_GET['clicksync_action'] ), $redirect_url );
			}
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Register top-level ClickSync menu and 4 dedicated sub-pages matching Shopify menus.
	 */
	public static function register_menu() {
		// 1. Top-Level Parent Menu (Settings)
		add_menu_page(
			__( 'ClickSync Connect: WooCommerce to ClickUp CRM Sync', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			__( 'ClickSync', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			'manage_options',
			'clicksync',
			array( SettingsPage::class, 'render' ),
			'dashicons-update',
			56
		);

		// Submenu 1: Settings (Default tab)
		add_submenu_page(
			'clicksync',
			__( 'ClickSync Settings', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			__( 'Settings', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			'manage_options',
			'clicksync',
			array( SettingsPage::class, 'render' )
		);

		// Submenu 2: Sync Logs
		add_submenu_page(
			'clicksync',
			__( 'Sync Logs', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			__( 'Sync Logs', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			'manage_options',
			'clicksync-logs',
			array( LogsPage::class, 'render' )
		);

		// Submenu 3: Sync Error Center
		add_submenu_page(
			'clicksync',
			__( 'Sync Error Center', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			__( 'Sync Error Center', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			'manage_options',
			'clicksync-errors',
			array( ErrorsPage::class, 'render' )
		);

		// Submenu 4: Help Center
		add_submenu_page(
			'clicksync',
			__( 'Help & Documentation', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			__( 'Help Center', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			'manage_options',
			'clicksync-help',
			array( HelpPage::class, 'render' )
		);
	}

	/**
	 * Enqueue assets on ClickSync admin pages only.
	 */
	public static function enqueue_assets( $hook ) {
		$allowed_pages = array( 'post.php', 'post-new.php', 'user-edit.php', 'profile.php', 'woocommerce_page_wc-orders' );
		$is_allowed = false;
		if ( strpos( $hook, 'clicksync' ) !== false ) {
			$is_allowed = true;
		} elseif ( in_array( $hook, $allowed_pages ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['post'] ) && get_post_type( absint( $_GET['post'] ) ) === 'shop_order' ) {
				$is_allowed = true;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-orders' ) {
				$is_allowed = true;
			} else {
				$screen = get_current_screen();
				if ( $screen && ( $screen->id === 'user-edit' || $screen->id === 'profile' || $screen->id === 'shop_order' ) ) {
					$is_allowed = true;
				}
			}
		}

		if ( ! $is_allowed ) {
			return;
		}

		wp_enqueue_style( 'clicksync-admin-css', CLICKSYNC_URL . 'assets/css/admin.css', array(), time() );
		wp_enqueue_script( 'clicksync-admin-js-v2', CLICKSYNC_URL . 'assets/js/admin.js', array( 'jquery' ), time(), true );

		wp_localize_script( 'clicksync-admin-js-v2', 'clicksyncData', array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php', 'relative' ),
			'cloudUrl' => CLICKSYNC_CLOUD_URL,
			'host'     => wp_parse_url( site_url(), PHP_URL_HOST ),
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
					<strong><?php esc_html_e( 'ClickSync Limit Reached:', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ); ?></strong>
					<?php
					// translators: 1: sync count, 2: total quota
					printf( esc_html__( 'You have used %1$d of your %2$d monthly sync tasks. Upgrade your plan to keep syncing WooCommerce events without interruption.', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ), esc_html( $sync_count ), esc_html( $quota ) );
					?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=clicksync#billing-section' ) ); ?>" class="button button-small button-primary" style="margin-left: 10px;">
						<?php esc_html_e( 'Upgrade Plan ->', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * AJAX endpoint to save local WordPress plugin settings (e.g. Refunds, Fulfillment).
	 */
	public static function ajax_save_local_settings() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$refunds_enabled = isset( $_POST['refunds_enabled'] ) ? '1' === $_POST['refunds_enabled'] : true;
		Options::update_settings( array(
			'refunds_enabled'     => $refunds_enabled,
			'fulfillment_enabled' => true,
		) );

		wp_send_json_success( array( 'message' => 'Local settings updated successfully.' ) );
	}

	/**
	 * AJAX endpoint to save user identity mappings (WP to ClickUp).
	 */
	public static function ajax_save_user_mappings() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$mappings = isset( $_POST['mappings'] ) ? (array) $_POST['mappings'] : array();
		$fallback = isset( $_POST['fallback_clickup_user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['fallback_clickup_user_id'] ) ) : '';

		// Clean keys and values
		$cleaned_mappings = array();
		foreach ( $mappings as $wp_id => $cu_id ) {
			$cleaned_mappings[ intval( $wp_id ) ] = sanitize_text_field( $cu_id );
		}

		Options::update_user_mappings( array(
			'mappings'                 => $cleaned_mappings,
			'fallback_clickup_user_id' => $fallback,
		) );

		wp_send_json_success( array( 'message' => 'User identity mappings saved successfully.' ) );
	}

	/**
	 * AJAX endpoint to retrieve dynamic WooCommerce fields, meta keys, and order statuses.
	 */
	public static function ajax_get_wc_fields() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		global $wpdb;
		$wpdb->hide_errors();

		// Clear any output buffer to prevent PHP warnings from corrupting the JSON payload
		if ( ob_get_length() ) {
			ob_clean();
		}

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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$order_meta_results = $wpdb->get_col( $wpdb->prepare( "
			SELECT DISTINCT meta_key 
			FROM {$wpdb->postmeta} 
			WHERE post_id IN (
				SELECT ID FROM {$wpdb->posts} 
				WHERE post_type = %s 
				ORDER BY ID DESC LIMIT 100
			) 
			AND meta_key NOT LIKE %s
		", 'shop_order', '\_%' ) );
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$customer_meta_results = $wpdb->get_col( $wpdb->prepare( "
			SELECT DISTINCT meta_key 
			FROM {$wpdb->usermeta} 
			WHERE user_id IN (
				SELECT ID FROM {$wpdb->users} 
				ORDER BY ID DESC LIMIT 100
			) 
			AND meta_key NOT LIKE %s
		", '\_%' ) );
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

	/**
	 * Register meta box for WooCommerce Edit Order screen.
	 */
	public static function register_metaboxes() {
		// Legacy orders page
		add_meta_box(
			'clicksync_order_details_metabox',
			__( 'ClickUp Task Sync', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			array( \ClickSync\Admin\Widget::class, 'render_order_metabox' ),
			'shop_order',
			'side',
			'high'
		);
		// HPOS support
		add_meta_box(
			'clicksync_order_details_metabox',
			__( 'ClickUp Task Sync', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
			array( \ClickSync\Admin\Widget::class, 'render_order_metabox' ),
			'woocommerce_page_wc-orders',
			'side',
			'high'
		);
	}

	/**
	 * Register meta box container for Edit User profile pages.
	 *
	 * @param \WP_User $user User object.
	 */
	public static function register_customer_metabox( $user ) {
		$roles = (array) $user->roles;
		if ( ! in_array( 'customer', $roles ) ) {
			return;
		}
		?>
		<h2><?php esc_html_e( 'ClickUp Customer Sync', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label><?php esc_html_e( 'ClickUp Task Link', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ); ?></label></th>
				<td>
					<?php \ClickSync\Admin\Widget::render_customer_metabox( $user ); ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * AJAX handler to retrieve ClickUp task details.
	 */
	public static function ajax_widget_get_task_details() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'edit_shop_orders' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$task_id = isset( $_POST['task_id'] ) ? sanitize_text_field( wp_unslash( $_POST['task_id'] ) ) : '';
		if ( empty( $task_id ) ) {
			wp_send_json_error( 'Missing task_id', 400 );
		}

		$response = \ClickSync\Api\Client::request( '/api/get-task-details', array( 'task_id' => $task_id ) );

		if ( ! $response || $response['status_code'] !== 200 ) {
			$error = isset( $response['data']['error'] ) ? $response['data']['error'] : 'Failed to fetch task details from ClickUp.';
			wp_send_json_error( $error, $response ? $response['status_code'] : 500 );
		}

		wp_send_json_success( $response['data'] );
	}

	/**
	 * AJAX handler to update ClickUp task attributes.
	 */
	public static function ajax_widget_update_task() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'edit_shop_orders' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$task_id = isset( $_POST['task_id'] ) ? sanitize_text_field( wp_unslash( $_POST['task_id'] ) ) : '';
		if ( empty( $task_id ) ) {
			wp_send_json_error( 'Missing task_id', 400 );
		}

		$payload = array( 'task_id' => $task_id );
		if ( isset( $_POST['status'] ) ) {
			$payload['status'] = sanitize_text_field( wp_unslash( $_POST['status'] ) );
		}
		if ( isset( $_POST['priority'] ) ) {
			$payload['priority'] = sanitize_text_field( wp_unslash( $_POST['priority'] ) );
		}
		if ( isset( $_POST['assignees'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$payload['assignees'] = (array) $_POST['assignees'];
		}

		$response = \ClickSync\Api\Client::request( '/api/update-task', $payload );

		if ( ! $response || $response['status_code'] !== 200 ) {
			$error = isset( $response['data']['error'] ) ? $response['data']['error'] : 'Failed to update task details.';
			wp_send_json_error( $error, $response ? $response['status_code'] : 500 );
		}

		// Update local WordPress cache
		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
		$customer_id = isset( $_POST['customer_id'] ) ? intval( $_POST['customer_id'] ) : 0;
		$data = $response['data']['task'];

		if ( $order_id ) {
			update_post_meta( $order_id, '_clicksync_task_status', $data['status'] );
			update_post_meta( $order_id, '_clicksync_task_priority', $data['priority'] );
			update_post_meta( $order_id, '_clicksync_task_assignees', $data['assignees'] );
			update_post_meta( $order_id, '_clicksync_last_sync', time() );
		} elseif ( $customer_id ) {
			update_user_meta( $customer_id, '_clicksync_task_status', $data['status'] );
			update_user_meta( $customer_id, '_clicksync_task_priority', $data['priority'] );
			update_user_meta( $customer_id, '_clicksync_task_assignees', $data['assignees'] );
			update_user_meta( $customer_id, '_clicksync_last_sync', time() );
		}

		wp_send_json_success( $response['data'] );
	}

	/**
	 * AJAX handler to manually force a sync for orders or customers.
	 */
	public static function ajax_widget_force_sync() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'edit_shop_orders' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
		$customer_id = isset( $_POST['customer_id'] ) ? intval( $_POST['customer_id'] ) : 0;

		if ( ! $order_id && ! $customer_id ) {
			wp_send_json_error( 'Missing parameters.', 400 );
		}

		if ( $order_id ) {
			if ( ! function_exists( 'wc_get_order' ) ) {
				wp_send_json_error( 'WooCommerce is not active.', 500 );
			}
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				wp_send_json_error( 'Order not found.', 404 );
			}

			$payload = \ClickSync\Integrations\WooCommerce::normalize_order( $order );

			$endpoint = '/api/sync-event';
			$json_body = wp_json_encode( $payload );

			$timestamp   = time();
			$host        = wp_parse_url( site_url(), PHP_URL_HOST );
			$secret_key  = Options::get_secret_key();
			$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
			$signature   = hash_hmac( 'sha256', $json_body, $signing_key . ':' . $timestamp );

			$args = array(
				'method'      => 'POST',
				'timeout'     => 30,
				'blocking'    => true,
				'headers'     => array(
					'Content-Type'         => 'application/json',
					'X-ClickSync-Topic'    => 'orders/create',
					'X-ClickSync-Shop'     => sanitize_text_field( $host ),
					'X-ClickSync-Timestamp'=> $timestamp,
					'X-ClickSync-Hmac'     => $signature,
					'X-ClickSync-Sync'     => 'true',
					'User-Agent'           => 'ClickSync-Connect-Plugin/' . CLICKSYNC_VERSION,
				),
				'body'        => $json_body,
			);

			$response = wp_remote_post( CLICKSYNC_CLOUD_URL . $endpoint, $args );
		} else {
			$user = get_userdata( $customer_id );
			if ( ! $user ) {
				wp_send_json_error( 'Customer user not found.', 404 );
			}

			$orders_count = 0;
			$total_spent = '0.00';
			if ( function_exists( 'wc_get_customer_order_count' ) ) {
				$orders_count = wc_get_customer_order_count( $customer_id );
				$total_spent = wc_get_customer_total_spent( $customer_id );
			}

			$payload = array(
				'id'           => $customer_id,
				'email'        => $user->user_email,
				'first_name'   => $user->first_name,
				'last_name'    => $user->last_name,
				'orders_count' => intval( $orders_count ),
				'total_spent'  => (string) $total_spent,
				'created_at'   => gmdate( 'c', strtotime( $user->user_registered ) )
			);

			$endpoint = '/api/sync-event';
			$json_body = wp_json_encode( $payload );

			$timestamp   = time();
			$host        = wp_parse_url( site_url(), PHP_URL_HOST );
			$secret_key  = Options::get_secret_key();
			$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
			$signature   = hash_hmac( 'sha256', $json_body, $signing_key . ':' . $timestamp );

			$args = array(
				'method'      => 'POST',
				'timeout'     => 30,
				'blocking'    => true,
				'headers'     => array(
					'Content-Type'         => 'application/json',
					'X-ClickSync-Topic'    => 'customers/create',
					'X-ClickSync-Shop'     => sanitize_text_field( $host ),
					'X-ClickSync-Timestamp'=> $timestamp,
					'X-ClickSync-Hmac'     => $signature,
					'X-ClickSync-Sync'     => 'true',
					'User-Agent'           => 'ClickSync-Connect-Plugin/' . CLICKSYNC_VERSION,
				),
				'body'        => $json_body,
			);

			$response = wp_remote_post( CLICKSYNC_CLOUD_URL . $endpoint, $args );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message(), 500 );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( empty( $body['task_ids'] ) ) {
			wp_send_json_success( array(
				'message' => isset( $body['message'] ) ? $body['message'] : __( 'Manual sync queued successfully. Refreshed cached values will load shortly.', 'clicksync-connect-clickup-crm-sync-for-woocommerce' ),
				'queued'  => true
			) );
		}

		if ( ! empty( $body['task_ids'] ) && is_array( $body['task_ids'] ) ) {
			$first_task_id = sanitize_text_field( $body['task_ids'][0] );
			$task_url = 'https://app.clickup.com/t/' . $first_task_id;

			if ( $order_id ) {
				update_post_meta( $order_id, '_clicksync_task_id', $first_task_id );
				update_post_meta( $order_id, '_clicksync_task_url', $task_url );
				update_post_meta( $order_id, '_clicksync_last_sync', time() );
			} elseif ( $customer_id ) {
				update_user_meta( $customer_id, '_clicksync_task_id', $first_task_id );
				update_user_meta( $customer_id, '_clicksync_task_url', $task_url );
				update_user_meta( $customer_id, '_clicksync_last_sync', time() );
			}
		}

		wp_send_json_success( array(
			'message'  => 'Synchronization completed successfully.',
			'task_ids' => $body['task_ids']
		) );
	}

	/**
	 * AJAX handler to process user contact submissions and post to Pumble.
	 */
	public static function ajax_contact_submit() {
		check_ajax_referer( 'clicksync_admin_nonce', 'security' );
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized', 403 );
		}

		$name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
		$email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
		$subject = isset( $_POST['contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_subject'] ) ) : '';
		$message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

		if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
			wp_send_json_error( 'Please fill out all required fields.', 400 );
		}

		$host = wp_parse_url( site_url(), PHP_URL_HOST );

		$account   = Options::get_account();
		$plan_name = $account['plan_name'] ?? 'Free Plan';
		$quota     = isset( $account['monthly_quota'] ) ? $account['monthly_quota'] : 100;
		$usage     = isset( $account['monthly_sync_count'] ) ? $account['monthly_sync_count'] : 0;
		$team_name = $account['team_name'] ?? 'Not Connected';

		$json_body = wp_json_encode( array(
			'name'     => $name,
			'email'    => $email,
			'subject'  => $subject,
			'message'  => $message,
			'planName' => $plan_name,
			'usage'    => intval( $usage ),
			'quota'    => intval( $quota ),
			'teamName' => $team_name
		) );

		$timestamp   = time();
		$secret_key  = Options::get_secret_key();
		$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
		$signature   = hash_hmac( 'sha256', $json_body, $signing_key . ':' . $timestamp );

		$args = array(
			'method'      => 'POST',
			'timeout'     => 15,
			'blocking'    => true,
			'headers'     => array(
				'Content-Type'          => 'application/json',
				'X-ClickSync-Shop'      => sanitize_text_field( $host ),
				'X-ClickSync-Timestamp' => $timestamp,
				'X-ClickSync-Hmac'      => $signature,
				'User-Agent'            => 'ClickSync-Connect-Plugin/' . CLICKSYNC_VERSION,
			),
			'body'        => $json_body,
		);

		$response = wp_remote_post( CLICKSYNC_CLOUD_URL . '/api/submit-support', $args );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message(), 500 );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$err_body = json_decode( wp_remote_retrieve_body( $response ), true );
			$err_msg  = isset( $err_body['error'] ) ? $err_body['error'] : 'Failed to dispatch submission to support server.';
			wp_send_json_error( $err_msg, $code );
		}

		wp_send_json_success( array( 'message' => 'Your message has been sent to our support desk. Thank you!' ) );
	}
}
