<?php
/**
 * ClickSync Connect - Custom Hooks and Filters Developer Example
 * 
 * Drop this snippet into your active theme's functions.php or a custom utility plugin
 * to hook into ClickSync's event pipeline.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Conditional Syncing: Stop syncing orders that are under $50
add_filter( 'clicksync_should_sync_order', 'clicksync_connect_clickup_crm_sync_for_woocommerce_order_eligibility', 10, 3 );
function clicksync_connect_clickup_crm_sync_for_woocommerce_order_eligibility( $should_sync, $order_id, $order ) {
	if ( $order && $order->get_total() < 50.00 ) {
		// Block syncing to save monthly quota limit
		return false;
	}
	return $should_sync;
}

// 2. Conditional Syncing: Only sync customer accounts created with specific roles
add_filter( 'clicksync_should_sync_customer', 'clicksync_connect_clickup_crm_sync_for_woocommerce_customer_eligibility', 10, 2 );
function clicksync_connect_clickup_crm_sync_for_woocommerce_customer_eligibility( $should_sync, $customer_id ) {
	$user = get_userdata( $customer_id );
	if ( $user && in_array( 'wholesale_customer', (array) $user->roles, true ) ) {
		return true;
	}
	// Block sync for non-wholesale customers
	return false;
}

// 3. Payload Modification: Append custom fields (ACF) to WooCommerce order payloads
add_filter( 'clicksync_order_payload', 'clicksync_connect_clickup_crm_sync_for_woocommerce_order_payload_decorator', 10, 2 );
function clicksync_connect_clickup_crm_sync_for_woocommerce_order_payload_decorator( $payload, $order ) {
	// Inject ACF metadata
	$delivery_date = get_post_meta( $order->get_id(), 'acf_delivery_date', true );
	if ( ! empty( $delivery_date ) ) {
		$payload['meta']['delivery_date'] = sanitize_text_field( $delivery_date );
	}

	// Add extra flags
	$payload['meta']['custom_source'] = 'Wholesale Portal';

	return $payload;
}

// 4. API Request Modification: Inject custom tracking header on outbound calls
add_filter( 'clicksync_api_request_headers', 'clicksync_connect_clickup_crm_sync_for_woocommerce_headers', 10, 2 );
function clicksync_connect_clickup_crm_sync_for_woocommerce_headers( $headers, $endpoint ) {
	$headers['X-Developer-Token'] = 'CS-DEV-9994827';
	return $headers;
}

// 5. Post-Dispatch Hook: Perform custom logic when an order successfully syncs
add_action( 'clicksync_event_dispatched', 'clicksync_connect_clickup_crm_sync_for_woocommerce_post_dispatch', 10, 3 );
function clicksync_connect_clickup_crm_sync_for_woocommerce_post_dispatch( $topic, $payload, $response ) {
	if ( 'orders/create' === $topic ) {
		$order_id = isset( $payload['id'] ) ? intval( $payload['id'] ) : 0;
		if ( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->add_order_note( '[ClickUp] Sync successfully executed to ClickUp workspace.' );
			}
		}
	}
}
