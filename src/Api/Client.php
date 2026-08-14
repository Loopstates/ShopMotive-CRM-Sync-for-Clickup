<?php

namespace ClickSync\Api;

use ClickSync\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Client
 * 
 * HTTP API client for dispatching normalized JSON payloads to ClickSync Cloud backend.
 */
class Client {

	/**
	 * Dispatch normalized JSON payload to ClickSync Cloud middleware.
	 *
	 * @param string $topic   Event topic (e.g. 'orders/create', 'customers/create', 'refunds/create', 'checkouts/update').
	 * @param array  $payload Normalized Shopify-schema JSON array.
	 * @return array|bool Response array or false on failure.
	 */
	public static function dispatch_event( $topic, $payload ) {
		$settings = Options::get_settings();
		$endpoint = CLICKSYNC_CLOUD_URL . '/api/sync-event';

		$json_body = wp_json_encode( $payload );

		// Sign request using secret key for security validation
		$timestamp   = time();
		$host        = parse_url( site_url(), PHP_URL_HOST );
		$secret_key  = Options::get_secret_key();
		$signing_key = ! empty( $secret_key ) ? $secret_key : $host;
		$signature   = hash_hmac( 'sha256', $json_body, $signing_key . ':' . $timestamp );

		$args = array(
			'method'      => 'POST',
			'timeout'     => 15,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'Content-Type'         => 'application/json',
				'X-ClickSync-Topic'    => sanitize_text_field( $topic ),
				'X-ClickSync-Shop'     => sanitize_text_field( $host ),
				'X-ClickSync-Timestamp'=> $timestamp,
				'X-ClickSync-Hmac'     => $signature,
				'User-Agent'           => 'ClickSync-WordPress-Plugin/' . CLICKSYNC_VERSION,
			),
			'body'        => $json_body,
		);

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			error_log( 'ClickSync Cloud API Dispatch Error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		// Update cached quota information if returned by server
		if ( isset( $data['account'] ) && is_array( $data['account'] ) ) {
			Options::update_account( $data['account'] );
		}

		return array(
			'status_code' => $status_code,
			'data'        => $data,
		);
	}
}
