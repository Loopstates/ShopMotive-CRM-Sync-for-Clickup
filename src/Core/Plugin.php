<?php

namespace ClickSync\Core;

use ClickSync\Admin\AdminMenu;
use ClickSync\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 * 
 * Main plugin orchestrator singleton.
 */
class Plugin {

	/**
	 * Singleton instance holder.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize plugin hooks.
	 */
	private function init_hooks() {
		// Load text domain for translations
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Initialize Admin Interface
		if ( is_admin() ) {
			AdminMenu::init();
		}

		// Initialize WooCommerce Listener
		WooCommerce::init();

		// Initialize REST API endpoints
		add_action( 'rest_api_init', array( \ClickSync\Api\Webhook::class, 'register_routes' ) );
	}

	/**
	 * Load plugin translation text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'clicksync-connect',
			false,
			dirname( plugin_basename( CLICKSYNC_FILE ) ) . '/languages'
		);
	}

	/**
	 * Plugin activation hook callback.
	 */
	public static function activate() {
		Options::get_settings();
		Options::get_account();

		$host = parse_url( site_url(), PHP_URL_HOST );
		wp_remote_post( CLICKSYNC_CLOUD_URL . '/api/save-config', array(
			'method'      => 'POST',
			'timeout'     => 5,
			'blocking'    => false,
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( array(
				'shop'       => $host,
				'actionType' => 'activate_plugin',
				'payload'    => array()
			) ),
		) );
	}

	/**
	 * Plugin deactivation hook callback.
	 */
	public static function deactivate() {
		$host = parse_url( site_url(), PHP_URL_HOST );
		wp_remote_post( CLICKSYNC_CLOUD_URL . '/api/save-config', array(
			'method'      => 'POST',
			'timeout'     => 5,
			'blocking'    => false,
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( array(
				'shop'       => $host,
				'actionType' => 'deactivate_plugin',
				'payload'    => array()
			) ),
		) );
	}
}
