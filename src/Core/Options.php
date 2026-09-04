<?php

namespace ShopMotive\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Options
 * 
 * Secure manager for WordPress options database storage.
 */
class Options {

	const OPTION_SETTINGS = 'shopmotive_settings';
	const OPTION_ACCOUNT  = 'shopmotive_account';
	const OPTION_USER_MAPPINGS = 'shopmotive_user_mappings';

	/**
	 * Get default plugin settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'connected'              => false,
			'api_key'                => '',
			'store_identifier'       => wp_parse_url( site_url(), PHP_URL_HOST ),
			'orders_enabled'         => true,
			'customers_enabled'      => true,
			'refunds_enabled'        => true,
			'fulfillment_enabled'    => true,
			'sync_rules'             => array(),
			'last_sync_timestamp'    => 0,
			'secret_key'             => '',
		);
	}

	/**
	 * Retrieve saved plugin settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION_SETTINGS, array() );
		return wp_parse_args( (array) $saved, self::get_defaults() );
	}

	/**
	 * Save updated plugin settings.
	 *
	 * @param array $settings Updated settings array.
	 * @return bool
	 */
	public static function update_settings( $settings ) {
		$current = self::get_settings();
		$merged  = wp_parse_args( $settings, $current );
		return update_option( self::OPTION_SETTINGS, $merged );
	}

	/**
	 * Get cached ClickUp account & plan details.
	 *
	 * @return array
	 */
	public static function get_account() {
		$defaults = array(
			'plan_name'          => 'Free Plan',
			'monthly_sync_count' => 0,
			'monthly_quota'      => 100,
			'last_sync_reset'    => '',
			'team_name'          => '',
		);
		$saved = get_option( self::OPTION_ACCOUNT, array() );
		return wp_parse_args( (array) $saved, $defaults );
	}

	/**
	 * Update cached account details.
	 *
	 * @param array $account Account array.
	 * @return bool
	 */
	public static function update_account( $account ) {
		return update_option( self::OPTION_ACCOUNT, $account );
	}

	/**
	 * Retrieve saved secret key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		$settings = self::get_settings();
		return $settings['secret_key'] ?? '';
	}

	/**
	 * Save secret key.
	 *
	 * @param string $secret_key Secret key hex.
	 * @return bool
	 */
	public static function update_secret_key( $secret_key ) {
		return self::update_settings( array( 'secret_key' => $secret_key ) );
	}

	/**
	 * Retrieve saved user mappings.
	 *
	 * @return array
	 */
	public static function get_user_mappings() {
		$defaults = array(
			'mappings'                 => array(),
			'fallback_clickup_user_id' => '',
		);
		$saved = get_option( self::OPTION_USER_MAPPINGS, array() );
		return wp_parse_args( (array) $saved, $defaults );
	}

	/**
	 * Update user mappings.
	 *
	 * @param array $mappings Mappings array.
	 * @return bool
	 */
	public static function update_user_mappings( $mappings ) {
		return update_option( self::OPTION_USER_MAPPINGS, $mappings );
	}

	/**
	 * Reset all stored plugin data.
	 *
	 * @return bool
	 */
	public static function clear_all() {
		delete_option( self::OPTION_SETTINGS );
		delete_option( self::OPTION_ACCOUNT );
		delete_option( self::OPTION_USER_MAPPINGS );
		return true;
	}
}
