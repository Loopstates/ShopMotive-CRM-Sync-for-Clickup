<?php

namespace ShopMotive\Api;

use ShopMotive\Core\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Client
 * 
 * HTTP API client for dispatching normalized JSON payloads to ShopMotive Cloud backend.
 */
class Client {

	/**
	 * Dispatch normalized JSON payload to ShopMotive Cloud middleware.
	 *
	 * @param string $topic   Event topic (e.g. 'orders/create', 'customers/create', 'refunds/create', 'checkouts/update').
	 * @param array  $payload Normalized Shopify-schema JSON array.
	 * @return array|bool Response array or false on failure.
	 */
	public static function dispatch_event( $topic, $payload, $is_retry = false ) {
		$settings = Options::get_settings();
		$endpoint = SHOPMOTIVE_CLOUD_URL . '/api/sync-event';

		$topic = apply_filters( 'shopmotive_event_topic', $topic, $payload );

		$json_body = wp_json_encode( $payload );

		// Sign request using secret key for security validation
		$timestamp   = time();
		$host        = wp_parse_url( site_url(), PHP_URL_HOST );
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
				'User-Agent'           => 'ShopMotive-CRM-Sync-for-ClickUp/' . SHOPMOTIVE_VERSION,
			),
			'body'        => $json_body,
		);

		$args['headers'] = apply_filters( 'shopmotive_api_request_headers', $args['headers'], $endpoint );
		$args            = apply_filters( 'shopmotive_api_request_args', $args, $endpoint );

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			// Debug log commented for WordPress.org compliance: error_log( 'ShopMotive Cloud API Dispatch Error: ' . $response->get_error_message() );
			do_action( 'shopmotive_event_failed', $topic, $payload, $response->get_error_message() );
			if ( ! $is_retry ) {
				self::schedule_retry( $topic, $payload );
			}
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		if ( $status_code < 200 || $status_code >= 300 ) {
			// Debug log commented for WordPress.org compliance: error_log( 'ShopMotive Cloud API Dispatch HTTP Error: ' . $status_code );
			do_action( 'shopmotive_event_failed', $topic, $payload, 'HTTP status code: ' . $status_code );
			if ( ! $is_retry ) {
				self::schedule_retry( $topic, $payload );
			}
			return false;
		}

		// Update cached quota information if returned by server
		if ( isset( $data['account'] ) && is_array( $data['account'] ) ) {
			Options::update_account( $data['account'] );
		}

		do_action( 'shopmotive_event_dispatched', $topic, $payload, $response );

		return array(
			'status_code' => $status_code,
			'data'        => $data,
		);
	}

	/**
	 * Schedule background retry for failed webhook event dispatches.
	 *
	 * @param string $topic    Event topic.
	 * @param array  $payload  Event payload.
	 * @param int    $attempts Current attempt number.
	 */
	public static function schedule_retry( $topic, $payload, $attempts = 1 ) {
		if ( function_exists( 'as_schedule_single_action' ) ) {
			$delay = 5 * MINUTE_IN_SECONDS * $attempts;
			$delay = apply_filters( 'shopmotive_retry_delay', $delay, $attempts, $topic );
			as_schedule_single_action(
				time() + $delay,
				'shopmotive_retry_event',
				array(
					'topic'    => $topic,
					'payload'  => $payload,
					'attempts' => $attempts,
				),
				'shopmotive-crm-sync-for-clickup'
			);
			// Debug log commented for WordPress.org compliance: error_log( sprintf( 'ClickSync scheduled retry attempt %d for topic "%s" in %d seconds.', $attempts, $topic, $delay ) );
		}
	}

	/**
	 * Send arbitrary signed request to ShopMotive Cloud middleware.
	 *
	 * @param string $endpoint_path Endpoint path (e.g. '/api/get-task-details').
	 * @param array  $payload Payload array.
	 * @return array|bool Response array or false on failure.
	 */
	public static function request( $endpoint_path, $payload ) {
		$endpoint = SHOPMOTIVE_CLOUD_URL . $endpoint_path;
		$json_body = wp_json_encode( $payload );

		// Sign request using secret key for security validation
		$timestamp   = time();
		$host        = wp_parse_url( site_url(), PHP_URL_HOST );
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
				'X-ClickSync-Shop'     => sanitize_text_field( $host ),
				'X-ClickSync-Timestamp'=> $timestamp,
				'X-ClickSync-Hmac'     => $signature,
				'User-Agent'           => 'ShopMotive-CRM-Sync-for-ClickUp/' . SHOPMOTIVE_VERSION,
			),
			'body'        => $json_body,
		);

		$args['headers'] = apply_filters( 'shopmotive_api_request_headers', $args['headers'], $endpoint );
		$args            = apply_filters( 'shopmotive_api_request_args', $args, $endpoint );

		$response = wp_remote_post( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			// Debug log commented for WordPress.org compliance: error_log( 'ShopMotive Cloud API Request Error: ' . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		
		return array(
			'status_code' => $status_code,
			'data'        => json_decode( $body, true ),
		);
	}
}
