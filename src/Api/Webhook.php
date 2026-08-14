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
			'permission_callback' => '__return_true', // Verified via HMAC signature checks inside callback
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
		$computed_signature = hash_hmac( 'sha256', $body, $host . ':' . $timestamp );

		if ( ! hash_equals( $computed_signature, $hmac ) ) {
			return new \WP_REST_Response( array( 'error' => 'Invalid HMAC signature.' ), 401 );
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
		$order->update_status( $status_slug, sprintf( __( 'Status updated to "%s" via ClickUp task status sync.', 'clicksync-wordpress' ), $status_slug ) );

		return new \WP_REST_Response( array(
			'success' => true,
			'message' => sprintf( 'Successfully updated order status to %s.', $status_slug )
		), 200 );
	}
}
