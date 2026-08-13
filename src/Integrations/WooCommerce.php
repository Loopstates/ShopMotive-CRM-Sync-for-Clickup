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

		// Customer Creation
		add_action( 'woocommerce_created_customer', array( __CLASS__, 'on_created_customer' ), 10, 3 );

		// Draft & Abandoned Checkout Tracking
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'on_checkout_processed' ), 10, 3 );
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

		$refund_amount = $refund ? abs( $refund->get_amount() ) : 0;
		$reason        = $refund ? $refund->get_reason() : 'Refund processed';

		$payload = array(
			'id'             => $refund_id,
			'order_id'       => $order_id,
			'amount'         => (string) $refund_amount,
			'currency'       => $order->get_currency(),
			'note'           => $reason,
			'created_at'     => date( 'c', strtotime( $order->get_date_created() ) ),
			'order_number'   => '#' . $order->get_order_number(),
			'customer_email' => $order->get_billing_email(),
		);

		Client::dispatch_event( 'refunds/create', $payload );
	}

	/**
	 * Handle WooCommerce customer creation.
	 *
	 * @param int   $customer_id Customer user ID.
	 * @param array $new_data    Customer data.
	 * @param bool  $password_generated Password generated boolean.
	 */
	public static function on_created_customer( $customer_id, $new_data, $password_generated ) {
		$settings = Options::get_settings();
		if ( empty( $settings['customers_enabled'] ) ) {
			return;
		}

		$user = get_userdata( $customer_id );
		if ( ! $user ) {
			return;
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
		);

		Client::dispatch_event( 'customers/create', $payload );
	}

	/**
	 * Handle WooCommerce checkout processing (Draft / Abandoned Checkouts).
	 *
	 * @param int   $order_id Order ID.
	 * @param array $posted_data Posted checkout data.
	 * @param \WC_Order $order WooCommerce Order Object.
	 */
	public static function on_checkout_processed( $order_id, $posted_data, $order = null ) {
		$settings = Options::get_settings();
		if ( empty( $settings['draft_checkouts_enabled'] ) ) {
			return;
		}

		if ( ! $order && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order || 'completed' === $order->get_status() ) {
			return;
		}

		$payload = self::normalize_order( $order );
		Client::dispatch_event( 'checkouts/update', $payload );
	}

	/**
	 * Normalize WooCommerce Order object into Shopify JSON Schema.
	 *
	 * @param \WC_Order $order WooCommerce Order.
	 * @return array
	 */
	public static function normalize_order( $order ) {
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
		);
	}
}
