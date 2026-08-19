<?php

namespace ClickSync\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Webhook
 * 
 * Handles incoming REST API callbacks from ClickSync Cloud (e.g. status changes).
 */
class Webhook {

	/**
	 * Register REST API routes.
	 */
	public static function register_routes() {
		register_rest_route( 'clicksync/v1', '/status-update', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_status_update' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'clicksync/v1', '/add-note', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_add_note' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'clicksync/v1', '/update-mapping', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_update_mapping' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'clicksync/v1', '/update-customer-mapping', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'handle_update_customer_mapping' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * Handle incoming status update webhook.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function handle_status_update( $request ) {
		$hmac      = $request->get_header( 'X-ClickSync-Hmac' );
		$timestamp = $request->get_header( 'X-ClickSync-Timestamp' );
		$body      = $request->get_body();

		if ( empty( $hmac ) || empty( $timestamp ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing signature headers.' ), 401 );
		}

		// Prevent replay attacks (allow 10-minute window)
		if ( abs( time() - intval( $timestamp ) ) > 600 ) {
			return new \WP_REST_Response( array( 'error' => 'Request expired.' ), 401 );
		}

		$host = parse_url( site_url(), PHP_URL_HOST );
		$secret_key = \ClickSync\Core\Options::get_secret_key();
		$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
		$computed_signature = hash_hmac( 'sha256', $body, $signing_key . ':' . $timestamp );

		if ( ! hash_equals( $computed_signature, $hmac ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid HMAC signature.' ), 401 );
		}

		$settings = \ClickSync\Core\Options::get_settings();
		if ( empty( $settings['orders_enabled'] ) ) {
			return new \WP_REST_Response( array( 'error' => 'Order status sync is disabled in settings.' ), 403 );
		}

		$params   = $request->get_json_params();
		$order_id = isset( $params['order_id'] ) ? intval( $params['order_id'] ) : 0;
		$status   = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : '';

		if ( ! $order_id || empty( $status ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing required fields.' ), 400 );
		}

		if ( ! function_exists( 'wc_get_order' ) ) {
			return new \WP_REST_Response( array( 'error' => 'WooCommerce is not active.' ), 500 );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new \WP_REST_Response( array( 'error' => 'Order not found.' ), 404 );
		}

		// Update order status. Note: WooCommerce statuses are prefixed with wc- in database but the API expects slug without prefix.
		$status_slug = str_replace( 'wc-', '', $status );
		
		// Update WooCommerce order status
		$order->update_status( $status_slug, sprintf( __( 'Status updated to "%s" via ClickUp task status sync.', 'clicksync-connect' ), $status_slug ) );

		return new \WP_REST_Response( array(
			'success' => true,
			'message' => sprintf( 'Successfully updated order status to %s.', $status_slug )
		), 200 );
	}

	/**
	 * Handle incoming add order note REST callback from ClickUp task comments.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function handle_add_note( $request ) {
		$hmac      = $request->get_header( 'X-ClickSync-Hmac' );
		$timestamp = $request->get_header( 'X-ClickSync-Timestamp' );
		$body      = $request->get_body();

		if ( empty( $hmac ) || empty( $timestamp ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing signature headers.' ), 401 );
		}

		if ( abs( time() - intval( $timestamp ) ) > 600 ) {
			return new \WP_REST_Response( array( 'error' => 'Request expired.' ), 401 );
		}

		$host = parse_url( site_url(), PHP_URL_HOST );
		$secret_key = \ClickSync\Core\Options::get_secret_key();
		$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
		$computed_signature = hash_hmac( 'sha256', $body, $signing_key . ':' . $timestamp );

		if ( ! hash_equals( $computed_signature, $hmac ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid HMAC signature.' ), 401 );
		}

		$params   = $request->get_json_params();
		$order_id = isset( $params['order_id'] ) ? intval( $params['order_id'] ) : 0;
		$content  = isset( $params['content'] ) ? sanitize_textarea_field( $params['content'] ) : '';
		$author_name = isset( $params['author_name'] ) ? sanitize_text_field( $params['author_name'] ) : 'ClickUp User';
		$author_clickup_id = isset( $params['author_clickup_id'] ) ? sanitize_text_field( $params['author_clickup_id'] ) : '';

		if ( ! $order_id || empty( $content ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing required fields.' ), 400 );
		}

		if ( ! function_exists( 'wc_get_order' ) ) {
			return new \WP_REST_Response( array( 'error' => 'WooCommerce is not active.' ), 500 );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new \WP_REST_Response( array( 'error' => 'Order not found.' ), 404 );
		}

		// Resolve mapping to attribute WordPress user comment
		$user_mappings_data = \ClickSync\Core\Options::get_user_mappings();
		$saved_mappings = $user_mappings_data['mappings'] ?? array();
		
		$wp_author_id = 0;
		if ( ! empty( $author_clickup_id ) ) {
			foreach ( $saved_mappings as $wp_id => $cu_id ) {
				if ( (string) $cu_id === (string) $author_clickup_id ) {
					$wp_author_id = intval( $wp_id );
					break;
				}
			}
		}

		$note_text = sprintf( '[ClickUp] %s: %s', $author_name, $content );
		
		// Add internal admin note to WooCommerce order
		$note_id = $order->add_order_note( $note_text, false, false );
		
		if ( $wp_author_id && $note_id ) {
			wp_update_comment( array(
				'comment_ID' => $note_id,
				'user_id'    => $wp_author_id
			) );
		}

		return new \WP_REST_Response( array(
			'success' => true,
			'message' => 'Successfully added note to WooCommerce order.'
		), 200 );
	}

	/**
	 * Handle incoming REST callback to cache task mapping metadata.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function handle_update_mapping( $request ) {
		$hmac      = $request->get_header( 'X-ClickSync-Hmac' );
		$timestamp = $request->get_header( 'X-ClickSync-Timestamp' );
		$body      = $request->get_body();

		if ( empty( $hmac ) || empty( $timestamp ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing signature headers.' ), 401 );
		}

		if ( abs( time() - intval( $timestamp ) ) > 600 ) {
			return new \WP_REST_Response( array( 'error' => 'Request expired.' ), 401 );
		}

		$host = parse_url( site_url(), PHP_URL_HOST );
		$secret_key = \ClickSync\Core\Options::get_secret_key();
		$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
		$computed_signature = hash_hmac( 'sha256', $body, $signing_key . ':' . $timestamp );

		if ( ! hash_equals( $computed_signature, $hmac ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid HMAC signature.' ), 401 );
		}

		$params   = $request->get_json_params();
		$order_id = isset( $params['order_id'] ) ? intval( $params['order_id'] ) : 0;
		$task_id  = isset( $params['task_id'] ) ? sanitize_text_field( $params['task_id'] ) : '';
		$task_url = isset( $params['task_url'] ) ? esc_url_raw( $params['task_url'] ) : '';
		$status   = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : '';
		$priority = isset( $params['priority'] ) ? sanitize_text_field( $params['priority'] ) : '';
		$assignees= isset( $params['assignees'] ) ? (array) $params['assignees'] : array();

		if ( ! $order_id || empty( $task_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing required fields.' ), 400 );
		}

		if ( ! function_exists( 'wc_get_order' ) ) {
			return new \WP_REST_Response( array( 'error' => 'WooCommerce is not active.' ), 500 );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new \WP_REST_Response( array( 'error' => 'Order not found.' ), 404 );
		}

		// Save mapped task details locally
		update_post_meta( $order_id, '_clicksync_task_id', $task_id );
		update_post_meta( $order_id, '_clicksync_task_url', $task_url );
		update_post_meta( $order_id, '_clicksync_task_status', $status );
		update_post_meta( $order_id, '_clicksync_task_priority', $priority );
		update_post_meta( $order_id, '_clicksync_task_assignees', $assignees );
		update_post_meta( $order_id, '_clicksync_last_sync', time() );

		return new \WP_REST_Response( array(
			'success' => true,
			'message' => 'Task mapping details cached locally successfully.'
		), 200 );
	}

	/**
	 * Handle incoming REST callback to cache customer task mapping metadata.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function handle_update_customer_mapping( $request ) {
		$hmac      = $request->get_header( 'X-ClickSync-Hmac' );
		$timestamp = $request->get_header( 'X-ClickSync-Timestamp' );
		$body      = $request->get_body();

		if ( empty( $hmac ) || empty( $timestamp ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing signature headers.' ), 401 );
		}

		if ( abs( time() - intval( $timestamp ) ) > 600 ) {
			return new \WP_REST_Response( array( 'error' => 'Request expired.' ), 401 );
		}

		$host = parse_url( site_url(), PHP_URL_HOST );
		$secret_key = \ClickSync\Core\Options::get_secret_key();
		$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
		$computed_signature = hash_hmac( 'sha256', $body, $signing_key . ':' . $timestamp );

		if ( ! hash_equals( $computed_signature, $hmac ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid HMAC signature.' ), 401 );
		}

		$params      = $request->get_json_params();
		$customer_id = isset( $params['customer_id'] ) ? intval( $params['customer_id'] ) : 0;
		$task_id     = isset( $params['task_id'] ) ? sanitize_text_field( $params['task_id'] ) : '';
		$task_url    = isset( $params['task_url'] ) ? esc_url_raw( $params['task_url'] ) : '';
		$status      = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : '';
		$priority    = isset( $params['priority'] ) ? sanitize_text_field( $params['priority'] ) : '';
		$assignees   = isset( $params['assignees'] ) ? (array) $params['assignees'] : array();

		if ( ! $customer_id || empty( $task_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Missing required fields.' ), 400 );
		}

		// Save mapped task details locally in user meta
		update_user_meta( $customer_id, '_clicksync_task_id', $task_id );
		update_user_meta( $customer_id, '_clicksync_task_url', $task_url );
		update_user_meta( $customer_id, '_clicksync_task_status', $status );
		update_user_meta( $customer_id, '_clicksync_task_priority', $priority );
		update_user_meta( $customer_id, '_clicksync_task_assignees', $assignees );
		update_user_meta( $customer_id, '_clicksync_last_sync', time() );

		return new \WP_REST_Response( array(
			'success' => true,
			'message' => 'Customer task mapping details cached locally successfully.'
		), 200 );
	}
}
