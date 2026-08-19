<?php

namespace ClickSync\Integrations;

use ClickSync\Core\Options;
use ClickSync\Api\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WooCommerce
 * 
 * WooCommerce event listener and payload normalizer converting WP WooCommerce objects
 * into standard Shopify JSON schema expected by ClickSync Cloud.
 */
class WooCommerce {

	/**
	 * Register WooCommerce event hooks.
	 */
	public static function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Order Creation & Status Changes
		add_action( 'woocommerce_new_order', array( __CLASS__, 'on_new_order' ), 10, 2 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'on_order_status_changed' ), 10, 4 );

		// Refunds
		add_action( 'woocommerce_order_refunded', array( __CLASS__, 'on_order_refunded' ), 10, 2 );
		add_action( 'woocommerce_new_order_note', array( __CLASS__, 'on_new_order_note' ), 10, 2 );

		// Customer Creation
		add_action( 'woocommerce_created_customer', array( __CLASS__, 'on_created_customer' ), 10, 3 );

		// Action Scheduler background retries
		add_action( 'clicksync_retry_event', array( __CLASS__, 'handle_retry_event' ), 10, 3 );
	}

	/**
	 * Handle new WooCommerce order creation.
	 *
	 * @param int       $order_id Order ID.
	 * @param \WC_Order $order    WooCommerce Order Object.
	 */
	public static function on_new_order( $order_id, $order = null ) {
		$settings = Options::get_settings();
		if ( empty( $settings['orders_enabled'] ) ) {
			return;
		}

		if ( ! $order && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		if ( ! apply_filters( 'clicksync_should_sync_order', true, $order_id, $order ) ) {
			return;
		}

		$payload = self::normalize_order( $order );
		Client::dispatch_event( 'orders/create', $payload );
	}

	/**
	 * Handle WooCommerce order status change.
	 *
	 * @param int       $order_id   Order ID.
	 * @param string    $old_status Old status slug.
	 * @param string    $new_status New status slug.
	 * @param \WC_Order $order      WooCommerce Order Object.
	 */
	public static function on_order_status_changed( $order_id, $old_status, $new_status, $order = null ) {
		$settings = Options::get_settings();
		if ( empty( $settings['orders_enabled'] ) ) {
			return;
		}

		if ( ! $order && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		if ( ! apply_filters( 'clicksync_should_sync_order', true, $order_id, $order ) ) {
			return;
		}

		$payload = self::normalize_order( $order );

		// Dispatch order update event
		Client::dispatch_event( 'orders/updated', $payload );

		// Dispatch specific fulfillment topic if completed
		if ( 'completed' === $new_status ) {
			Client::dispatch_event( 'orders/fulfilled', $payload );
		}
	}

	/**
	 * Handle WooCommerce order refund.
	 *
	 * @param int $order_id  Order ID.
	 * @param int $refund_id Refund ID.
	 */
	public static function on_order_refunded( $order_id, $refund_id ) {
		$settings = Options::get_settings();
		if ( empty( $settings['refunds_enabled'] ) ) {
			return;
		}

		$order  = wc_get_order( $order_id );
		$refund = wc_get_order( $refund_id );

		if ( ! $order ) {
			return;
		}

		if ( ! apply_filters( 'clicksync_should_sync_order', true, $order_id, $order ) ) {
			return;
		}

		$refund_amount = $refund ? abs( $refund->get_amount() ) : 0;
		$reason        = $refund ? $refund->get_reason() : 'Refund processed';

		$refund_line_items = array();
		if ( $refund ) {
			foreach ( $refund->get_items() as $item_id => $item ) {
				$refund_line_items[] = array(
					'quantity'  => abs( $item->get_quantity() ),
					'subtotal'  => (string) abs( $item->get_subtotal() ),
					'line_item' => array(
						'name' => $item->get_name(),
					),
				);
			}
		}

		$payload = array(
			'id'                => $refund_id,
			'order_id'          => $order_id,
			'amount'            => (string) $refund_amount,
			'currency'          => $order->get_currency(),
			'note'              => $reason,
			'created_at'        => date( 'c', strtotime( $order->get_date_created() ) ),
			'order_number'      => '#' . $order->get_order_number(),
			'customer_email'    => $order->get_billing_email(),
			'refund_line_items' => $refund_line_items,
		);

		$payload = apply_filters( 'clicksync_refund_payload', $payload, $refund_id, $order );

		Client::dispatch_event( 'refunds/create', $payload );
	}

	/**
	 * Handle WooCommerce new order note creation.
	 *
	 * @param int       $note_id Note ID.
	 * @param \WC_Order $order   WooCommerce Order Object.
	 */
	public static function on_new_order_note( $note_id, $order ) {
		$settings = Options::get_settings();
		if ( empty( $settings['orders_enabled'] ) ) {
			return;
		}

		if ( ! function_exists( 'wc_get_order_note' ) ) {
			return;
		}

		$note = wc_get_order_note( $note_id );
		if ( ! $note ) {
			return;
		}

		// Prevent sync feedback loops
		if ( strpos( $note->content, 'via ClickUp' ) !== false || strpos( $note->content, '[ClickUp]' ) !== false ) {
			return;
		}

		if ( ! apply_filters( 'clicksync_should_sync_order', true, $order->get_id(), $order ) ) {
			return;
		}

		// Resolve current admin identity mapping
		$user_mappings_data = Options::get_user_mappings();
		$saved_mappings = $user_mappings_data['mappings'] ?? array();
		$fallback_clickup_id = $user_mappings_data['fallback_clickup_user_id'] ?? '';

		$current_user_id = get_current_user_id();
		$action_maker_clickup_id = $saved_mappings[ $current_user_id ] ?? $fallback_clickup_id;
		$wp_user = wp_get_current_user();
		$action_maker_name = $wp_user && $wp_user->ID ? $wp_user->display_name : 'System';

		$payload = array(
			'order_id'                => $order->get_id(),
			'note_id'                 => $note_id,
			'content'                 => $note->content,
			'customer_note'           => $note->customer_note,
			'added_by'                => $note->added_by,
			'action_maker_clickup_id' => $action_maker_clickup_id,
			'action_maker_name'       => $action_maker_name,
		);

		$payload = apply_filters( 'clicksync_order_note_payload', $payload, $note_id, $order );

		Client::dispatch_event( 'orders/note_created', $payload );
	}

	public static function on_created_customer( $customer_id, $new_data, $password_generated ) {
		$settings = Options::get_settings();
		if ( empty( $settings['customers_enabled'] ) ) {
			return;
		}

		if ( ! apply_filters( 'clicksync_should_sync_customer', true, $customer_id, $new_data ) ) {
			return;
		}

		$user = get_userdata( $customer_id );
		if ( ! $user ) {
			return;
		}

		$user_meta = array();
		$all_user_meta = get_user_meta( $customer_id );
		if ( ! empty( $all_user_meta ) ) {
			foreach ( $all_user_meta as $k => $values ) {
				if ( strpos( $k, '_' ) !== 0 ) {
					$user_meta[ $k ] = maybe_unserialize( $values[0] );
				}
			}
		}

		$payload = array(
			'id'            => $customer_id,
			'email'         => $user->user_email,
			'first_name'    => get_user_meta( $customer_id, 'billing_first_name', true ) ?: $user->first_name,
			'last_name'     => get_user_meta( $customer_id, 'billing_last_name', true ) ?: $user->last_name,
			'orders_count'  => wc_get_customer_order_count( $customer_id ),
			'total_spent'   => (string) wc_get_customer_total_spent( $customer_id ),
			'created_at'    => date( 'c', strtotime( $user->user_registered ) ),
			'phone'         => get_user_meta( $customer_id, 'billing_phone', true ),
			'meta'          => $user_meta,
		);

		$payload = apply_filters( 'clicksync_customer_payload', $payload, $customer_id );

		Client::dispatch_event( 'customers/create', $payload );
	}


	/**
	 * Normalize WooCommerce Order object into Shopify JSON Schema.
	 *
	 * @param \WC_Order $order WooCommerce Order.
	 * @return array
	 */
	public static function normalize_order( $order ) {
		$user_mappings_data = Options::get_user_mappings();
		$saved_mappings = $user_mappings_data['mappings'] ?? array();
		$fallback_clickup_id = $user_mappings_data['fallback_clickup_user_id'] ?? '';

		$current_user_id = get_current_user_id();
		$action_maker_clickup_id = $saved_mappings[ $current_user_id ] ?? $fallback_clickup_id;
		$wp_user = wp_get_current_user();
		$action_maker_name = $wp_user && $wp_user->ID ? $wp_user->display_name : 'System';

		$meta_data = array();
		foreach ( $order->get_meta_data() as $meta ) {
			if ( strpos( $meta->key, '_' ) !== 0 ) {
				$meta_data[ $meta->key ] = $meta->value;
			}
		}

		$items = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			$items[] = array(
				'id'         => $item->get_id(),
				'product_id' => $item->get_product_id(),
				'name'       => $item->get_name(),
				'quantity'   => $item->get_quantity(),
				'price'      => (string) $order->get_item_total( $item, false, true ),
				'sku'        => $product ? $product->get_sku() : '',
			);
		}

		$status = $order->get_status();
		$financial_status = 'pending';
		if ( in_array( $status, array( 'processing', 'completed' ), true ) ) {
			$financial_status = 'paid';
		} elseif ( 'refunded' === $status ) {
			$financial_status = 'refunded';
		} elseif ( 'cancelled' === $status ) {
			$financial_status = 'voided';
		}

		$fulfillment_status = 'unfulfilled';
		if ( 'completed' === $status ) {
			$fulfillment_status = 'fulfilled';
		}

		return array(
			'id'                 => $order->get_id(),
			'name'               => '#' . $order->get_order_number(),
			'order_number'       => (string) $order->get_order_number(),
			'email'              => $order->get_billing_email(),
			'created_at'         => date( 'c', strtotime( $order->get_date_created() ) ),
			'updated_at'         => date( 'c', strtotime( $order->get_date_modified() ) ),
			'total_price'        => (string) $order->get_total(),
			'subtotal_price'     => (string) $order->get_subtotal(),
			'total_tax'          => (string) $order->get_total_tax(),
			'currency'           => $order->get_currency(),
			'financial_status'   => $financial_status,
			'fulfillment_status' => $fulfillment_status,
			'note'               => $order->get_customer_note(),
			'line_items'         => $items,
			'customer'           => array(
				'id'         => $order->get_customer_id(),
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'email'      => $order->get_billing_email(),
				'phone'      => $order->get_billing_phone(),
			),
			'billing_address'    => array(
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'company'    => $order->get_billing_company(),
				'address1'   => $order->get_billing_address_1(),
				'address2'   => $order->get_billing_address_2(),
				'city'       => $order->get_billing_city(),
				'province'   => $order->get_billing_state(),
				'country'    => $order->get_billing_country(),
				'zip'        => $order->get_billing_postcode(),
				'phone'      => $order->get_billing_phone(),
			),
			'shipping_address'   => array(
				'first_name' => $order->get_shipping_first_name(),
				'last_name'  => $order->get_shipping_last_name(),
				'company'    => $order->get_shipping_company(),
				'address1'   => $order->get_shipping_address_1(),
				'address2'   => $order->get_shipping_address_2(),
				'city'       => $order->get_shipping_city(),
				'province'   => $order->get_shipping_state(),
				'country'    => $order->get_shipping_country(),
				'zip'        => $order->get_shipping_postcode(),
			),
			'payment_method'          => $order->get_payment_method(),
			'payment_method_title'    => $order->get_payment_method_title(),
			'action_maker_clickup_id' => $action_maker_clickup_id,
			'action_maker_name'       => $action_maker_name,
		);

		return apply_filters( 'clicksync_order_payload', $payload, $order );
	}

	/**
	 * Background retry event callback executed by Action Scheduler.
	 *
	 * @param string $topic    Event topic.
	 * @param array  $payload  Event payload.
	 * @param int    $attempts Current attempt number.
	 */
	public static function handle_retry_event( $topic, $payload, $attempts ) {
		error_log( sprintf( 'ClickSync executing background retry attempt %d for topic "%s".', $attempts, $topic ) );
		
		$result = \ClickSync\Api\Client::dispatch_event( $topic, $payload, true );

		if ( ! $result ) {
			$retry_limit = apply_filters( 'clicksync_retry_limit', 3 );
			if ( $attempts < $retry_limit ) {
				\ClickSync\Api\Client::schedule_retry( $topic, $payload, $attempts + 1 );
			} else {
				error_log( sprintf( 'ClickSync background retry failed after maximum attempts (%d) for topic "%s".', $retry_limit, $topic ) );
			}
		} else {
			error_log( sprintf( 'ClickSync background retry succeeded on attempt %d for topic "%s".', $attempts, $topic ) );
		}
	}
}
